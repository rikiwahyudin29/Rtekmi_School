<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\TripayService;
use App\Services\LogKeuanganService;

class KeuanganApiController extends Controller
{
    protected $tripay;
    protected $log;

    public function __construct(TripayService $tripay, LogKeuanganService $log)
    {
        $this->tripay = $tripay;
        $this->log = $log;
    }

    private function getSiswaInfo($nisn)
    {
        return DB::table('tbl_siswa')
            ->select('id', 'nama_lengkap', 'nis', 'no_hp_siswa')
            ->where('nisn', $nisn)
            ->first();
    }

    // ==========================================
    // 1. GET DATA TAGIHAN DAN RIWAYAT
    // ==========================================
    public function getTagihan(Request $request)
    {
        $nisn = $request->input('nisn');
        $siswa = $this->getSiswaInfo($nisn);
        if (!$siswa) {
            return response()->json(['status' => false, 'message' => 'Siswa tidak ditemukan'], 404);
        }

        $tagihan = DB::table('tbl_tagihan as t')
            ->select('t.*', 'p.nama_pos')
            ->join('tbl_jenis_bayar as j', 'j.id', '=', 't.id_jenis_bayar')
            ->join('tbl_pos_bayar as p', 'p.id', '=', 'j.id_pos_bayar')
            ->where('t.id_siswa', $siswa->id)
            ->where('t.status_bayar', '!=', 'LUNAS')
            ->get();

        $riwayat = DB::table('tbl_transaksi as trx')
            ->select('trx.*', 'p.nama_pos', 't.keterangan')
            ->join('tbl_tagihan as t', 't.id', '=', 'trx.id_tagihan')
            ->join('tbl_jenis_bayar as j', 'j.id', '=', 't.id_jenis_bayar')
            ->join('tbl_pos_bayar as p', 'p.id', '=', 'j.id_pos_bayar')
            ->where('trx.id_siswa', $siswa->id)
            ->orderBy('trx.created_at', 'DESC')
            ->get();

        return response()->json([
            'status' => true,
            'tagihan' => $tagihan,
            'riwayat' => $riwayat,
            'channels' => $this->tripay->getChannels()
        ], 200);
    }

    // ==========================================
    // 2. PROSES BAYAR TRIPAY (KEMBALIKAN URL UNTUK ANDROID)
    // ==========================================
    public function bayarTripay(Request $request)
    {
        $id_tagihan    = $request->input('id_tagihan');
        $kode_metode   = $request->input('method');
        $input_nominal = $request->input('nominal_bayar', 0);
        
        if(empty($id_tagihan) || empty($kode_metode)) {
            return response()->json(['status' => false, 'message' => 'Data pembayaran tidak lengkap.'], 400);
        }

        $tagihan = DB::table('tbl_tagihan as t')
            ->select(
                't.*', 
                'p.nama_pos', 
                's.id as id_siswa_asli', 
                's.nama_lengkap', 
                'u.email', 
                's.no_hp_siswa'
            )
            ->join('tbl_jenis_bayar as j', 'j.id', '=', 't.id_jenis_bayar')
            ->join('tbl_pos_bayar as p', 'p.id', '=', 'j.id_pos_bayar')
            ->join('tbl_siswa as s', 's.id', '=', 't.id_siswa')
            ->leftJoin('tbl_users as u', 'u.id', '=', DB::raw('COALESCE(s.user_id, s.id_user)'))
            ->where('t.id', $id_tagihan)
            ->first();

        if (!$tagihan) {
            return response()->json(['status' => false, 'message' => 'Tagihan tidak valid.'], 404);
        }

        $sisa_tagihan = $tagihan->nominal_tagihan - $tagihan->nominal_terbayar;
        $nominal_fix   = $input_nominal ? (int)$input_nominal : $sisa_tagihan;

        if ($nominal_fix > $sisa_tagihan) $nominal_fix = $sisa_tagihan;
        if ($nominal_fix < 10000) {
            return response()->json(['status' => false, 'message' => 'Minimal pembayaran via Tripay adalah Rp 10.000'], 400);
        }

        $merchant_ref  = 'INV-' . date('ymdHis') . rand(100,999); 

        $payload = [
            'method'         => $kode_metode,
            'merchant_ref'   => $merchant_ref,
            'amount'         => $nominal_fix,
            'customer_name'  => $tagihan->nama_lengkap,
            'customer_email' => $tagihan->email ?? 'siswa@sekolah.sch.id',
            'customer_phone' => $tagihan->no_hp_siswa ?? '08123456789',
            'order_items'    => [
                [
                    'sku'      => 'TAG-' . $tagihan->id,
                    'name'     => $tagihan->nama_pos . ' (Cicilan)',
                    'price'    => $nominal_fix,
                    'quantity' => 1
                ]
            ]
        ];

        $result = $this->tripay->requestTransaction($payload);

        if (!isset($result['success']) || $result['success'] == false) {
            return response()->json(['status' => false, 'message' => 'Gagal koneksi ke Tripay: ' . ($result['message'] ?? 'Periksa API Key')], 500);
        }

        $data_tripay = $result['data'] ?? [];

        if (empty($data_tripay['checkout_url'])) {
            return response()->json(['status' => false, 'message' => 'Tripay gagal mengeluarkan Link Pembayaran.'], 500);
        }

        DB::table('tbl_transaksi')->insert([
            'merchant_ref'      => $merchant_ref,
            'reference'         => $data_tripay['reference'] ?? '-', 
            'id_tagihan'        => $id_tagihan,
            'id_siswa'          => $tagihan->id_siswa_asli,
            'jumlah_bayar'      => $nominal_fix,
            'fee_admin'         => ($data_tripay['total_fee'] ?? $nominal_fix) - ($data_tripay['amount'] ?? $nominal_fix),
            'total_bayar'       => $data_tripay['total_fee'] ?? $nominal_fix,
            'payment_type'      => $data_tripay['payment_name'] ?? $kode_metode, 
            'status_transaksi'  => 'UNPAID',
            'checkout_url'      => $data_tripay['checkout_url'],
            'petugas_id'        => 0, 
            'created_at'        => date('Y-m-d H:i:s')
        ]);

        $this->log->catat("Membuat Invoice Tripay via API Mobile ($kode_metode) Ref: $merchant_ref senilai Rp " . number_format($nominal_fix, 0, ',', '.'));

        return response()->json([
            'status' => true, 
            'message' => 'Invoice berhasil dibuat',
            'checkout_url' => $data_tripay['checkout_url']
        ], 200);
    }
}
