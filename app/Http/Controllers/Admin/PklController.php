<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PklKelompok;
use App\Models\Guru;
use App\Models\Dudi;

class PklController extends Controller
{
    // ==========================================
    // 1. MASTER DUDI
    // ==========================================
    public function index()
    {
        $dudi = Dudi::orderBy('nama_dudi', 'asc')->get();

        return Inertia::render('Admin/Pkl/Dudi', [
            'dudi' => $dudi
        ]);
    }

    public function simpanDudi(Request $request)
    {
        $request->validate([
            'nama_dudi' => 'required|string',
            'bidang_usaha' => 'required|string',
            'nama_pimpinan' => 'required|string',
            'alamat_lengkap' => 'required|string',
            'radius_absen' => 'required|integer'
        ]);

        Dudi::create($request->all());

        return redirect()->route('admin.pkl.index')->with('message', 'Data DU/DI berhasil ditambahkan!');
    }

    public function updateDudi(Request $request)
    {
        $id = $request->input('id');
        $request->validate([
            'nama_dudi' => 'required|string',
            'bidang_usaha' => 'required|string',
            'nama_pimpinan' => 'required|string',
            'alamat_lengkap' => 'required|string',
            'radius_absen' => 'required|integer'
        ]);

        $dudi = Dudi::findOrFail($id);
        $dudi->update($request->all());

        return redirect()->route('admin.pkl.index')->with('message', 'Data DU/DI berhasil diperbarui!');
    }

    public function deleteDudi($id)
    {
        $dudi = Dudi::findOrFail($id);
        $dudi->delete();
        return redirect()->route('admin.pkl.index')->with('message', 'Data DU/DI berhasil dihapus!');
    }

