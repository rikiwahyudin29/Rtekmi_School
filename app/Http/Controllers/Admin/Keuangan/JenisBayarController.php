<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\JenisBayar;
use App\Models\PosBayar;
use App\Models\TahunAjaran;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JenisBayarController extends Controller
{
    public function index()
    {
        $jenis = JenisBayar::with(['posBayar', 'tahunAjaran'])->orderBy('id', 'desc')->get();
        $pos = PosBayar::orderBy('nama_pos', 'asc')->get();
        $tahun = TahunAjaran::orderBy('id', 'desc')->get()->unique('tahun_ajaran')->values();
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();

        return Inertia::render('Admin/Keuangan/Jenis/Index', [
            'jenis' => $jenis,
            'pos'   => $pos,
            'tahun' => $tahun,
            'kelas' => $kelas
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_pos_bayar'    => 'required|integer',
            'id_tahun_ajaran' => 'required|integer',
            'tipe_bayar'      => 'required|in:BULANAN,BEBAS',
            'nominal_default' => 'required'
        ]);

        $nominal = str_replace('.', '', $request->nominal_default);

        // Cek duplikasi
        $cek = JenisBayar::where('id_pos_bayar', $request->id_pos_bayar)
                         ->where('id_tahun_ajaran', $request->id_tahun_ajaran)
                         ->count();

        if ($cek > 0) {
            return back()->with('error', 'Gagal! Jenis pembayaran ini sudah ada di tahun ajaran tersebut.');
        }

        JenisBayar::create([
            'id_pos_bayar'    => $request->id_pos_bayar,
            'id_tahun_ajaran' => $request->id_tahun_ajaran,
            'tipe_bayar'      => $request->tipe_bayar,
            'nominal_default' => $nominal
        ]);

        return back()->with('message', 'Setting pembayaran berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_pos_bayar'    => 'required|integer',
            'id_tahun_ajaran' => 'required|integer',
            'tipe_bayar'      => 'required|in:BULANAN,BEBAS',
            'nominal_default' => 'required'
        ]);

        $nominal = str_replace('.', '', $request->nominal_default);

        $jenis = JenisBayar::findOrFail($id);
        
        // Cek duplikasi jika mengubah pos atau tahun
        if ($jenis->id_pos_bayar != $request->id_pos_bayar || $jenis->id_tahun_ajaran != $request->id_tahun_ajaran) {
            $cek = JenisBayar::where('id_pos_bayar', $request->id_pos_bayar)
                             ->where('id_tahun_ajaran', $request->id_tahun_ajaran)
                             ->count();

            if ($cek > 0) {
                return back()->with('error', 'Gagal! Jenis pembayaran ini sudah ada di tahun ajaran tersebut.');
            }
        }

        $jenis->update([
            'id_pos_bayar'    => $request->id_pos_bayar,
            'id_tahun_ajaran' => $request->id_tahun_ajaran,
            'tipe_bayar'      => $request->tipe_bayar,
            'nominal_default' => $nominal
        ]);

        return back()->with('message', 'Setting pembayaran berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $jenis = JenisBayar::findOrFail($id);
        // Nanti bisa ditambahkan proteksi pengecekan apakah sudah ada transaksi
        $jenis->delete();
        
        return back()->with('message', 'Data berhasil dihapus.');
    }
}
