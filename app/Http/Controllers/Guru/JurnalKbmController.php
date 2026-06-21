<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class JurnalKbmController extends Controller
{
    private function getGuruId()
    {
        $guru = DB::table('tbl_guru')
            ->where('id_user', Auth::id())
            ->orWhere('user_id', Auth::id())
            ->first();
        return $guru ? $guru->id : Auth::id();
    }

    private function getHariIndo($day)
    {
        $hari = [
            'Sunday'    => 'Minggu', 'Monday'  => 'Senin',  'Tuesday'  => 'Selasa',
            'Wednesday' => 'Rabu',   'Thursday' => 'Kamis', 'Friday'   => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        return $hari[$day] ?? 'Senin';
    }

    /**
     * Riwayat Jurnal & Jadwal Hari Ini
     */
    public function index(Request $request)
    {
        $id_guru  = $this->getGuruId();
        $bulan    = $request->get('bulan', date('Y-m'));
        $ta_aktif = DB::table('tbl_tahun_ajaran')->where('status', 'Aktif')->first();

        // 1. Ambil Riwayat Jurnal Bulan Ini
        $query = DB::table('tbl_jurnal')
            ->select('tbl_jurnal.*', 'tbl_kelas.nama_kelas', 'tbl_mapel.nama_mapel')
            ->leftJoin('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_jurnal.id_kelas')
            ->leftJoin('tbl_mapel', 'tbl_mapel.id', '=', 'tbl_jurnal.id_mapel')
            ->where('tbl_jurnal.id_guru', $id_guru)
            ->where('tbl_jurnal.tanggal', '>=', "$bulan-01")
            ->where('tbl_jurnal.tanggal', '<=', date('Y-m-t', strtotime("$bulan-01")))
            ->orderBy('tbl_jurnal.tanggal', 'DESC')
            ->orderBy('tbl_jurnal.jam_ke', 'ASC');

        $jurnal = $query->get();

        // --- 2. Ambil Jadwal Mengajar Mingguan (Grid Jurnal) ---
        $jadwal_mingguan = [];
        $jam_master = DB::table('tbl_jam_master')->orderBy('urutan', 'ASC')->get();
        
        if ($ta_aktif) {
            $jadwal_mingguan = DB::table('tbl_jadwal')
                ->select(
                    'tbl_jadwal.id', 'tbl_jadwal.id_kelas', 'tbl_jadwal.id_mapel', 'tbl_jadwal.hari',
                    'tbl_mapel.nama_mapel', 'tbl_kelas.nama_kelas',
                    'tbl_jadwal.jam_mulai', 'tbl_jadwal.jam_selesai'
                )
                ->join('tbl_mapel', 'tbl_mapel.id', '=', 'tbl_jadwal.id_mapel')
                ->join('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_jadwal.id_kelas')
                ->where('tbl_jadwal.id_guru', $id_guru)
                ->where('tbl_jadwal.id_tahun_ajaran', $ta_aktif->id)
                ->orderByRaw('FIELD(tbl_jadwal.hari, "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu")')
                ->orderBy('tbl_jadwal.jam_mulai', 'ASC')
                ->get();

            // Mapping hari Indo ke bahasa Inggris untuk strtotime
            $hariMap = [
                'Senin' => 'Monday',
                'Selasa' => 'Tuesday',
                'Rabu' => 'Wednesday',
                'Kamis' => 'Thursday',
                'Jumat' => 'Friday',
                'Sabtu' => 'Saturday',
                'Minggu' => 'Sunday'
            ];

            // Dapatkan awal minggu ini (Senin)
            // Jika hari ini adalah Minggu, reset ke minggu berikutnya (Senin besok)
            if (date('w') == 0) {
                $startOfWeek = strtotime('monday next week');
            } else {
                $startOfWeek = strtotime('monday this week');
            }
            $jadwal_mingguan = $jadwal_mingguan->map(function ($j) use ($id_guru, $hariMap, $startOfWeek) {
                // Hitung tanggal untuk hari ini pada minggu berjalan
                $daysOffset = array_search($j->hari, array_keys($hariMap));
                if ($daysOffset === false) $daysOffset = 0;
                $tanggal_kbm = date('Y-m-d', strtotime("+$daysOffset days", $startOfWeek));

                $j->tanggal_kbm = $tanggal_kbm;

                // Cek apakah sudah isi jurnal di tanggal tersebut
                $sudah_jurnal = DB::table('tbl_jurnal')
                    ->where('id_kelas', $j->id_kelas)
                    ->where('id_mapel', $j->id_mapel)
                    ->where('id_guru', $id_guru)
                    ->where('tanggal', $tanggal_kbm)
                    ->exists();

                // Cek apakah ada tugas di tanggal tersebut
                $ada_tugas = DB::table('tbl_tugas')
                    ->where('kelas_id', $j->id_kelas)
                    ->where('mapel_id', $j->id_mapel)
                    ->where('guru_id', $id_guru)
                    ->whereDate('created_at', $tanggal_kbm)
                    ->exists();

                if ($sudah_jurnal) {
                    $j->status_kbm = 'Selesai';
                } elseif ($ada_tugas) {
                    $j->status_kbm = 'Tugas Saja';
                } else {
                    // Jika tanggal KBM lebih besar dari hari ini, statusnya 'Belum Waktunya'
                    if ($tanggal_kbm > date('Y-m-d')) {
                        $j->status_kbm = 'Belum Waktunya';
                    } else {
                        $j->status_kbm = 'Belum';
                    }
                }

                return $j;
            });
        }

        return Inertia::render('Guru/ELearning/Jurnal/Index', [
            'jurnal'      => $jurnal,
            'jadwal'      => $jadwal_mingguan,
            'jam_master'  => $jam_master,
            'hari_ini'    => $this->getHariIndo(date('l')),
            'tanggal'     => date('Y-m-d'),
            'bulan'       => $bulan,
            'ta_aktif'    => $ta_aktif,
        ]);
    }

    /**
     * Simpan Jurnal → redirect ke Absen Siswa
     */
    public function simpan(Request $request)
    {
        $id_guru  = $this->getGuruId();
        $ta_aktif = DB::table('tbl_tahun_ajaran')->where('status', 'Aktif')->first();

        if (!$ta_aktif) {
            return back()->with('error', 'Tidak ada Tahun Ajaran aktif!');
        }

        $request->validate([
            'id_kelas' => 'required',
            'id_mapel' => 'required',
            'materi'   => 'required|string',
        ]);

        $fotoKegiatan = null;
        if ($request->hasFile('foto_kegiatan')) {
            $file         = $request->file('foto_kegiatan');
            $fotoKegiatan = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/jurnal'), $fotoKegiatan);
        }

        $id_jurnal = DB::table('tbl_jurnal')->insertGetId([
            'id_guru'         => $id_guru,
            'id_tahun_ajaran' => $ta_aktif->id,
            'id_kelas'        => $request->id_kelas,
            'id_mapel'        => $request->id_mapel,
            'tanggal'         => $request->tanggal ?? date('Y-m-d'),
            'jam_ke'          => $request->jam_ke,
            'materi'          => $request->materi,
            'keterangan'      => $request->keterangan,
            'foto_kegiatan'   => $fotoKegiatan,
            'created_at'      => now(),
        ]);

        return redirect()->route('guru.elearning.jurnal.absen', $id_jurnal)
            ->with('success', 'Jurnal berhasil disimpan! Silakan isi absensi siswa.');
    }

    /**
     * Halaman Absen Siswa per Jurnal
     */
    public function absen($id_jurnal)
    {
        $jurnal = DB::table('tbl_jurnal')
            ->select('tbl_jurnal.*', 'tbl_kelas.nama_kelas', 'tbl_mapel.nama_mapel')
            ->join('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_jurnal.id_kelas')
            ->join('tbl_mapel', 'tbl_mapel.id', '=', 'tbl_jurnal.id_mapel')
            ->where('tbl_jurnal.id', $id_jurnal)
            ->first();

        if (!$jurnal) {
            return redirect()->route('guru.elearning.jurnal.index');
        }

        $siswa = DB::table('tbl_siswa')
            ->where('kelas_id', $jurnal->id_kelas)
            ->orderBy('nama_lengkap')
            ->get();

        $existing = DB::table('tbl_absensi_mapel')
            ->where('id_jurnal', $id_jurnal)
            ->get()
            ->keyBy('id_siswa')
            ->map(fn($e) => $e->status);

        return Inertia::render('Guru/ELearning/Jurnal/Absen', [
            'jurnal'   => $jurnal,
            'siswa'    => $siswa,
            'existing' => $existing,
        ]);
    }

    /**
     * Simpan Absensi Siswa
     */
    public function simpanAbsen(Request $request)
    {
        $id_jurnal = $request->id_jurnal;
        $status    = $request->status ?? [];

        // Hapus lama, insert baru (clean update)
        DB::table('tbl_absensi_mapel')->where('id_jurnal', $id_jurnal)->delete();

        $batch = [];
        foreach ($status as $id_siswa => $st) {
            $batch[] = [
                'id_jurnal' => $id_jurnal,
                'id_siswa'  => $id_siswa,
                'status'    => $st,
            ];
        }

        if (!empty($batch)) {
            DB::table('tbl_absensi_mapel')->insert($batch);
        }

        return redirect()->route('guru.elearning.jurnal.index')
            ->with('success', 'Jurnal & Absensi berhasil disimpan!');
    }

    /**
     * Detail Absensi (JSON untuk modal)
     */
    public function detailAbsen($id_jurnal)
    {
        $data = DB::table('tbl_absensi_mapel')
            ->select('tbl_siswa.nama_lengkap', 'tbl_absensi_mapel.status')
            ->join('tbl_siswa', 'tbl_siswa.id', '=', 'tbl_absensi_mapel.id_siswa')
            ->where('id_jurnal', $id_jurnal)
            ->orderBy('tbl_siswa.nama_lengkap')
            ->get();

        return response()->json($data);
    }

    public function hapus($id)
    {
        DB::table('tbl_absensi_mapel')->where('id_jurnal', $id)->delete();
        DB::table('tbl_jurnal')->where('id', $id)->delete();
        return back()->with('success', 'Jurnal berhasil dihapus.');
    }
}