    public function dashboard(Request $request)
    {
        // 1. Ambil daftar kelas yang diset sebagai Kelas PKL
        $kelas_pkl_raw = \Illuminate\Support\Facades\DB::table('tbl_pkl_kelas')->get()->toArray();
        $active_class_ids = array_column($kelas_pkl_raw, 'kelas_id');

        if (empty($active_class_ids)) {
            $active_class_ids = [0]; 
        }

        $semua_kelas = \Illuminate\Support\Facades\DB::table('tbl_kelas')->orderBy('nama_kelas', 'ASC')->get()->toArray();
        $list_kelas_aktif = \Illuminate\Support\Facades\DB::table('tbl_kelas')->whereIn('id', $active_class_ids)->orderBy('nama_kelas', 'ASC')->get()->toArray();
        $filter_kelas_id = $request->query('kelas_id'); 
        $target_kelas_ids = !empty($filter_kelas_id) ? [$filter_kelas_id] : $active_class_ids;

        // --- BAGIAN 1: MENGHITUNG STATISTIK PENEMPATAN ---
        $total_siswa = \Illuminate\Support\Facades\DB::table('tbl_siswa')
                          ->where('status_siswa', '!=', 'Lulus')
                          ->whereIn('kelas_id', $target_kelas_ids)
                          ->count();

        $b_dit = \Illuminate\Support\Facades\DB::table('tbl_pkl')
                    ->select('tbl_pkl.siswa_id')
                    ->join('tbl_siswa', 'tbl_siswa.id', '=', 'tbl_pkl.siswa_id')
                    ->where('tbl_pkl.status_pkl', '!=', 'Dibatalkan')
                    ->whereIn('tbl_siswa.kelas_id', $target_kelas_ids)
                    ->groupBy('tbl_pkl.siswa_id')
                    ->get()->toArray();
        $siswa_ditempatkan = count($b_dit);

        $siswa_belum = $total_siswa - $siswa_ditempatkan;
        if ($siswa_belum < 0) $siswa_belum = 0;

        $pkl_aktif = \Illuminate\Support\Facades\DB::table('tbl_pkl')
                        ->join('tbl_siswa', 'tbl_siswa.id', '=', 'tbl_pkl.siswa_id')
                        ->where('tbl_pkl.status_pkl', 'Aktif')
                        ->whereIn('tbl_siswa.kelas_id', $target_kelas_ids)
                        ->count();

        $pkl_selesai = \Illuminate\Support\Facades\DB::table('tbl_pkl')
                          ->join('tbl_siswa', 'tbl_siswa.id', '=', 'tbl_pkl.siswa_id')
                          ->where('tbl_pkl.status_pkl', 'Selesai')
                          ->whereIn('tbl_siswa.kelas_id', $target_kelas_ids)
                          ->count();

        $total_dudi = Dudi::count();
        $persen_penempatan = ($total_siswa > 0) ? round(($siswa_ditempatkan / $total_siswa) * 100) : 0;

        // --- BAGIAN 2: MENGHITUNG LEADERBOARD KINERJA KEMITRAAN ---
        $builder = \Illuminate\Support\Facades\DB::table('tbl_pkl');
        $builder->select('tbl_pkl.dudi_id', 'tbl_pkl.guru_id', 'tbl_dudi.nama_dudi', 'tbl_guru.nama_lengkap as nama_guru', \Illuminate\Support\Facades\DB::raw('COUNT(tbl_pkl.id) as total_siswa'));
        $builder->join('tbl_dudi', 'tbl_dudi.id', '=', 'tbl_pkl.dudi_id');
        $builder->join('tbl_guru', 'tbl_guru.id', '=', 'tbl_pkl.guru_id');
        $builder->join('tbl_siswa', 'tbl_siswa.id', '=', 'tbl_pkl.siswa_id');
        $builder->where('tbl_pkl.status_pkl', 'Aktif');
        $builder->whereIn('tbl_siswa.kelas_id', $target_kelas_ids);
        $builder->groupBy('tbl_pkl.dudi_id', 'tbl_pkl.guru_id', 'tbl_dudi.nama_dudi', 'tbl_guru.nama_lengkap');
        $kemitraan = $builder->get()->toArray();

        $data_performa = [];
        $akumulasi_global = 0;
        $count_kemitraan = 0;

        foreach ($kemitraan as $mitra) {
            // A. Kinerja Siswa
            $siswa = \Illuminate\Support\Facades\DB::table('tbl_pkl')
                ->select('tbl_pkl.*')
                ->join('tbl_siswa', 'tbl_siswa.id', '=', 'tbl_pkl.siswa_id')
                ->where('tbl_pkl.dudi_id', $mitra->dudi_id)
                ->where('tbl_pkl.guru_id', $mitra->guru_id)
                ->where('tbl_pkl.status_pkl', 'Aktif')
                ->whereIn('tbl_siswa.kelas_id', $target_kelas_ids)
                ->get()->toArray();

            $total_skor_siswa = 0;
            $actual_siswa_count = 0;

            foreach($siswa as $s) {
                $start = strtotime($s->tgl_mulai);
                $end = strtotime($s->tgl_selesai);
                $target_hari = 0;
                for ($i = $start; $i <= $end; $i += 86400) { if (date('w', $i) != 0 && date('w', $i) != 6) $target_hari++; }
                if($target_hari == 0) $target_hari = 1;

                // Hitung absen di sistem jika ada tabelnya, jika tidak sementara 0
                // Misal tabel: tbl_presensi atau tbl_pkl_absen
                $hadir = 0; 
                if (\Illuminate\Support\Facades\Schema::hasTable('tbl_pkl_absen')) {
                    $hadir = \Illuminate\Support\Facades\DB::table('tbl_pkl_absen')->where('pkl_id', $s->id)->where('status', 'Hadir')->count();
                }
                $p_hadir = min(($hadir / $target_hari) * 100, 100);

                $jurnal = 0;
                if (\Illuminate\Support\Facades\Schema::hasTable('tbl_pkl_jurnal')) {
                    $jurnal = \Illuminate\Support\Facades\DB::table('tbl_pkl_jurnal')->where('pkl_id', $s->id)->where('status_jurnal', 'Disetujui')->count();
                }
                $p_jurnal = min(($jurnal / $target_hari) * 100, 100);

                $p_laporan = 0;
                if (\Illuminate\Support\Facades\Schema::hasTable('tbl_pkl_laporan')) {
                    $laporan = \Illuminate\Support\Facades\DB::table('tbl_pkl_laporan')->where('pkl_id', $s->id)->first();
                    if($laporan) {
                        if($laporan->status_laporan == 'Disetujui') $p_laporan = 100;
                        elseif($laporan->status_laporan == 'Revisi') $p_laporan = 50;
                        else $p_laporan = 25;
                    }
                }

                $nilai_sistem = ($p_hadir * 0.4) + ($p_jurnal * 0.4) + ($p_laporan * 0.2);
                $total_skor_siswa += $nilai_sistem;
                $actual_siswa_count++;
            }

            $rata_siswa = $actual_siswa_count > 0 ? ($total_skor_siswa / $actual_siswa_count) : 0;

            // B. Kinerja Guru
            $kunjungan = 0;
            if (\Illuminate\Support\Facades\Schema::hasTable('tbl_pkl_kunjungan')) {
                $kunjungan = \Illuminate\Support\Facades\DB::table('tbl_pkl_kunjungan')
                    ->where('guru_id', $mitra->guru_id)->where('dudi_id', $mitra->dudi_id)
                    ->count();
            }
            $kpi_guru = min(($kunjungan / 3) * 100, 100); 

            // C. Akumulasi (50-50)
            $skor_akumulasi = ($rata_siswa * 0.5) + ($kpi_guru * 0.5);
            $akumulasi_global += $skor_akumulasi;
            $count_kemitraan++;

            if($skor_akumulasi >= 90) $grade = 'A';
            elseif($skor_akumulasi >= 80) $grade = 'B';
            elseif($skor_akumulasi >= 70) $grade = 'C';
            elseif($skor_akumulasi >= 60) $grade = 'D';
            else $grade = 'E';

            $data_performa[] = [
                'nama_dudi'      => $mitra->nama_dudi,
                'nama_guru'      => $mitra->nama_guru,
                'total_siswa'    => $actual_siswa_count,
                'rata_siswa'     => round($rata_siswa, 1),
                'kunjungan_guru' => $kunjungan,
                'kpi_guru'       => round($kpi_guru, 1),
                'skor_akumulasi' => round($skor_akumulasi, 1),
                'grade'          => $grade
            ];
        }

        usort($data_performa, function($a, $b) { return $b['skor_akumulasi'] <=> $a['skor_akumulasi']; });
        $rata_global = $count_kemitraan > 0 ? ($akumulasi_global / $count_kemitraan) : 0;

        return Inertia::render('Admin/Pkl/Dashboard', [
            'semua_kelas'       => $semua_kelas,
            'list_kelas_aktif'  => $list_kelas_aktif,
            'active_class_ids'  => $active_class_ids,
            'kelas_aktif'       => $filter_kelas_id, 
            'total_siswa'       => $total_siswa,
            'siswa_ditempatkan' => $siswa_ditempatkan,
            'siswa_belum'       => $siswa_belum,
            'pkl_aktif'         => $pkl_aktif,
            'pkl_selesai'       => $pkl_selesai,
            'total_dudi'        => $total_dudi,
            'persen_penempatan' => $persen_penempatan,
            'performa'          => $data_performa,
            'rata_global'       => round($rata_global, 1)
        ]);
    }

