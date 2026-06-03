<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\Transaksi;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->get('q');
        $siswa = [];

        if ($keyword) {
            $siswa = Siswa::with('kelas')
                ->where('nama_lengkap', 'like', "%{$keyword}%")
                ->orWhere('nisn', 'like', "%{$keyword}%")
                ->limit(10)
                ->get();
        }

        return Inertia::render('Admin/Keuangan/Pembayaran/Index', [
            'siswa' => $siswa,
            'keyword' => $keyword
        ]);
    }

    public function transaksi($id_siswa)
    {
        $siswa = Siswa::with('kelas')->findOrFail($id_siswa);

        $tagihan = Tagihan::with(['jenisBayar.posBayar'])
            ->where('id_siswa', $id_siswa)
            ->orderBy('status_bayar', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        $riwayat = Transaksi::with(['tagihan.jenisBayar.posBayar', 'petugas'])
            ->where('id_siswa', $id_siswa)
            ->orderBy('created_at', 'desc')
            ->get();

        $total_tunggakan = $tagihan->sum(function ($t) {
            return $t->nominal_tagihan - $t->nominal_terbayar;
        });

        return Inertia::render('Admin/Keuangan/Pembayaran/Transaksi', [
            'siswa'           => $siswa,
            'tagihan'         => $tagihan,
            'riwayat'         => $riwayat,
            'total_tunggakan' => $total_tunggakan
        ]);
    }

    public function prosesBayar(Request $request)
    {
        $request->validate([
            'id_tagihan' => 'required|integer',
            'jumlah_bayar' => 'required',
            'id_siswa' => 'required|integer'
        ]);

        $bayar = (float) str_replace('.', '', $request->jumlah_bayar);
        
        $tagihan = Tagihan::findOrFail($request->id_tagihan);
        
        $sisa = $tagihan->nominal_tagihan - $tagihan->nominal_terbayar;
        if ($bayar > ($sisa + 1)) {
            return back()->with('error', 'Kelebihan bayar!');
        }

        $trx = Transaksi::create([
            'kode_transaksi' => 'TRX-' . date('ymdHis') . rand(10,99),
            'id_tagihan'     => $tagihan->id,
            'id_siswa'       => $request->id_siswa,
            'jumlah_bayar'   => $bayar,
            'petugas_id'     => auth()->id(),
            'payment_type'   => 'TUNAI',
            'status_transaksi' => 'SUCCESS',
            'tanggal_bayar'  => Carbon::now()
        ]);

        $total_terbayar = $tagihan->nominal_terbayar + $bayar;
        $status_baru = ($total_terbayar >= $tagihan->nominal_tagihan) ? 'LUNAS' : 'CICIL';

        $tagihan->update([
            'nominal_terbayar' => $total_terbayar,
            'status_bayar'     => $status_baru
        ]);

        // Note: Logging and WA can be implemented later or via Observers.

        return redirect()->route('admin.keuangan.pembayaran.transaksi', $request->id_siswa)->with('message', 'Pembayaran berhasil disimpan.');
    }

    public function batal(Request $request)
    {
        $request->validate([
            'id_transaksi' => 'required|integer'
        ]);

        $trx = Transaksi::findOrFail($request->id_transaksi);
        $tagihan = Tagihan::findOrFail($trx->id_tagihan);

        $saldo_baru = $tagihan->nominal_terbayar - $trx->jumlah_bayar;
        
        if ($saldo_baru <= 0) {
            $saldo_baru = 0;
            $status_baru = 'BELUM';
        } elseif ($saldo_baru < $tagihan->nominal_tagihan) {
            $status_baru = 'CICIL';
        } else {
            $status_baru = 'LUNAS'; 
        }

        $tagihan->update([
            'nominal_terbayar' => $saldo_baru,
            'status_bayar'     => $status_baru
        ]);

        $trx->delete();

        return back()->with('message', 'Transaksi berhasil dibatalkan. Saldo tagihan dikembalikan.');
    }

    public function cetakThermal($id)
    {
        $trx = Transaksi::with(['tagihan.jenisBayar.posBayar', 'tagihan.jenisBayar.tahunAjaran', 'siswa.kelas', 'petugas'])->findOrFail($id);
        $sekolah = Sekolah::first();
        
        return view('keuangan.cetak_thermal', compact('trx', 'sekolah'));
    }

    public function cetakInvoice($id)
    {
        $trx = Transaksi::with(['tagihan.jenisBayar.posBayar', 'tagihan.jenisBayar.tahunAjaran', 'siswa.kelas', 'petugas'])->findOrFail($id);
        $sekolah = Sekolah::first();
        
        return view('keuangan.cetak_invoice', compact('trx', 'sekolah'));
    }
}
