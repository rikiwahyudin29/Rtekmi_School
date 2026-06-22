<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use App\Models\Transaksi;
use App\Models\LogKeuangan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class KeuanganController extends Controller
{
    public function index()
    {
        $siswa_id = Auth::user()->siswa->id ?? 0;

        // Ambil data tagihan beserta jenis bayar
        $tagihan = Tagihan::with('jenisBayar')
            ->where('id_siswa', $siswa_id)
            ->orderBy('id', 'desc')
            ->get();

        // Hitung ringkasan
        $total_tagihan = 0;
        $total_dibayar = 0;
        $sisa_tagihan = 0;

        foreach ($tagihan as $t) {
            $total_tagihan += $t->nominal_tagihan;
            $total_dibayar += $t->nominal_terbayar;
            // Jika ada selisih
            $sisa = $t->nominal_tagihan - $t->nominal_terbayar;
            if ($sisa > 0) {
                $sisa_tagihan += $sisa;
            }
        }

        // Riwayat transaksi pembayaran tagihan
        // Transaksi biasanya nyambung ke tabel transaksi
        $riwayat_transaksi = Transaksi::with(['tagihan.jenisBayar', 'petugas'])
            ->whereHas('tagihan', function($q) use ($siswa_id) {
                $q->where('id_siswa', $siswa_id);
            })
            ->where('status_transaksi', 'SUCCESS')
            ->orderBy('tanggal_bayar', 'desc')
            ->limit(20)
            ->get();

        return Inertia::render('Siswa/Keuangan/Index', [
            'tagihan' => $tagihan,
            'riwayat_transaksi' => $riwayat_transaksi,
            'ringkasan' => [
                'total_tagihan' => $total_tagihan,
                'total_dibayar' => $total_dibayar,
                'sisa_tagihan'  => $sisa_tagihan
            ]
        ]);
    }

    public function bayarQris(Request $request)
    {
        $request->validate([
            'tagihan_id'    => 'required|exists:tbl_tagihan,id',
            'nominal_bayar' => 'required|numeric|min:10000'
        ], [
            'nominal_bayar.min' => 'Minimum pembayaran via QRIS adalah Rp 10.000'
        ]);

        $siswa = Auth::user()->siswa;
        if (!$siswa) {
            return back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $tagihan = Tagihan::with('jenisBayar')
            ->where('id', $request->tagihan_id)
            ->where('id_siswa', $siswa->id)
            ->first();

        if (!$tagihan) {
            return back()->with('error', 'Tagihan tidak ditemukan atau bukan milik Anda.');
        }

        $sisa_tagihan = $tagihan->nominal_tagihan - $tagihan->nominal_terbayar;

        if ($sisa_tagihan <= 0) {
            return back()->with('error', 'Tagihan ini sudah lunas.');
        }

        $nominal_bayar = (int) $request->nominal_bayar;
        
        if ($nominal_bayar > $sisa_tagihan) {
            return back()->with('error', 'Nominal bayar tidak boleh melebihi sisa tagihan.');
        }

        // Cek apakah sudah ada transaksi UNPAID untuk nominal yang SAMA
        $existingTx = Transaksi::where('id_tagihan', $tagihan->id)
            ->where('status_transaksi', 'UNPAID')
            ->where('jumlah_bayar', $nominal_bayar)
            ->orderBy('id', 'desc')
            ->first();

        if ($existingTx && !empty($existingTx->checkout_url)) {
            // Langsung arahkan ke URL yang lama jika nominal sama
            return Inertia::location($existingTx->checkout_url);
        }

        $tripay = new \App\Services\TripayService();
        $merchantRef = 'INV-' . time() . '-' . $tagihan->id;

        $data = [
            'method'         => 'QRIS',
            'merchant_ref'   => $merchantRef,
            'amount'         => $nominal_bayar,
            'customer_name'  => $siswa->nama_lengkap,
            'customer_email' => Auth::user()->email ?? 'siswa@example.com',
            'customer_phone' => $siswa->no_hp ?? '080000000000',
            'order_items'    => [
                [
                    'sku'      => 'TAGIHAN',
                    'name'     => $tagihan->jenisBayar->nama_pembayaran ?? 'Pembayaran SPP/Lainnya',
                    'price'    => $nominal_bayar,
                    'quantity' => 1
                ]
            ]
        ];

        $response = $tripay->requestTransaction($data);

        if (isset($response['success']) && $response['success']) {
            $txData = $response['data'];

            Transaksi::create([
                'kode_transaksi'   => $merchantRef,
                'merchant_ref'     => $merchantRef,
                'reference'        => $txData['reference'],
                'id_tagihan'       => $tagihan->id,
                'id_siswa'         => $siswa->id,
                'jumlah_bayar'     => $nominal_bayar,
                'fee_admin'        => $txData['total_fee'],
                'total_bayar'      => $txData['amount'],
                'status_transaksi' => 'UNPAID',
                'checkout_url'     => $txData['checkout_url'],
                'payment_type'     => 'QRIS',
                'tanggal_bayar'    => now()
            ]);

            // Catat ke Log Aktivitas Keuangan
            LogKeuangan::create([
                'user_id'     => Auth::id(),
                'nama_user'   => Auth::user()->name ?? $siswa->nama_lengkap,
                'role'        => Auth::user()->role ?? 'siswa',
                'ip_address'  => $request->ip(),
                'device_info' => $request->header('User-Agent'),
                'aksi'        => "Membuat invoice pembayaran QRIS sebesar Rp " . number_format($nominal_bayar, 0, ',', '.') . " untuk tagihan #" . $tagihan->id . " (Ref: $merchantRef)"
            ]);

            return Inertia::location($txData['checkout_url']);
        }

        return back()->with('error', 'Gagal memproses pembayaran: ' . ($response['message'] ?? 'Kesalahan tidak diketahui.'));
    }
}