    public function set_kelas_pkl(Request $request)
    {
        $kelas_ids = $request->input('kelas_ids', []); 

        // Kosongkan pengaturan lama
        \Illuminate\Support\Facades\DB::table('tbl_pkl_kelas')->truncate();

        // Simpan pengaturan baru
        if (!empty($kelas_ids)) {
            $data_insert = [];
            foreach ($kelas_ids as $id) {
                $data_insert[] = ['kelas_id' => $id];
            }
            \Illuminate\Support\Facades\DB::table('tbl_pkl_kelas')->insert($data_insert);
        }

        return redirect()->route('admin.pkl.dashboard')->with('message', 'Pengaturan Target Kelas PKL berhasil diperbarui!');
    }

    // ==========================================
    // 2. MAPPING KELOMPOK
    // ==========================================
    public function kelompok()
    {
        $kelompok = PklKelompok::with(['guru', 'dudi'])->get();
        
        foreach($kelompok as $k) {
            $k->peserta = \Illuminate\Support\Facades\DB::table('tbl_pkl')
                ->join('tbl_siswa', 'tbl_pkl.siswa_id', '=', 'tbl_siswa.id')
                ->where('tbl_pkl.dudi_id', $k->dudi_id)
                ->where('tbl_pkl.guru_id', $k->guru_id)
                // Filter by the exact date if multiple groups exist
                // ->where('tbl_pkl.tgl_mulai', $k->tgl_mulai) // wait, PklKelompok doesn't have tgl_mulai!
                ->select('tbl_siswa.nama_lengkap', 'tbl_siswa.nis', 'tbl_pkl.tgl_mulai', 'tbl_pkl.tgl_selesai', 'tbl_pkl.id as pkl_id', 'tbl_pkl.siswa_id')
                ->get();
        }

        $guru = Guru::all();
        $dudi = Dudi::all();

        $assigned_siswa_ids = \Illuminate\Support\Facades\DB::table('tbl_pkl')
            ->whereNotIn('status_pkl', ['Dibatalkan'])
            ->pluck('siswa_id')->toArray();

        $siswa = \App\Models\Siswa::with(['kelas', 'jurusan'])
            ->where('status_siswa', 'Aktif')
            ->orderBy('nama_lengkap', 'asc')
            ->get()->map(function($s) use ($assigned_siswa_ids) {
                $s->is_assigned = in_array($s->id, $assigned_siswa_ids);
                return $s;
            });

        return Inertia::render('Admin/Pkl/Kelompok', [
            'kelompok' => $kelompok,
            'guru' => $guru,
            'dudi' => $dudi,
            'siswa' => $siswa
        ]);
    }

