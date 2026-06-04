<?php

namespace App\Http\Controllers\Admin\Kesiswaan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Pendaftar;
use App\Models\Siswa;
use App\Services\WaService;
use Illuminate\Support\Facades\DB;

class PpdbController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        
        $query = Pendaftar::orderBy('tgl_daftar', 'DESC');
        
        if ($status) {
            $query->where('status_pendaftaran', $status);
        }

        $pendaftar = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Pendaftar::count(),
            'pending' => Pendaftar::where('status_pendaftaran', 'Pending')->count(),
            'diterima' => Pendaftar::where('status_pendaftaran', 'Diterima')->count(),
            'ditolak' => Pendaftar::where('status_pendaftaran', 'Ditolak')->count(),
        ];

        return Inertia::render('Admin/Kesiswaan/Ppdb/Index', [
            'pendaftar' => $pendaftar,
            'stats' => $stats,
            'filters' => $request->only(['status'])
        ]);
    }

    public function show($id)
    {
        $pendaftar = Pendaftar::findOrFail($id);
        return Inertia::render('Admin/Kesiswaan/Ppdb/Show', [
            'pendaftar' => $pendaftar
        ]);
    }

    public function updateStatus(Request $request, $id, WaService $waService)
    {
        $pendaftar = Pendaftar::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:Pending,Diterima,Cadangan,Ditolak',
            'catatan' => 'nullable|string'
        ]);

        $statusLama = $pendaftar->status_pendaftaran;
        $statusBaru = $request->status;

        $pendaftar->status_pendaftaran = $statusBaru;
        if ($request->catatan) {
            $pendaftar->catatan_admin = $request->catatan;
        }
        $pendaftar->save();

        // Kirim Notifikasi WA jika status berubah menjadi Diterima atau Ditolak
        if ($statusLama !== $statusBaru && in_array($statusBaru, ['Diterima', 'Ditolak'])) {
            $pesanWA = "Halo *{$pendaftar->nama_lengkap}*,\n\n";
            $pesanWA .= "Status pendaftaran PPDB Anda (No: {$pendaftar->no_pendaftaran}) telah diperbarui menjadi: *{$statusBaru}*.\n\n";
            
            if ($statusBaru === 'Diterima') {
                $pesanWA .= "Selamat! Anda telah diterima. Silakan cek informasi pendaftaran ulang di portal sekolah atau hubungi panitia untuk langkah selanjutnya.\n\n";
            } else {
                $pesanWA .= "Mohon maaf, Anda belum dapat diterima pada periode ini.\n\n";
            }

            if ($pendaftar->catatan_admin) {
                $pesanWA .= "Catatan Panitia:\n_{$pendaftar->catatan_admin}_\n\n";
            }

            $pesanWA .= "Salam,\nPanitia PPDB";
            
            $waService->kirim($pendaftar->no_hp_siswa, $pesanWA);
        }

        return redirect()->route('admin.ppdb.show', $pendaftar->id)->with('message', 'Status pendaftar berhasil diperbarui.');
    }

    public function migrateToSiswa($id)
    {
        $pendaftar = Pendaftar::findOrFail($id);

        if ($pendaftar->status_pendaftaran !== 'Diterima') {
            return back()->with('error', 'Hanya pendaftar dengan status Diterima yang bisa dimigrasi menjadi Siswa.');
        }

        if ($pendaftar->is_migrated) {
            return back()->with('error', 'Pendaftar ini sudah pernah dimigrasi ke data Siswa.');
        }

        DB::beginTransaction();
        try {
            // Kita cari jurusan_id berdasarkan nama jurusan
            $jurusan = DB::table('tbl_jurusan')->where('nama_jurusan', $pendaftar->jurusan_minat)->first();
            $jurusanId = $jurusan ? $jurusan->id : null;

            Siswa::create([
                'nisn' => $pendaftar->nisn,
                'nik' => $pendaftar->nik,
                'nama_lengkap' => $pendaftar->nama_lengkap,
                'jk' => $pendaftar->jk,
                'jenis_kelamin' => $pendaftar->jk,
                'tempat_lahir' => $pendaftar->tempat_lahir,
                'tanggal_lahir' => $pendaftar->tgl_lahir,
                'tgl_lahir' => $pendaftar->tgl_lahir,
                'agama' => $pendaftar->agama,
                'alamat' => $pendaftar->alamat,
                'sekolah_asal' => $pendaftar->asal_sekolah,
                'no_hp_siswa' => $pendaftar->no_hp_siswa,
                'no_hp_ortu' => $pendaftar->no_hp_ortu,
                'nama_ayah' => $pendaftar->nama_ayah,
                'pekerjaan_ayah' => $pendaftar->pekerjaan_ayah,
                'nama_ibu' => $pendaftar->nama_ibu,
                'pekerjaan_ibu' => $pendaftar->pekerjaan_ibu,
                'nama_wali' => $pendaftar->nama_wali,
                'pekerjaan_wali' => $pendaftar->pekerjaan_wali,
                'foto' => $pendaftar->foto ?: 'default.png',
                'status_siswa' => 'Aktif',
                'jurusan_id' => $jurusanId,
                'tanggal_diterima' => date('Y-m-d'),
                'tahun_angkatan' => date('Y'),
                'password' => bcrypt($pendaftar->nisn) // Default password is NISN
            ]);

            $pendaftar->is_migrated = true;
            $pendaftar->save();

            DB::commit();
            return redirect()->route('admin.ppdb.show', $pendaftar->id)->with('message', 'Data pendaftar berhasil dipindahkan ke Data Siswa Aktif!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memigrasi data: ' . $e->getMessage());
        }
    }
}
