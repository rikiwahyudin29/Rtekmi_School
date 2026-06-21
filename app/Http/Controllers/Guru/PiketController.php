<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class PiketController extends Controller
{
    private function getHariIndo($day) {
        $hari = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];
        return $hari[$day] ?? 'Senin';
    }

    public function dashboard()
    {
        $guruId = Auth::user()->id; // Asumsi Auth user menempel ke tabel guru, atau jika menggunakan session tersendiri disesuaikan
        // Wait, in this Siakad Laravel, user is Auth::user()->id, but how do we get guru_id?
        // Let's assume there is a relation or tbl_guru.id_user = Auth::id()
        $guru = DB::table('tbl_guru')->where('id_user', Auth::id())->orWhere('user_id', Auth::id())->first();
        $id_guru_login = $guru ? $guru->id : Auth::id(); // Fallback if no table match (sometimes system is still a bit raw)

        $hari_ini = $this->getHariIndo(date('l'));
        $tanggal_ini = date('Y-m-d');
        $jam_sekarang = date('H:i:s');

        // Cek apakah hari ini memang jadwal piketnya
        $isPiket = DB::table('tbl_jadwal_piket')
            ->where('hari', $hari_ini)
            ->where('guru_id', $id_guru_login)
            ->exists();

        if (!$isPiket && Auth::user()->role != 'admin') {
            return redirect()->route('dashboard')->with('error', 'Hari ini bukan jadwal piket Anda.');
        }

        // ==========================================
        // 1. MONITORING KBM (Guru yang mengajar hari ini)
        // ==========================================
        $kolom_guru = DB::getSchemaBuilder()->hasColumn('tbl_guru', 'nama_guru') ? 'nama_guru' : 'nama_lengkap';
        
        $jadwal = DB::table('tbl_jadwal')
            ->select("tbl_jadwal.*", "tbl_guru.{$kolom_guru} as nama_guru", "tbl_mapel.nama_mapel", "tbl_kelas.nama_kelas")
            ->join('tbl_guru', 'tbl_guru.id', '=', 'tbl_jadwal.id_guru')
            ->join('tbl_mapel', 'tbl_mapel.id', '=', 'tbl_jadwal.id_mapel')
            ->join('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_jadwal.id_kelas')
            ->where('tbl_jadwal.hari', $hari_ini)
            ->orderBy('tbl_jadwal.jam_mulai', 'ASC')
            ->get();

        $monitoring = [];
        foreach ($jadwal as $j) {
            $cekJurnal = DB::table('tbl_jurnal')
                ->where('id_guru', $j->id_guru)
                ->where('id_kelas', $j->id_kelas)
                ->where('id_mapel', $j->id_mapel)
                ->where('tanggal', $tanggal_ini)
                ->first();

            $status = '';
            $badgeColor = '';

            if ($cekJurnal) {
                $status = 'HADIR (Mengajar)';
                $badgeColor = 'bg-emerald-100 text-emerald-700';
            } else {
                if ($jam_sekarang < $j->jam_mulai) {
                    $status = 'MENUNGGU';
                    $badgeColor = 'bg-slate-100 text-slate-500';
                } elseif ($jam_sekarang >= $j->jam_mulai && $jam_sekarang <= $j->jam_selesai) {
                    $status = 'SEDANG BERLANGSUNG';
                    $badgeColor = 'bg-amber-100 text-amber-700 animate-pulse';
                } else {
                    $status = 'ALPHA (Tidak Ada Laporan)';
                    $badgeColor = 'bg-rose-100 text-rose-700';
                }
            }

            $j->status_kbm = $status;
            $j->badge_color = $badgeColor;
            $j->data_jurnal = $cekJurnal;
            $monitoring[] = $j;
        }

        // ==========================================
        // 2. BUKU TAMU HARI INI
        // ==========================================
        $buku_tamu = DB::table('tbl_buku_tamu')
            ->where('tanggal', $tanggal_ini)
            ->orderBy('jam_datang', 'DESC')
            ->get();

        // ==========================================
        // 3. IZIN KELUAR HARI INI
        // ==========================================
        $izin_keluar = DB::table('tbl_izin_keluar')
            ->select('tbl_izin_keluar.*', 'tbl_siswa.nama_lengkap', 'tbl_kelas.nama_kelas')
            ->join('tbl_siswa', 'tbl_siswa.id', '=', 'tbl_izin_keluar.siswa_id')
            ->join('tbl_kelas', 'tbl_kelas.id', '=', 'tbl_siswa.kelas_id')
            ->whereDate('waktu_keluar', $tanggal_ini)
            ->orderBy('waktu_keluar', 'DESC')
            ->get();

        $siswa = DB::table('tbl_siswa')->orderBy('nama_lengkap', 'ASC')->get();
        $kelas = DB::table('tbl_kelas')->get();
        $semua_guru = DB::table('tbl_guru')->get();

        // Cek Jurnal Piket Saya Hari Ini
        $jurnal_saya = DB::table('tbl_jurnal_piket')
            ->where('tanggal', $tanggal_ini)
            ->where('guru_id', $id_guru_login)
            ->first();

        return Inertia::render('Guru/Piket/Dashboard', [
            'monitoring' => $monitoring,
            'buku_tamu' => $buku_tamu,
            'izin_keluar' => $izin_keluar,
            'siswa' => $siswa,
            'kelas' => $kelas,
            'semua_guru' => $semua_guru,
            'hari_ini' => $hari_ini,
            'tanggal_ini' => $tanggal_ini,
            'jurnal_saya' => $jurnal_saya,
            'id_guru_login' => $id_guru_login
        ]);
    }

    public function simpanBukuTamu(Request $request)
    {
        $request->validate([
            'nama_tamu' => 'required|string|max:150',
            'keperluan' => 'required|string',
            'bertemu_dengan' => 'required|string',
            'jam_datang' => 'required'
        ]);

        $guru = DB::table('tbl_guru')->where('id_user', Auth::id())->orWhere('user_id', Auth::id())->first();

        DB::table('tbl_buku_tamu')->insert([
            'tanggal'         => date('Y-m-d'),
            'nama_lengkap'    => $request->nama_tamu,
            'instansi_asal'   => $request->instansi_asal,
            'keperluan'       => $request->keperluan,
            'bertemu_dengan'  => $request->bertemu_dengan,
            'jam_datang'      => $request->jam_datang,
            'jam_pulang'      => $request->jam_pulang,
            'pencatat_id'     => $guru ? $guru->id : Auth::id(),
            'created_at'      => now(),
        ]);

        return back()->with('message', 'Tamu berhasil dicatat.');
    }

    public function simpanIzinKeluar(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|integer',
            'alasan' => 'required|string'
        ]);

        DB::table('tbl_izin_keluar')->insert([
            'siswa_id' => $request->siswa_id,
            'alasan' => $request->alasan,
            'waktu_keluar' => now(),
            'pencatat_id' => Auth::id() // Di tbl_izin_keluar pencatat_id di join ke users
        ]);

        return back()->with('message', 'Izin keluar berhasil ditambahkan.');
    }

    public function hapusIzinKeluar($id)
    {
        DB::table('tbl_izin_keluar')->where('id', $id)->delete();
        return back()->with('message', 'Izin keluar dibatalkan.');
    }

    public function updateJamPulangTamu($id)
    {
        DB::table('tbl_buku_tamu')->where('id', $id)->update([
            'jam_pulang' => date('H:i:s'),
        ]);
        return back()->with('message', 'Tamu telah pulang.');
    }

    public function hapusBukuTamu($id)
    {
        DB::table('tbl_buku_tamu')->where('id', $id)->delete();
        return back()->with('message', 'Buku tamu dihapus.');
    }

    public function simpanJurnal(Request $request)
    {
        $request->validate([
            'keterangan' => 'required',
            'tugas' => 'required'
        ]);

        $guru = DB::table('tbl_guru')->where('id_user', Auth::id())->orWhere('user_id', Auth::id())->first();
        $id_guru_login = $guru ? $guru->id : Auth::id();

        DB::table('tbl_jurnal_piket')->updateOrInsert(
            ['tanggal' => date('Y-m-d'), 'guru_id' => $id_guru_login],
            [
                'keterangan' => $request->keterangan,
                'tugas' => $request->tugas,
                'guru_pengganti_id' => $request->guru_pengganti_id,
                'created_at' => now()
            ]
        );

        return back()->with('message', 'Jurnal piket harian berhasil disimpan!');
    }

    public function bukuTamu()
    {
        $tanggal_ini = date('Y-m-d');
        
        $tamu = \App\Models\BukuTamu::where('tanggal', $tanggal_ini)
            ->orderBy('jam_datang', 'DESC')
            ->get();
            
        return Inertia::render('Guru/Piket/BukuTamu', [
            'tamu' => $tamu,
            'tanggal' => date('d M Y')
        ]);
    }

    public function simpanTtd(Request $request)
    {
        $request->validate([
            'id_tamu' => 'required',
            'ttd_data' => 'required'
        ]);

        $guru = DB::table('tbl_guru')->where('id_user', Auth::id())->orWhere('user_id', Auth::id())->first();

        \App\Models\BukuTamu::where('id', $request->id_tamu)->update([
            'status'           => 'Selesai',
            'ttd_piket'        => $request->ttd_data,
            'dikonfirmasi_oleh'=> $guru ? $guru->nama_lengkap ?? $guru->nama_guru ?? Auth::user()->name : Auth::user()->name,
        ]);

        return redirect()->back()->with('success', 'Tanda tangan berhasil disimpan. Tamu selesai dilayani.');
    }

    public function cetakThermal($id)
    {
        $tamu = \App\Models\BukuTamu::findOrFail($id);
        return Inertia::render('Guru/Piket/CetakThermal', [
            'tamu' => $tamu,
            'waktu_cetak' => now()->format('d/m/Y H:i:s')
        ]);
    }
}
