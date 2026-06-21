<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Buku;
use App\Models\PeminjamanBuku;
use App\Models\Siswa;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PerpustakaanController extends Controller
{
    // ==========================================
    // 1. OPAC (KATALOG BUKU)
    // ==========================================
    public function katalog(Request $request)
    {
        $query = Buku::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('judul', 'like', "%{$search}%")
                  ->orWhere('pengarang', 'like', "%{$search}%")
                  ->orWhere('kode_buku', 'like', "%{$search}%");
        }

        $buku = $query->orderBy('judul', 'ASC')->get();

        return Inertia::render('Admin/Perpustakaan/Katalog', [
            'buku' => $buku,
            'filters' => $request->only('search')
        ]);
    }

    public function simpanBuku(Request $request)
    {
        $request->validate([
            'kode_buku' => 'required|unique:tbl_buku,kode_buku',
            'judul' => 'required',
            'pengarang' => 'required',
            'stok_total' => 'required|integer|min:1'
        ]);

        $stok = $request->stok_total;
        
        Buku::create([
            'kode_buku'     => $request->kode_buku,
            'judul'         => $request->judul,
            'pengarang'     => $request->pengarang,
            'penerbit'      => $request->penerbit,
            'tahun_terbit'  => $request->tahun_terbit,
            'stok_total'    => $stok,
            'stok_tersedia' => $stok
        ]);

        return back()->with('message', 'Buku baru berhasil ditambahkan ke Katalog!');
    }

    public function hapusBuku($id)
    {
        $buku = Buku::findOrFail($id);
        $buku->delete();
        return back()->with('message', 'Buku berhasil dihapus dari Katalog.');
    }

    // ==========================================
    // 2. SIRKULASI (PINJAM & KEMBALI)
    // ==========================================
    public function sirkulasi()
    {
        $dipinjam = PeminjamanBuku::with(['siswa', 'buku'])
            ->where('status', 'Dipinjam')
            ->orderBy('tgl_pinjam', 'DESC')
            ->get();

        return Inertia::render('Admin/Perpustakaan/Sirkulasi', [
            'dipinjam' => $dipinjam
        ]);
    }

    public function prosesPinjam(Request $request)
    {
        $request->validate([
            'nis' => 'required',
            'kode_buku' => 'required'
        ]);

        $nis = $request->nis;
        $kode_buku = $request->kode_buku;

        $siswa = Siswa::where('nis', $nis)->orWhere('nisn', $nis)->first();
        if (!$siswa) {
            return back()->withErrors(['error' => 'Siswa dengan NIS tersebut tidak ditemukan!']);
        }

        $buku = Buku::where('kode_buku', $kode_buku)->first();
        if (!$buku) {
            return back()->withErrors(['error' => 'Barcode buku tidak dikenali!']);
        }

        if ($buku->stok_tersedia <= 0) {
            return back()->withErrors(['error' => 'Maaf, stok buku ini sedang habis dipinjam!']);
        }

        DB::beginTransaction();
        try {
            $tgl_pinjam = date('Y-m-d');
            $tgl_kembali = date('Y-m-d', strtotime('+7 days'));

            PeminjamanBuku::create([
                'id_siswa'               => $siswa->id,
                'id_buku'                => $buku->id,
                'tgl_pinjam'             => $tgl_pinjam,
                'tgl_kembali_seharusnya' => $tgl_kembali,
                'status'                 => 'Dipinjam',
                'denda'                  => 0
            ]);

            $buku->update(['stok_tersedia' => DB::raw('stok_tersedia - 1')]);

            DB::commit();
            return back()->with('message', 'Berhasil! Buku dipinjamkan kepada ' . $siswa->nama_lengkap);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memproses peminjaman: ' . $e->getMessage()]);
        }
    }

    public function prosesKembali($id_pinjam)
    {
        $pinjam = PeminjamanBuku::findOrFail($id_pinjam);
        if ($pinjam->status != 'Dipinjam') {
            return back()->withErrors(['error' => 'Buku ini sudah dikembalikan!']);
        }

        $tgl_sekarang = date('Y-m-d');
        $denda = 0;
        $tarif_denda = 500; // Rp 500 per hari

        if (strtotime($tgl_sekarang) > strtotime($pinjam->tgl_kembali_seharusnya)) {
            $selisih = strtotime($tgl_sekarang) - strtotime($pinjam->tgl_kembali_seharusnya);
            $hari_terlambat = floor($selisih / (60 * 60 * 24));
            $denda = $hari_terlambat * $tarif_denda;
        }

        DB::beginTransaction();
        try {
            $pinjam->update([
                'tgl_dikembalikan' => $tgl_sekarang,
                'status'           => 'Dikembalikan',
                'denda'            => $denda
            ]);

            $buku = Buku::findOrFail($pinjam->id_buku);
            $buku->update(['stok_tersedia' => DB::raw('stok_tersedia + 1')]);

            DB::commit();

            $pesan = "Buku berhasil dikembalikan.";
            if ($denda > 0) {
                $pesan .= " Terlambat! Siswa dikenakan denda Rp " . number_format($denda, 0, ',', '.');
            }

            return back()->with('message', $pesan);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memproses pengembalian: ' . $e->getMessage()]);
        }
    }
}
