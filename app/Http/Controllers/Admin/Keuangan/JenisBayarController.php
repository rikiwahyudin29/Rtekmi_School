<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\JenisBayar;
use App\Models\PosBayar;
use App\Models\TahunAjaran;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\JenisBayarJurusan;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JenisBayarController extends Controller
{
    public function index()
    {
        $jenis = JenisBayar::with(['posBayar', 'tahunAjaran', 'jenisBayarJurusan.jurusan'])->orderBy('id', 'desc')->get();
        $pos = PosBayar::orderBy('nama_pos', 'asc')->get();
        $tahun = TahunAjaran::orderBy('id', 'desc')->get()->unique('tahun_ajaran')->values();
        $kelas = Kelas::orderBy('nama_kelas', 'asc')->get();
        $jurusan = Jurusan::orderBy('nama_jurusan', 'asc')->get();

        return Inertia::render('Admin/Keuangan/Jenis/Index', [
            'jenis' => $jenis,
            'pos'   => $pos,
            'tahun' => $tahun,
            'kelas' => $kelas,
            'jurusan' => $jurusan
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_pos_bayar'    => 'required|integer',
            'id_tahun_ajaran' => 'required|integer',
            'tipe_bayar'      => 'required|in:BULANAN,BEBAS',
            'is_per_jurusan'  => 'boolean',
        ]);

        $nominal_default = 0;
        if (!$request->is_per_jurusan) {
            $nominal_default = str_replace('.', '', $request->nominal_default ?? 0);
        }

        // Cek duplikasi
        $cek = JenisBayar::where('id_pos_bayar', $request->id_pos_bayar)
                         ->where('id_tahun_ajaran', $request->id_tahun_ajaran)
                         ->count();

        if ($cek > 0) {
            return back()->with('error', 'Gagal! Jenis pembayaran ini sudah ada di tahun ajaran tersebut.');
        }

        $jenis = JenisBayar::create([
            'id_pos_bayar'    => $request->id_pos_bayar,
            'id_tahun_ajaran' => $request->id_tahun_ajaran,
            'tipe_bayar'      => $request->tipe_bayar,
            'is_per_jurusan'  => $request->is_per_jurusan ? 1 : 0,
            'nominal_default' => $nominal_default
        ]);

        if ($request->is_per_jurusan && $request->has('nominal_jurusan')) {
            foreach ($request->nominal_jurusan as $id_jur => $nom) {
                JenisBayarJurusan::create([
                    'id_jenis_bayar' => $jenis->id,
                    'id_jurusan' => $id_jur,
                    'nominal' => str_replace('.', '', $nom ?? 0)
                ]);
            }
        }

        return back()->with('message', 'Setting pembayaran berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'id_pos_bayar'    => 'required|integer',
            'id_tahun_ajaran' => 'required|integer',
            'tipe_bayar'      => 'required|in:BULANAN,BEBAS',
            'is_per_jurusan'  => 'boolean',
        ]);

        $nominal_default = 0;
        if (!$request->is_per_jurusan) {
            $nominal_default = str_replace('.', '', $request->nominal_default ?? 0);
        }

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
            'is_per_jurusan'  => $request->is_per_jurusan ? 1 : 0,
            'nominal_default' => $nominal_default
        ]);

        // Re-sync jurusan nominals
        JenisBayarJurusan::where('id_jenis_bayar', $jenis->id)->delete();
        if ($request->is_per_jurusan && $request->has('nominal_jurusan')) {
            foreach ($request->nominal_jurusan as $id_jur => $nom) {
                JenisBayarJurusan::create([
                    'id_jenis_bayar' => $jenis->id,
                    'id_jurusan' => $id_jur,
                    'nominal' => str_replace('.', '', $nom ?? 0)
                ]);
            }
        }

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