    public function storeKelompok(Request $request)
    {
        $request->validate([
            'dudi_id' => 'required|integer',
            'guru_id' => 'required|integer',
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date',
            'siswa_ids' => 'required|array|min:1',
        ]);

        $dudi = Dudi::findOrFail($request->dudi_id);
        $guru = Guru::findOrFail($request->guru_id);
        
        $nama_kelompok = $dudi->nama_dudi . ' (' . $guru->nama_lengkap . ')';

        PklKelompok::create([
            'nama_kelompok' => $nama_kelompok,
            'guru_id' => $request->guru_id,
            'dudi_id' => $request->dudi_id
        ]);

        foreach($request->siswa_ids as $siswa_id) {
            \Illuminate\Support\Facades\DB::table('tbl_pkl')->insert([
                'siswa_id' => $siswa_id,
                'dudi_id' => $request->dudi_id,
                'guru_id' => $request->guru_id,
                'tgl_mulai' => $request->tgl_mulai,
                'tgl_selesai' => $request->tgl_selesai,
                'status_pkl' => 'Aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Peserta PKL berhasil ditempatkan.');
    }

    public function updateKelompok(Request $request, $id)
    {
        $request->validate([
            'dudi_id' => 'required|integer',
            'guru_id' => 'required|integer',
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date',
            'siswa_ids' => 'required|array|min:1',
        ]);

        $kelompok = PklKelompok::findOrFail($id);
        $old_guru_id = $kelompok->guru_id;
        $old_dudi_id = $kelompok->dudi_id;

        $dudi = Dudi::findOrFail($request->dudi_id);
        $guru = Guru::findOrFail($request->guru_id);
        
        $nama_kelompok = $dudi->nama_dudi . ' (' . $guru->nama_lengkap . ')';

        $kelompok->update([
            'nama_kelompok' => $nama_kelompok,
            'guru_id' => $request->guru_id,
            'dudi_id' => $request->dudi_id
        ]);

        $existing_siswa = \Illuminate\Support\Facades\DB::table('tbl_pkl')
            ->where('guru_id', $old_guru_id)
            ->where('dudi_id', $old_dudi_id)
            ->pluck('siswa_id')->toArray();

        $to_remove = array_diff($existing_siswa, $request->siswa_ids);
        if (count($to_remove) > 0) {
            \Illuminate\Support\Facades\DB::table('tbl_pkl')
                ->where('guru_id', $old_guru_id)
                ->where('dudi_id', $old_dudi_id)
                ->whereIn('siswa_id', $to_remove)
                ->delete();
        }

        $to_insert = array_diff($request->siswa_ids, $existing_siswa);
        foreach ($to_insert as $siswa_id) {
            \Illuminate\Support\Facades\DB::table('tbl_pkl')->insert([
                'siswa_id' => $siswa_id,
                'dudi_id' => $request->dudi_id,
                'guru_id' => $request->guru_id,
                'tgl_mulai' => $request->tgl_mulai,
                'tgl_selesai' => $request->tgl_selesai,
                'status_pkl' => 'Aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $to_keep = array_intersect($request->siswa_ids, $existing_siswa);
        if (count($to_keep) > 0) {
            \Illuminate\Support\Facades\DB::table('tbl_pkl')
                ->where('guru_id', $old_guru_id)
                ->where('dudi_id', $old_dudi_id)
                ->whereIn('siswa_id', $to_keep)
                ->update([
                    'dudi_id' => $request->dudi_id,
                    'guru_id' => $request->guru_id,
                    'tgl_mulai' => $request->tgl_mulai,
                    'tgl_selesai' => $request->tgl_selesai,
                    'updated_at' => now(),
                ]);
        }

        return redirect()->back()->with('success', 'Penempatan PKL berhasil diubah.');
    }

    public function deleteKelompok($id)
    {
        $kelompok = PklKelompok::findOrFail($id);
        
        \Illuminate\Support\Facades\DB::table('tbl_pkl')
            ->where('guru_id', $kelompok->guru_id)
            ->where('dudi_id', $kelompok->dudi_id)
            ->delete();

        $kelompok->delete();

        return redirect()->back()->with('success', 'Penempatan PKL berhasil dihapus.');
    }
}
