<?php

namespace App\Http\Controllers\Admin\CBT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\JadwalUjian;
use App\Models\DraftUjian;
use App\Models\Guru;
use App\Models\JenisUjian;
use App\Models\TahunAjaran;
use App\Models\Ruangan;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Jurusan;
use App\Models\UjianSiswa;
use Illuminate\Support\Facades\DB;

class JadwalUjianController extends Controller
{
    public function index(Request $request)
    {
        $query = JadwalUjian::with(['draftUjian', 'guru', 'jenisUjian', 'tahunAjaran', 'ruangan', 'kelas'])->orderBy('id', 'desc');

        $userRoles = session('roles', []);
        $isAdmin = in_array('admin', $userRoles) || in_array('superadmin', $userRoles) || in_array('kurikulum', $userRoles) || in_array('kepsek', $userRoles);
        
        if (!$isAdmin && in_array('guru', $userRoles)) {
            $query->whereHas('draftUjian.bankSoal', function($q) {
                $q->where('user_id', auth()->id());
            });
        }

        if ($request->filled('search')) {
            $query->where('nama_ujian', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status_ujian')) {
            $query->where('status_ujian', $request->status_ujian);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereDate('waktu_mulai', '>=', $request->start_date)
                  ->whereDate('waktu_selesai', '<=', $request->end_date);
        } elseif ($request->filled('start_date')) {
            $query->whereDate('waktu_mulai', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->whereDate('waktu_selesai', '<=', $request->end_date);
        }

        $jadwalUjian = $query->paginate(10)->withQueryString();
        
        // Append calculated properties
        $jadwalUjian->getCollection()->transform(function ($jadwal) {
            $jadwal->total_siswa = \App\Models\UjianSiswa::where('jadwal_id', $jadwal->id)->count();
            $jadwal->is_generated = \App\Models\UjianSiswa::where('jadwal_id', $jadwal->id)
                                        ->whereNotNull('soal_generated')
                                        ->exists();
            
            $kelas_concat = \Illuminate\Support\Facades\DB::table('ujian_siswa as us')
                                    ->join('tbl_siswa as ms', 'ms.id', '=', 'us.siswa_id')
                                    ->join('tbl_kelas as mk', 'mk.id', '=', 'ms.kelas_id')
                                    ->where('us.jadwal_id', $jadwal->id)
                                    ->selectRaw("GROUP_CONCAT(DISTINCT mk.nama_kelas SEPARATOR ', ') as kelas")
                                    ->value('kelas');
                                    
            \Illuminate\Support\Facades\Log::info('Debug kelas concat jadwal ' . $jadwal->id, [
                'kelas_concat' => $kelas_concat,
                'total_siswa' => $jadwal->total_siswa,
                'raw_peserta' => \Illuminate\Support\Facades\DB::table('ujian_siswa')->where('jadwal_id', $jadwal->id)->pluck('siswa_id'),
                'raw_siswa' => \Illuminate\Support\Facades\DB::table('tbl_siswa')->whereIn('id', \Illuminate\Support\Facades\DB::table('ujian_siswa')->where('jadwal_id', $jadwal->id)->pluck('siswa_id'))->select('id', 'kelas_id')->get(),
                'raw_kelas' => \Illuminate\Support\Facades\DB::table('tbl_kelas')->get()
            ]);
            
            $jadwal->kelas_concat = $kelas_concat;
            
            return $jadwal;
        });
        
        // Pass lookups for Modal Form
        $draftUjians = DraftUjian::with(['mapel', 'bankSoal'])
            ->when(!$isAdmin && in_array('guru', $userRoles), function($q) {
                return $q->whereHas('bankSoal', function($subQ) {
                    $subQ->where('user_id', auth()->id());
                });
            })
            ->orderBy('id', 'desc')
            ->get();
        $gurus = Guru::all();
        $jenisUjians = JenisUjian::all();
        $tahunAjarans = TahunAjaran::all();
        $ruangans = Ruangan::all();
        $kelass = Kelas::all();
        $jurusans = Jurusan::all();

        return Inertia::render('Admin/CBT/JadwalUjian/Index', [
            'jadwalUjian' => $jadwalUjian,
            'draftUjians' => $draftUjians,
            'gurus' => $gurus,
            'jenisUjians' => $jenisUjians,
            'tahunAjarans' => $tahunAjarans,
            'ruangans' => $ruangans,
            'kelass' => $kelass,
            'jurusans' => $jurusans,
            'filters' => $request->only(['status_ujian', 'start_date', 'end_date', 'search'])
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_ujian' => 'required',
            'id_bank_soal' => 'required|exists:draft_ujian,id',
            'waktu_mulai' => 'required|date',
            'waktu_selesai' => 'required|date',
            'id_tahun_ajaran' => 'required|exists:tbl_tahun_ajaran,id',
            'id_jenis_ujian' => 'required|exists:tbl_jenis_ujian,id',
        ]);

        $data = $request->all();

        // Ambil durasi dari draft ujian (timeout)
        $draft = DraftUjian::findOrFail($request->id_bank_soal);
        $data['durasi'] = $draft->timeout ?? 0;
        
        // Jika durasi di draft 0, fallback ke selisih waktu
        if ($data['durasi'] == 0) {
            $start = \Carbon\Carbon::parse($request->waktu_mulai);
            $end = \Carbon\Carbon::parse($request->waktu_selesai);
            $data['durasi'] = round(abs($end->diffInMinutes($start)));
        }

        // Default strict safety values
        $data['setting_strict'] = $request->boolean('setting_strict');
        $data['setting_show_score'] = $request->boolean('setting_show_score');
        $data['setting_multi_login'] = $request->boolean('setting_multi_login');
        $data['setting_token'] = $request->boolean('setting_token');
        $data['acak_soal'] = $request->boolean('acak_soal');
        $data['acak_opsi'] = $request->boolean('acak_opsi');

        if ($request->filled('token')) {
            $data['token'] = strtoupper($request->token);
        }

        DB::transaction(function() use ($data, $request) {
            $jadwal = JadwalUjian::create($data);
            $jadwal->generateToken();
        });

        return redirect()->back()->with('success', 'Jadwal Ujian berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_ujian' => 'required',
            'id_bank_soal' => 'required|exists:draft_ujian,id',
            'waktu_mulai' => 'required|date',
            'waktu_selesai' => 'required|date',
            'id_tahun_ajaran' => 'required|exists:tbl_tahun_ajaran,id',
            'id_jenis_ujian' => 'required|exists:tbl_jenis_ujian,id',
        ]);

        $jadwal = JadwalUjian::findOrFail($id);
        
        $data = $request->all();

        // Ambil durasi dari draft ujian (timeout)
        $draft = DraftUjian::findOrFail($request->id_bank_soal);
        $data['durasi'] = $draft->timeout ?? 0;
        
        // Jika durasi di draft 0, fallback ke selisih waktu
        if ($data['durasi'] == 0) {
            $start = \Carbon\Carbon::parse($request->waktu_mulai);
            $end = \Carbon\Carbon::parse($request->waktu_selesai);
            $data['durasi'] = round(abs($end->diffInMinutes($start)));
        }

        $data['setting_strict'] = $request->boolean('setting_strict');
        $data['setting_show_score'] = $request->boolean('setting_show_score');
        $data['setting_multi_login'] = $request->boolean('setting_multi_login');
        $data['setting_token'] = $request->boolean('setting_token');
        $data['acak_soal'] = $request->boolean('acak_soal');
        $data['acak_opsi'] = $request->boolean('acak_opsi');

        if ($request->filled('token')) {
            $data['token'] = strtoupper($request->token);
        }

        DB::transaction(function() use ($jadwal, $data, $request) {
            $jadwal->update($data);
        });

        return redirect()->back()->with('success', 'Jadwal Ujian berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $jadwal = JadwalUjian::findOrFail($id);
        $jadwal->kelas()->detach();
        $jadwal->delete();

        return redirect()->back()->with('success', 'Jadwal Ujian berhasil dihapus.');
    }

    // --- API Endpoints for Peserta Modal ---

    public function getPeserta($id)
    {
        $allSiswa = DB::table('tbl_siswa as s')
            ->select('s.id', 's.nama_lengkap as nama', 's.nis', 's.nisn', 'k.nama_kelas as kelas', 's.kelas_id', 'k.tingkat', 's.jurusan_id', 's.agama', 's.status_siswa')
            ->leftJoin('tbl_kelas as k', 'k.id', '=', 's.kelas_id')
            ->get();
            
        $ujianSiswaIds = DB::table('ujian_siswa')
            ->where('jadwal_id', $id)
            ->pluck('siswa_id')
            ->toArray();
            
        $result = $allSiswa->map(function($siswa) use ($ujianSiswaIds) {
            $siswa->exists = in_array($siswa->id, $ujianSiswaIds);
            return $siswa;
        });

        return response()->json(['data' => $result]);
    }

    public function getMasterKelas()
    {
        return response()->json(['data' => Kelas::all()]);
    }

    public function syncPeserta(Request $request, $id)
    {
        $request->validate(['peserta' => 'array']);
        
        $peserta = collect($request->peserta)->map(function($siswa_id) use ($id) {
            return [
                'jadwal_id' => $id,
                'siswa_id' => $siswa_id
            ];
        })->toArray();
        
        DB::transaction(function() use ($id, $peserta) {
            UjianSiswa::where('jadwal_id', $id)->delete();
            if (count($peserta) > 0) {
                UjianSiswa::insert($peserta);
            }
        });

        return response()->json(['message' => 'Peserta berhasil diupdate']);
    }

    public function generateSoal(Request $request, $id)
    {
        $ids = $request->input('ids', []);
        $query = UjianSiswa::where('jadwal_id', $id);
        
        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }
        
        $ujian_siswa_ids = (clone $query)->pluck('id')->toArray();
        if (!empty($ujian_siswa_ids)) {
            DB::table('jawaban_pg_ujian_siswa')->whereIn('ujian_id', $ujian_siswa_ids)->delete();
            DB::table('jawaban_pgmulti_ujian_siswa')->whereIn('ujian_id', $ujian_siswa_ids)->delete();
            DB::table('jawaban_pgtf_ujian_siswa')->whereIn('ujian_id', $ujian_siswa_ids)->delete();
            DB::table('jawaban_pgcouple_ujian_siswa')->whereIn('ujian_id', $ujian_siswa_ids)->delete();
            DB::table('jawaban_shortentry_ujian_siswa')->whereIn('ujian_id', $ujian_siswa_ids)->delete();
            DB::table('jawaban_esai_ujian_siswa')->whereIn('ujian_id', $ujian_siswa_ids)->delete();
            DB::table('tbl_jawaban_siswa')->whereIn('id_ujian_siswa', $ujian_siswa_ids)->delete(); // Cleanup old tables
            
            $query->update([
                'soal_generated' => 0, // Should be 0 so it regenerates
                'status' => 0,
                'start_at' => null,
                'end_at' => null,
                'pg_benar' => 0,
                'pgtf_benar' => 0,
                'pgmulti_benar' => 0,
                'pgcouple_benar' => 0,
                'shortentry_benar' => 0,
                'nilai_pg' => 0,
                'nilai_esai' => 0,
                'hasCheating' => 0
            ]);
        }

        return response()->json(['message' => empty($ids) ? 'Berhasil meng-generate ulang soal (Reset) untuk semua peserta!' : 'Berhasil meng-generate ulang soal (Reset) untuk peserta terpilih!']);
    }

    public function generateSoalSingle(Request $request, $id)
    {
        $ujian_id = $request->input('ujian_id');
        if ($ujian_id) {
            DB::table('jawaban_pg_ujian_siswa')->where('ujian_id', $ujian_id)->delete();
            DB::table('jawaban_pgmulti_ujian_siswa')->where('ujian_id', $ujian_id)->delete();
            DB::table('jawaban_pgtf_ujian_siswa')->where('ujian_id', $ujian_id)->delete();
            DB::table('jawaban_pgcouple_ujian_siswa')->where('ujian_id', $ujian_id)->delete();
            DB::table('jawaban_shortentry_ujian_siswa')->where('ujian_id', $ujian_id)->delete();
            DB::table('jawaban_esai_ujian_siswa')->where('ujian_id', $ujian_id)->delete();
            DB::table('tbl_jawaban_siswa')->where('id_ujian_siswa', $ujian_id)->delete(); // Cleanup old tables
            
            DB::table('ujian_siswa')->where('id', $ujian_id)->update([
                'soal_generated' => 0, // Should be 0 so it regenerates
                'status' => 0,
                'start_at' => null,
                'end_at' => null,
                'pg_benar' => 0,
                'pgtf_benar' => 0,
                'pgmulti_benar' => 0,
                'pgcouple_benar' => 0,
                'shortentry_benar' => 0,
                'nilai_pg' => 0,
                'nilai_esai' => 0,
                'hasCheating' => 0
            ]);
            return response()->json(['message' => 'Berhasil mereset soal untuk peserta!']);
        }
        return response()->json(['error' => 'ID peserta tidak valid'], 400);
    }

    public function all(Request $request)
    {
        try {
            $data = JadwalUjian::with(['draftUjian.mapel', 'ruangan', 'jenisUjian'])->get();

            $formatted = [];
            foreach ($data as $row) {
                // Mapel Code
                $kode_mapel = $row->draftUjian->mapel->nama_mapel ?? $row->nama_ujian ?? 'Ujian';
                $kode_jenis = $row->jenisUjian->nama_jenis ?? 'CBT';
                
                // Draft Name
                $nama_draft = $row->draftUjian->nama ?? $row->nama_ujian ?? 'Draft Soal'; 
                
                // Ruangan Sync
                $ruang_id   = $row->id_ruangan ?? 1;
                $kode_ruang = $row->ruangan->kode_ruangan ?? 'ALL';
                $nama_ruang = $row->ruangan->nama_ruangan ?? 'Semua Ruang';
                
                // UNIX Time for Calendar
                $waktu_mulai = $row->waktu_mulai ?? date('Y-m-d H:i:s');
                $waktu_selesai = $row->waktu_selesai ?? date('Y-m-d H:i:s');
                
                $startDateTime = strtotime($waktu_mulai);
                $endDateTime   = strtotime($waktu_selesai);
                
                $formatted[] = [
                    'id' => $row->id,
                    'status' => $row->status_ujian === 'AKTIF' ? 1 : 0,
                    'kode_mapel' => $kode_mapel,
                    'kode_jenis' => $kode_jenis,
                    'nama_draft' => $nama_draft,
                    'ruang_id' => $ruang_id,
                    'kode_ruang' => $kode_ruang,
                    'nama_ruang' => $nama_ruang,
                    'startDateTime' => $startDateTime,
                    'endDateTime' => $endDateTime,
                    'setting_token' => $row->setting_token ?? 0,
                    'setting_show_score' => $row->setting_show_score ?? 0,
                    'setting_multi_login' => $row->setting_multi_login ?? 0
                ];
            }

            return response()->json(['data' => $formatted]);
            
        } catch (\Throwable $e) {
            return response()->json([
                'message' => "ERROR GET ALL: " . $e->getMessage()
            ], 500);
        }
    }

    public function batchTime(Request $request)
    {
        $jadwals = $request->input('jadwals', []);

        if (empty($jadwals)) {
            return response()->json(['message' => 'Tidak ada jadwal yang dipilih'], 400);
        }

        try {
            DB::transaction(function() use ($jadwals) {
                foreach ($jadwals as $j) {
                    $startUnix = $j['startDateTime'];
                    $endUnix = $j['endDateTime'];
                    $durasi = round(abs($endUnix - $startUnix) / 60);

                    DB::table('tbl_jadwal_ujian')->where('id', $j['id'])->update([
                        'waktu_mulai'         => date('Y-m-d H:i:s', $startUnix),
                        'waktu_selesai'       => date('Y-m-d H:i:s', $endUnix),
                        'durasi'              => $durasi,
                        'status_ujian'        => $j['status'] == 1 ? 'AKTIF' : 'NONAKTIF',
                        'setting_token'       => $j['use_token'],
                        'setting_show_score'  => $j['show_point'],
                        'setting_multi_login' => $j['multi_login']
                    ]);
                }
            });

            return response()->json(['message' => 'Berhasil mengubah waktu ujian massal']);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Gagal mengubah waktu massal: ' . $e->getMessage()], 500);
        }
    }

    public function hapusJawabanMasal(Request $request)
    {
        $jadwalIds = $request->input('jadwals', []);

        if (empty($jadwalIds)) {
            return response()->json(['message' => 'Tidak ada jadwal yang dipilih'], 400);
        }

        try {
            DB::transaction(function() use ($jadwalIds) {
                $ujianSiswaIds = DB::table('ujian_siswa')->whereIn('jadwal_id', $jadwalIds)->pluck('id')->toArray();
                
                if (!empty($ujianSiswaIds)) {
                    // Delete answers
                    DB::table('jawaban_pg_ujian_siswa')->whereIn('ujian_id', $ujianSiswaIds)->delete();
                    DB::table('jawaban_esai_ujian_siswa')->whereIn('ujian_id', $ujianSiswaIds)->delete();
                    DB::table('jawaban_shortentry_ujian_siswa')->whereIn('ujian_id', $ujianSiswaIds)->delete();
                    DB::table('jawaban_pgmulti_ujian_siswa')->whereIn('ujian_id', $ujianSiswaIds)->delete();
                    DB::table('jawaban_pgtf_ujian_siswa')->whereIn('ujian_id', $ujianSiswaIds)->delete();
                    DB::table('jawaban_pgcouple_ujian_siswa')->whereIn('ujian_id', $ujianSiswaIds)->delete();
                    DB::table('tbl_jawaban_siswa')->whereIn('id_ujian_siswa', $ujianSiswaIds)->delete(); // Cleanup old tables
                    
                    // Reset progress
                    DB::table('ujian_siswa')->whereIn('id', $ujianSiswaIds)->update([
                        'status' => '0',
                        'nilai_pg' => 0,
                        'nilai_esai' => 0,
                        'jml_benar' => 0,
                        'jml_salah' => 0,
                        'start_at' => null,
                        'end_at' => null,
                        'soal_generated' => 0
                    ]);
                }
            });

            return response()->json(['message' => 'Berhasil menghapus jawaban ujian massal']);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Gagal menghapus jawaban massal: ' . $e->getMessage()], 500);
        }
    }
}
