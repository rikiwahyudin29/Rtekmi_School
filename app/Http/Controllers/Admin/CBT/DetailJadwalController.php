<?php

namespace App\Http\Controllers\Admin\CBT;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Response;
use App\Models\JadwalUjian;
use App\Models\UjianSiswa;
use App\Models\Sekolah;

class DetailJadwalController extends Controller
{
    private function getJadwal($id)
    {
        $id = preg_replace('/[^0-9]/', '', is_numeric($id) ? $id : base64_decode($id));
        
        if (empty($id) || !is_numeric($id)) return null; 
        
        $data = DB::table('tbl_jadwal_ujian')
            ->leftJoin('tbl_ruangan', 'tbl_jadwal_ujian.id_ruangan', '=', 'tbl_ruangan.id')
            ->leftJoin('tbl_jenis_ujian', 'tbl_jadwal_ujian.id_jenis_ujian', '=', 'tbl_jenis_ujian.id')
            ->leftJoin('draft_ujian', 'tbl_jadwal_ujian.id_bank_soal', '=', 'draft_ujian.id')
            ->select('tbl_jadwal_ujian.*', 'tbl_ruangan.nama_ruangan as kode_ruang', 'tbl_jenis_ujian.kode_jenis', 'draft_ujian.nama as nama_draft')
            ->where('tbl_jadwal_ujian.id', $id)
            ->first();
            
        if ($data) {
            $data->nama          = $data->nama_ujian ?? 'Jadwal Ujian';
            $data->kode_ruang    = $data->kode_ruang ?? 'Semua Ruang';
            $data->nama_draft    = $data->nama_draft ?? 'Draft Soal';
            $data->kode_jenis    = $data->kode_jenis ?? 1;
            $data->use_token     = $data->setting_token ?? 0;
            $data->status        = $data->status ?? 1;
            $data->token         = $data->token ?? '-';
            $data->startDateTime = strtotime($data->waktu_mulai ?? date('Y-m-d H:i:s'));
            $data->endDateTime   = strtotime($data->waktu_selesai ?? date('Y-m-d H:i:s'));
            return $data;
        }
        return null;
    }

    public function index($id)
    {
        if (strpos($id, '/jawaban/') !== false) {
            $parts = explode('/jawaban/', $id);
            return $this->jawaban($parts[0], $parts[1]); 
        }

        $id = preg_replace('/[^0-9]/', '', is_numeric($id) ? $id : base64_decode($id));
        $jadwalData = $this->getJadwal($id);
        
        if (!$jadwalData) {
            abort(404, "Data ID: $id tidak ditemukan di tbl_jadwal_ujian.");
        }

        $kelasList = DB::table('tbl_kelas')->select('id', 'nama_kelas')->get();

        return Inertia::render('Admin/CBT/Jadwal/Detail/Index', [
            'page_title' => $jadwalData->nama . " - " . $jadwalData->kode_ruang,
            'jadwalData' => $jadwalData,
            'id' => $id,
            'kelasList' => $kelasList
        ]);
    }

    public function status($id)
    {
        $jadwalData = $this->getJadwal($id);
        if (!$jadwalData) return response()->json(['message' => 'Jadwal Tidak Ditemukan'], 404);
        
        $data = [
            'jenis_ujian'   => $jadwalData->kode_jenis,
            'status'        => $jadwalData->status,
            'token'         => $jadwalData->token,
            'use_token'     => $jadwalData->use_token,
            'nama'          => $jadwalData->nama,
            'nama_draft'    => $jadwalData->nama_draft,
            'startDateTime' => $jadwalData->startDateTime,
            'endDateTime'   => $jadwalData->endDateTime,
            'belum_ujian'   => DB::table('ujian_siswa')->where(['jadwal_id' => $jadwalData->id, 'status' => '0'])->count(),
            'sedang_ujian'  => DB::table('ujian_siswa')->where(['jadwal_id' => $jadwalData->id, 'status' => '1'])->count(),
            'sudah_ujian'   => DB::table('ujian_siswa')->where(['jadwal_id' => $jadwalData->id, 'status' => '2'])->count(),
        ];
        return response()->json($data);
    }

    public function progress(Request $request, $id)
    {
        $jadwalData = $this->getJadwal($id);
        if (!$jadwalData) return response()->json(['message' => 'Jadwal Tidak Ditemukan'], 404);

        $query = DB::table('ujian_siswa as us')
            ->select('us.*', 's.nama_lengkap as nama', 's.nisn as nomor_induk', 'k.nama_kelas', 's.kelas_id as id_kelas', 'ju.bobot_pg as bobot_tf', 'ju.bobot_esai', 'ju.durasi as lama_ujian', 'du.visible_pg', 'du.visible_pgmulti', 'du.visible_pgtf', 'du.visible_pgcouple', 'du.visible_shortentry', 'du.visible_esai')
            ->leftJoin('tbl_siswa as s', 's.id', '=', 'us.siswa_id')
            ->leftJoin('tbl_kelas as k', 'k.id', '=', 's.kelas_id')
            ->leftJoin('tbl_jadwal_ujian as ju', 'ju.id', '=', 'us.jadwal_id')
            ->leftJoin('draft_ujian as du', 'du.id', '=', 'ju.id_bank_soal')
            ->where('us.jadwal_id', $jadwalData->id);
        
        $kelas = $request->get('kelas');
        $status = $request->get('status');
        
        if ($kelas && $kelas != 'all') $query->where('s.kelas_id', $kelas);
        if ($status !== null && !in_array($status, ['all', '3'])) $query->where('us.status', $status);
        
        $result = $query->get()->map(function ($row) {
            $row = (array) $row;
            
            $start = !empty($row['start_at']) ? (is_numeric($row['start_at']) ? (int)$row['start_at'] : strtotime($row['start_at'])) : 0;
            $end   = !empty($row['end_at']) ? (is_numeric($row['end_at']) ? (int)$row['end_at'] : strtotime($row['end_at'])) : 0;
            
            $row['start_at'] = $start > 0 ? $start : null;
            $row['end_at']   = $end > 0 ? $end : null;

            $row['visible_pg']         = $row['visible_pg'] ?? 0;
            $row['visible_pgmulti']    = $row['visible_pgmulti'] ?? 0;
            $row['visible_pgtf']       = $row['visible_pgtf'] ?? 0;
            $row['visible_pgcouple']   = $row['visible_pgcouple'] ?? 0;
            $row['visible_shortentry'] = $row['visible_shortentry'] ?? 0;
            $row['visible_esai']       = $row['visible_esai'] ?? 0;
            $row['bobot_tf']           = $row['bobot_tf'] ?? 100;
            $row['bobot_esai']         = $row['bobot_esai'] ?? 0;

            $row['soal_pg_benar']         = $row['pg_benar'] ?? 0;
            $row['soal_pgmulti_benar']    = $row['pgmulti_benar'] ?? 0;
            $row['soal_pgtf_benar']       = $row['pgtf_benar'] ?? 0;
            $row['soal_pgcouple_benar']   = $row['pgcouple_benar'] ?? 0;
            $row['soal_shortentry_benar'] = $row['shortentry_benar'] ?? 0;
            
            $row['soal_pg_selesai']         = $row['soal_pg_selesai'] ?? 0;
            $row['soal_pgmulti_selesai']    = $row['soal_pgmulti_selesai'] ?? 0;
            $row['soal_pgtf_selesai']       = $row['soal_pgtf_selesai'] ?? 0;
            $row['soal_pgcouple_selesai']   = $row['soal_pgcouple_selesai'] ?? 0;
            $row['soal_shortentry_selesai'] = $row['soal_shortentry_selesai'] ?? 0;
            
            $row['nilai'] = round(($row['nilai_pg'] ?? 0) + ($row['nilai_esai'] ?? 0), 2);

            return $row;
        });
        
        return response()->json([
            'data' => $result, 
            'server_time' => time(), 
            // In Laravel, CSRF is handled automatically via axios headers, but we return it if the front end relies on it
            'csrf' => ['name' => '_token', 'value' => csrf_token()] 
        ]);
    }

    public function token($id)
    {
        $jadwalData = $this->getJadwal($id);
        if (!$jadwalData) return response()->json(['message' => 'Not Found'], 404);
        
        $token = strtoupper(substr(md5(time() . $jadwalData->id), 0, 6));
        DB::table('tbl_jadwal_ujian')->where('id', $jadwalData->id)->update(['token' => $token]);
        
        return response()->json(['token' => $token]);
    }

    public function extra_time(Request $request, $id)
    {
        $jadwalData = $this->getJadwal($id);
        if (!$jadwalData) return response()->json(['message' => 'Not Found'], 404);
        
        $d = $request->get('data');
        if (!$d || !is_array($d)) return response()->json(['message' => 'Forbidden'], 403);
        
        $ids = array_column($d, 'id');
        $uj = DB::table('ujian_siswa')->whereIn('id', $ids)->get()->toArray();
        $updated = false;
        
        foreach ($uj as $u) {
            foreach ($d as $dd) {
                if (isset($dd['id']) && $dd['id'] == $u->id) {
                    $endTimeString = $jadwalData->endDateTime;
                    $endUnixLimit  = is_string($endTimeString) ? strtotime($endTimeString) : $endTimeString;
                    
                    $extraTime = $u->end_at + ($dd['extra_time'] * 60);
                    if ($extraTime > $endUnixLimit) $extraTime = $endUnixLimit;
                    
                    DB::table('ujian_siswa')
                        ->where('id', $u->id)
                        ->update(['end_at' => $extraTime, 'status' => '1']);
                    $updated = true;
                }
            }
        }
        
        if ($updated) {
            return response()->json(['message' => "Berhasil Menambahkan Waktu"]);
        }
        return response()->json(['message' => "Gagal Menambahkan Waktu"], 400);
    }

    public function unlock($jid, $ujian_id) 
    { 
        $ujian_id = preg_replace('/[^0-9]/', '', is_numeric($ujian_id) ? $ujian_id : base64_decode($ujian_id));
        
        DB::table('ujian_siswa')
           ->where('id', $ujian_id)
           ->update(['status' => '1', 'ip_address' => null, 'hasCheating' => 0]);
           
        return response()->json(['unlocked' => true, 'message' => 'Sesi Ujian Berhasil Dibuka dan Pelanggaran Direset!']); 
    }

    public function unlock_batch(Request $request, $jid) 
    {
        $ids = $request->get('id');
        if (!$ids || !is_array($ids)) return response()->json(['message' => 'No IDs provided'], 400);

        DB::table('ujian_siswa')
           ->whereIn('id', $ids)
           ->update(['status' => '1', 'ip_address' => null, 'hasCheating' => 0]);
           
        return response()->json(['unlocked' => true, 'message' => 'Sesi Peserta Terpilih Berhasil Dibuka!']);
    }

    public function finish_batch(Request $request, $jid)
    {
        $ids = $request->get('id');
        if (empty($ids) || !is_array($ids)) {
            return response()->json(['finished' => false, 'message' => "Tidak ada peserta yang dipilih"], 400);
        }

        DB::table('ujian_siswa')
            ->whereIn('id', $ids)
            ->whereIn('status', ['0', '1'])
            ->update([
                'status' => '2',
                'end_at' => time()
            ]);
            
        return response()->json(['finished' => true, 'message' => 'Berhasil Menyelesaikan Ujian Terpilih']);
    }

    public function jawaban($jadwal_id = null, $ujian_id = null)
    {
        if ($ujian_id === null) $ujian_id = $jadwal_id; 

        $ujian_id_decoded = preg_replace('/[^0-9]/', '', is_numeric($ujian_id) ? $ujian_id : base64_decode($ujian_id));
        
        $ujian = DB::table('ujian_siswa')->where('id', $ujian_id_decoded)->first();
        if (!$ujian) return response()->json(['error' => 'Data ujian siswa tidak ditemukan'], 404);
        $ujian = (array) $ujian;
        
        $jadwal = DB::table('tbl_jadwal_ujian')->where('id', $ujian['jadwal_id'])->first();
        $soals = [];
        
        if ($jadwal) {
            $draft = DB::table('draft_ujian')->where('id', $jadwal->id_bank_soal)->first();
            if ($draft && !empty($draft->bank_soal_id)) {
                $soals = DB::table('soal_data')->where('bank_id', $draft->bank_soal_id)->get()->map(function($x){return (array)$x;})->toArray();
            }
        }

        if (empty($soals)) {
            return response()->json(['error' => 'Tidak Ada Data Jawaban (Soal Kosong)']);
        }

        $this->migrateJawabanCi4ToLaravel($ujian_id_decoded, $soals);

        $soal_ids = array_column($soals, 'id');
        $semua_opsi = DB::table('soal_opsi')->whereIn('soal_id', $soal_ids)->get()->map(function($x){return (array)$x;})->toArray();
        
        $semua_couple = [];
        try {
            $semua_couple = DB::table('soal_data_couple')->whereIn('soal_id', $soal_ids)->get()->map(function($x){return (array)$x;})->toArray();
        } catch (\Exception $e) {
            // table doesn't exist, ignore
        }

        $semua_jawaban = DB::table('tbl_jawaban_siswa')->where('id_ujian_siswa', $ujian_id_decoded)->get();
        $pg = []; $pgmulti = []; $pgcouple = []; $pgtf = []; $short = []; $esai = [];
        foreach($semua_jawaban as $j) {
            $sid = $j->id_soal;
            $inp = $j->jawaban_siswa;
            if ($inp === null || $inp === '') continue;
            
            $jenis = 0;
            foreach($soals as $s) {
                if ($s['id'] == $sid) {
                    $jenis = (int)$s['jenis_soal'];
                    break;
                }
            }
            
            if ($jenis == 1) {
                $pg[] = ['soal_id' => $sid, 'opsi_id' => $inp];
            } elseif ($jenis == 3) {
                $arr = json_decode($inp, true);
                if (is_array($arr)) foreach($arr as $oid) $pgmulti[] = ['soal_id' => $sid, 'opsi_id' => $oid];
            } elseif ($jenis == 4) {
                $arr = json_decode($inp, true);
                if (is_array($arr)) foreach($arr as $kiri => $kanan) $pgcouple[] = ['soal_id' => $sid, 'couple_id' => $kiri, 'opsi_id' => $kanan];
            } elseif ($jenis == 6) {
                $arr = json_decode($inp, true);
                if (is_array($arr)) foreach($arr as $oid => $tf) $pgtf[] = ['soal_id' => $sid, 'opsi_id' => $oid, 'tf' => $tf];
            } elseif ($jenis == 5) {
                $short[] = ['soal_id' => $sid, 'respond' => $inp];
            } elseif ($jenis == 2) {
                $esai[] = ['soal_id' => $sid, 'esai' => $inp, 'nilai' => $j->nilai];
            }
        }
        
        $f_pg = ['soal' => [], 'opsi' => []]; 
        $f_pgmulti = ['soal' => [], 'opsi' => []]; 
        $f_pgtf = ['soal' => [], 'opsi' => []]; 
        $f_pgcouple = ['soal' => [], 'opsi' => [], 'couple' => []]; 
        $f_short = ['soal' => []]; 
        $f_esai = ['soal' => []];

        $baseUrl = url('/');

        foreach ($soals as $s) {
            $jenis = (int)$s['jenis_soal'];
            $sid = $s['id'];
            
            $s['question'] = str_replace('../../../', $baseUrl . '/', $s['question']);
            
            $opsi_soal_ini = array_filter($semua_opsi, function($o) use ($sid, $baseUrl) { 
                if ($o['soal_id'] == $sid) {
                    $o['body'] = str_replace('../../../', $baseUrl . '/', $o['body']);
                    return true;
                }
                return false;
            });
            $couple_soal_ini = array_filter($semua_couple, function($c) use ($sid) { return $c['soal_id'] == $sid; });

            $soal_data = [
                'id' => $ujian_id_decoded,
                'soal_id' => $sid,
                'question' => $s['question'],
            ];

            if ($jenis == 1) { 
                $ans = array_filter($pg, function($p) use ($sid) { return $p['soal_id'] == $sid; });
                $ans = reset($ans);
                $soal_data['opsi_id'] = $ans ? $ans['opsi_id'] : '';
                $f_pg['soal'][] = $soal_data;
                foreach($opsi_soal_ini as $o) $f_pg['opsi'][] = $o;

            } elseif ($jenis == 3) { 
                $ans = array_filter($pgmulti, function($p) use ($sid) { return $p['soal_id'] == $sid; });
                $arr = array_column($ans, 'opsi_id');
                $soal_data['opsi'] = implode(',', $arr);
                $f_pgmulti['soal'][] = $soal_data;
                foreach($opsi_soal_ini as $o) $f_pgmulti['opsi'][] = $o;

            } elseif ($jenis == 4) { 
                $ans = array_filter($pgcouple, function($p) use ($sid) { return $p['soal_id'] == $sid; });
                $arr = [];
                foreach ($ans as $a) $arr[] = $a['couple_id'].'-'.$a['opsi_id'];
                $soal_data['opsi'] = implode(',', $arr);
                $f_pgcouple['soal'][] = $soal_data;
                foreach($opsi_soal_ini as $o) $f_pgcouple['opsi'][] = $o;
                foreach($couple_soal_ini as $c) $f_pgcouple['couple'][] = $c;

            } elseif ($jenis == 6) { 
                $ans = array_filter($pgtf, function($p) use ($sid) { return $p['soal_id'] == $sid; });
                $arr = [];
                foreach ($ans as $a) $arr[] = $a['opsi_id'].'-'.$a['tf'];
                $soal_data['opsi'] = implode(',', $arr);
                $f_pgtf['soal'][] = $soal_data;
                foreach($opsi_soal_ini as $o) $f_pgtf['opsi'][] = $o;

            } elseif ($jenis == 5) { 
                $ans = array_filter($short, function($p) use ($sid) { return $p['soal_id'] == $sid; });
                $ans = reset($ans);
                $soal_data['respond'] = $ans ? $ans['respond'] : '';
                $soal_data['correct_answer'] = $s['shortentry'] ?? '';
                $f_short['soal'][] = $soal_data;

            } elseif ($jenis == 2) { 
                $ans = array_filter($esai, function($p) use ($sid) { return $p['soal_id'] == $sid; });
                $ans = reset($ans);
                $soal_data['esai'] = $ans ? $ans['esai'] : '';
                $soal_data['nilai'] = $ans ? $ans['nilai'] : 0;
                $f_esai['soal'][] = $soal_data;
            }
        }

        // Return array keys reset just to match CI4 json behavior
        return response()->json([
            'ujian'   => $ujian,
            'soal'    => $soals,
            'jawaban' => [
                'pg'         => ['soal' => array_values($f_pg['soal']), 'opsi' => array_values($f_pg['opsi'])],
                'pgmulti'    => ['soal' => array_values($f_pgmulti['soal']), 'opsi' => array_values($f_pgmulti['opsi'])],
                'pgtf'       => ['soal' => array_values($f_pgtf['soal']), 'opsi' => array_values($f_pgtf['opsi'])],
                'pgcouple'   => ['soal' => array_values($f_pgcouple['soal']), 'opsi' => array_values($f_pgcouple['opsi']), 'couple' => array_values($f_pgcouple['couple'])],
                'shortentry' => ['soal' => array_values($f_short['soal'])],
                'esai'       => ['soal' => array_values($f_esai['soal'])]
            ]
        ]);
    }

    public function update_jawaban(Request $request, $jadwal_id, $ujian_id)
    {
        $ujian_id_decoded = preg_replace('/[^0-9]/', '', is_numeric($ujian_id) ? $ujian_id : base64_decode($ujian_id));
        
        $pg = $request->input('pg', []);
        $shortentry = $request->input('shortentry', []);
        $esai = $request->input('esai', []);

        DB::beginTransaction();
        try {
            foreach ($pg as $p) {
                if (empty($p['soal_id'])) continue;
                DB::table('tbl_jawaban_siswa')->where(['id_ujian_siswa' => $ujian_id_decoded, 'id_soal' => $p['soal_id']])->update(['jawaban_siswa' => $p['opsi_id'] ?? null]);
            }

            foreach ($esai as $e) {
                if (empty($e['soal_id'])) continue;
                DB::table('tbl_jawaban_siswa')->where(['id_ujian_siswa' => $ujian_id_decoded, 'id_soal' => $e['soal_id']])->update(['jawaban_siswa' => $e['esai'] ?? null, 'nilai' => floatval($e['nilai'] ?? 0)]);
            }
            
            foreach ($shortentry as $s) {
                if (empty($s['soal_id'])) continue;
                DB::table('tbl_jawaban_siswa')->where(['id_ujian_siswa' => $ujian_id_decoded, 'id_soal' => $s['soal_id']])->update(['jawaban_siswa' => $s['respond'] ?? '']);
            }

            $pgmulti = $request->input('pgmulti', []);
            foreach ($pgmulti as $s) {
                if (empty($s['soal_id'])) continue;
                $arr = [];
                if (!empty($s['opsi'])) {
                    $opsi_arr = explode(',', $s['opsi']);
                    foreach($opsi_arr as $o) if($o) $arr[] = $o;
                }
                DB::table('tbl_jawaban_siswa')->where(['id_ujian_siswa' => $ujian_id_decoded, 'id_soal' => $s['soal_id']])->update(['jawaban_siswa' => empty($arr) ? null : json_encode($arr)]);
            }

            $pgtf = $request->input('pgtf', []);
            foreach ($pgtf as $s) {
                if (empty($s['soal_id'])) continue;
                $arr = [];
                if (!empty($s['opsi'])) {
                    $opsi_arr = explode(',', $s['opsi']);
                    foreach($opsi_arr as $pair) {
                        $parts = explode('-', $pair);
                        if (count($parts) == 2 && $parts[0] != '') $arr[$parts[0]] = $parts[1];
                    }
                }
                DB::table('tbl_jawaban_siswa')->where(['id_ujian_siswa' => $ujian_id_decoded, 'id_soal' => $s['soal_id']])->update(['jawaban_siswa' => empty($arr) ? null : json_encode($arr)]);
            }

            $pgcouple = $request->input('pgcouple', []);
            foreach ($pgcouple as $s) {
                if (empty($s['soal_id'])) continue;
                $arr = [];
                if (!empty($s['opsi'])) {
                    $opsi_arr = explode(',', $s['opsi']);
                    foreach($opsi_arr as $pair) {
                        $parts = explode('-', $pair);
                        if (count($parts) == 2 && $parts[0] != '' && $parts[1] != '') $arr[$parts[0]] = $parts[1];
                    }
                }
                DB::table('tbl_jawaban_siswa')->where(['id_ujian_siswa' => $ujian_id_decoded, 'id_soal' => $s['soal_id']])->update(['jawaban_siswa' => empty($arr) ? null : json_encode($arr)]);
            }

            // REKALKULASI NILAI UJIAN
            $ujian = DB::table('ujian_siswa')->where('id', $ujian_id_decoded)->first();
            if ($ujian) {
                $jadwal = DB::table('tbl_jadwal_ujian')->where('id', $ujian->jadwal_id)->first();
                $draft = DB::table('draft_ujian')->where('id', $jadwal->id_bank_soal)->first();
                
                $list = DB::table('tbl_jawaban_siswa')
                    ->select('tbl_jawaban_siswa.*', 'tbl_jawaban_siswa.id_soal as soal_id', 'tbl_jawaban_siswa.id_ujian_siswa as ujian_siswa_id', 'soal_data.jenis_soal')
                    ->join('soal_data', 'soal_data.id', '=', 'tbl_jawaban_siswa.id_soal')
                    ->where('id_ujian_siswa', $ujian_id_decoded)
                    ->get();
                    
                $skorTot = 0; $bobotTot = 0; 
                $ben_pg = 0; $ben_pgmulti = 0; $ben_pgtf = 0; $ben_pgcouple = 0; $ben_short = 0;

                foreach($list as $j) {
                    $sc = 0; $cor = 0;
                    $inp = $j->jawaban_siswa;
                    $bobot = 0;

                    if ($j->jenis_soal == 1) { 
                        $bobot = $jadwal->bobot_pg ?? 1;
                        if ($inp !== null && $inp !== '') {
                            if(DB::table('soal_opsi')->where(['id'=>$inp, 'is_key'=>1])->exists()) { $sc=1; $cor=1; $ben_pg++; }
                        }
                    } 
                    elseif ($j->jenis_soal == 6) { 
                        $bobot = $jadwal->bobot_pg ?? 1;
                        if ($inp) {
                            $obj = json_decode($inp, true);
                            $tot = DB::table('soal_opsi')->where('soal_id', $j->soal_id)->count();
                            $hit = 0;
                            if($obj && is_array($obj)){
                                foreach($obj as $k => $v) if(DB::table('soal_opsi')->where(['id'=>$k, 'is_key'=>$v])->exists()) $hit++;
                                if($tot>0){ $sc = $hit/$tot; if($sc==1) { $cor=1; $ben_pgtf++; } }
                            }
                        }
                    } 
                    elseif ($j->jenis_soal == 3) { 
                        $bobot = $jadwal->bobot_pg ?? 1;
                        if ($inp) {
                            $arr = json_decode($inp, true);
                            $kunciObj = DB::table('soal_opsi')->select('id')->where(['soal_id'=>$j->soal_id,'is_key'=>1])->get();
                            $kunci = [];
                            foreach($kunciObj as $ko) $kunci[] = (string)$ko->id; 
                            
                            if($arr && is_array($arr)){ 
                                $arrStr = array_map('strval', $arr);
                                sort($arrStr); 
                                sort($kunci); 
                                if($arrStr == $kunci){ $sc=1; $cor=1; $ben_pgmulti++; } 
                            }
                        }
                    } 
                    elseif ($j->jenis_soal == 4) { 
                        $bobot = $jadwal->bobot_pg ?? 1;
                        if ($inp) {
                            $obj = json_decode($inp, true);
                            $tot = DB::table('soal_data_couple')->where('soal_id', $j->soal_id)->count(); 
                            $hit = 0;
                            if($obj && is_array($obj)){ 
                                foreach($obj as $k => $v) if(DB::table('soal_opsi')->where(['id'=>$v, 'soal_couple_id'=>$k])->exists()) $hit++; 
                                if($tot>0){ $sc = $hit/$tot; if($sc==1) { $cor=1; $ben_pgcouple++; } } 
                            }
                        }
                    } 
                    elseif ($j->jenis_soal == 5) { 
                        $bobot = $jadwal->bobot_esai ?? 1;
                        if ($inp !== null && $inp !== '') {
                            $kunci = DB::table('soal_opsi')->where(['soal_id'=>$j->soal_id,'is_key'=>1])->first();
                            if($kunci && trim(strtolower($inp)) == trim(strtolower($kunci->body))) { $sc=1; $cor=1; $ben_short++; }
                        }
                    }

                    if ($j->jenis_soal != 2) {
                        $nilai = $sc * $bobot; 
                        $skorTot += $nilai; 
                        $bobotTot += $bobot;
                        DB::table('tbl_jawaban_siswa')->where('id', $j->id)->update(['is_benar'=>$cor, 'nilai'=>$nilai]);
                    }
                }
                
                $final_pg = ($bobotTot > 0) ? round(($skorTot / $bobotTot) * 100, 2) : 0;
                
                $total_nilai_esai = DB::table('tbl_jawaban_siswa')->join('soal_data', 'soal_data.id', '=', 'tbl_jawaban_siswa.id_soal')
                    ->where('id_ujian_siswa', $ujian_id_decoded)->where('soal_data.jenis_soal', 2)->sum('nilai');
                $jml_esai = $draft->visible_esai ?? 0;
                $bobot_esai = $jadwal->bobot_esai > 0 ? $jadwal->bobot_esai : ($draft->bobot_esai ?? 0);
                $final_esai = $jml_esai > 0 ? ($total_nilai_esai / ($jml_esai * 100)) * $bobot_esai : 0;

                DB::table('ujian_siswa')->where('id', $ujian_id_decoded)->update([
                    'pg_benar' => $ben_pg,
                    'shortentry_benar' => $ben_short,
                    'pgmulti_benar' => $ben_pgmulti,
                    'pgtf_benar' => $ben_pgtf,
                    'pgcouple_benar' => $ben_pgcouple,
                    'nilai_pg' => round($final_pg, 2),
                    'nilai_esai' => round($final_esai, 2)
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Jawaban berhasil disimpan.']);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Update Jawaban Error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function printHadir($id)
    {
        $jadwal_id = preg_replace('/[^0-9]/', '', is_numeric($id) ? $id : base64_decode($id));
        $jadwal = JadwalUjian::with(['draftUjian.mapel', 'ruangan', 'jenisUjian'])->findOrFail($jadwal_id);
        $peserta = UjianSiswa::with(['siswa.kelas'])->where('jadwal_id', $jadwal_id)->get();
        $sekolah = Sekolah::first();

        return view('admin.cbt.print.print_hadir', compact('jadwal', 'peserta', 'sekolah'));
    }

    public function printBerita($id)
    {
        $jadwal_id = preg_replace('/[^0-9]/', '', is_numeric($id) ? $id : base64_decode($id));
        $jadwal = JadwalUjian::with(['draftUjian.mapel', 'ruangan', 'jenisUjian'])->findOrFail($jadwal_id);
        $peserta = UjianSiswa::with(['siswa.kelas'])->where('jadwal_id', $jadwal_id)->get();
        $sekolah = Sekolah::first();

        return view('admin.cbt.print.print_berita', compact('jadwal', 'peserta', 'sekolah'));
    }

    public function printNilai($id)
    {
        $jadwal_id = preg_replace('/[^0-9]/', '', is_numeric($id) ? $id : base64_decode($id));
        $jadwal = JadwalUjian::with(['draftUjian.mapel', 'ruangan', 'jenisUjian'])->findOrFail($jadwal_id);
        $peserta = UjianSiswa::with(['siswa.kelas'])->where('jadwal_id', $jadwal_id)->get();
        $sekolah = Sekolah::first();

        return view('admin.cbt.print.print_nilai', compact('jadwal', 'peserta', 'sekolah'));
    }

    public function exportExcel($id)
    {
        $jadwal_id = preg_replace('/[^0-9]/', '', is_numeric($id) ? $id : base64_decode($id));
        $jadwal = JadwalUjian::with(['draftUjian.mapel', 'ruangan'])->findOrFail($jadwal_id);
        $peserta = UjianSiswa::with(['siswa.kelas'])->where('jadwal_id', $jadwal_id)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header Info
        $sheet->setCellValue('A1', 'DAFTAR NILAI UJIAN');
        $sheet->setCellValue('A2', 'Mata Pelajaran: ' . ($jadwal->draftUjian->mapel->nama_mapel ?? $jadwal->nama_ujian ?? '-'));
        $sheet->setCellValue('A3', 'Kelas/Ruang: ' . ($jadwal->ruangan->nama_ruangan ?? 'Semua Ruang'));
        
        // Table Header
        $sheet->setCellValue('A5', 'No');
        $sheet->setCellValue('B5', 'NIS');
        $sheet->setCellValue('C5', 'Nama Peserta');
        $sheet->setCellValue('D5', 'Kelas');
        $sheet->setCellValue('E5', 'Benar PG');
        $sheet->setCellValue('F5', 'Benar PG K.');
        $sheet->setCellValue('G5', 'Benar B/S');
        $sheet->setCellValue('H5', 'Benar Menj.');
        $sheet->setCellValue('I5', 'Benar Isian');
        $sheet->setCellValue('J5', 'Skor Obyektif');
        $sheet->setCellValue('K5', 'Nilai Esai');
        $sheet->setCellValue('L5', 'Total Nilai');
        
        $row = 6;
        foreach($peserta as $index => $p) {
            $sheet->setCellValue('A'.$row, $index + 1);
            $sheet->setCellValueExplicit('B'.$row, $p->siswa->nis ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C'.$row, $p->siswa->nama_lengkap ?? '-');
            $sheet->setCellValue('D'.$row, $p->siswa->kelas->nama_kelas ?? '-');
            $sheet->setCellValue('E'.$row, $p->pg_benar ?? 0);
            $sheet->setCellValue('F'.$row, $p->pgmulti_benar ?? 0);
            $sheet->setCellValue('G'.$row, $p->pgtf_benar ?? 0);
            $sheet->setCellValue('H'.$row, $p->pgcouple_benar ?? 0);
            $sheet->setCellValue('I'.$row, $p->shortentry_benar ?? 0);
            $sheet->setCellValue('J'.$row, $p->nilai_pg ?? 0);
            $sheet->setCellValue('K'.$row, $p->nilai_esai ?? 0);
            $sheet->setCellValue('L'.$row, ($p->nilai_pg ?? 0) + ($p->nilai_esai ?? 0));
            $row++;
        }
        
        // Styling headers
        $sheet->getStyle('A5:L5')->getFont()->setBold(true);
        foreach(range('A','L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Nilai_Ujian_'.$jadwal_id.'.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($temp_file);

        return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);
    }

    public function printJawaban($id)
    {
        $jadwal_id = preg_replace('/[^0-9]/', '', is_numeric($id) ? $id : base64_decode($id));
        $jadwal = JadwalUjian::with(['draftUjian.mapel', 'ruangan'])->findOrFail($jadwal_id);
        
        $peserta = UjianSiswa::with(['siswa.kelas'])->where('jadwal_id', $jadwal_id)->get();
        
        $draft = DB::table('draft_ujian')->where('id', $jadwal->id_bank_soal)->first();
        $soals = collect();
        if ($draft && !empty($draft->bank_soal_id)) {
            $soals = DB::table('soal_data')->where('bank_id', $draft->bank_soal_id)->get();
            if (!empty($draft->order_soal)) {
                $orderArr = explode(',', $draft->order_soal);
                $soals = $soals->sortBy(function($model) use ($orderArr) {
                    $pos = array_search($model->id, $orderArr);
                    return $pos === false ? 9999 : $pos;
                })->values();
            }
        }
        
        $soal_ids = $soals->pluck('id')->toArray();
        $semua_opsi = DB::table('soal_opsi')->whereIn('soal_id', $soal_ids)->get();
        
        foreach($peserta as $p) {
            $this->migrateJawabanCi4ToLaravel($p->id, $soals);
        }

        $semua_jawaban = DB::table('tbl_jawaban_siswa')
            ->whereIn('id_ujian_siswa', $peserta->pluck('id')->toArray())
            ->get()
            ->groupBy('id_ujian_siswa');
            
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header Info
        $sheet->setCellValue('A1', 'REKAP JAWABAN PESERTA');
        $sheet->setCellValue('A2', 'Mata Pelajaran: ' . ($jadwal->draftUjian->mapel->nama_mapel ?? $jadwal->nama_ujian ?? '-'));
        $sheet->setCellValue('A3', 'Kelas/Ruang: ' . ($jadwal->ruangan->nama_ruangan ?? 'Semua Ruang'));
        
        // Table Header
        $sheet->setCellValue('A5', 'No');
        $sheet->setCellValue('B5', 'NIS');
        $sheet->setCellValue('C5', 'Nama Peserta');
        $sheet->setCellValue('D5', 'Kelas');
        
        $col = 'E';
        foreach ($soals as $idx => $s) {
            $sheet->setCellValue($col . '5', 'Soal ' . ($idx + 1));
            $col++;
        }
        
        $row = 6;
        foreach($peserta as $index => $p) {
            $sheet->setCellValue('A'.$row, $index + 1);
            $sheet->setCellValueExplicit('B'.$row, $p->siswa->nis ?? '-', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('C'.$row, $p->siswa->nama_lengkap ?? '-');
            $sheet->setCellValue('D'.$row, $p->siswa->kelas->nama_kelas ?? '-');
            
            $col = 'E';
            $jawabans = $semua_jawaban->get($p->id) ?? collect();
            
            foreach ($soals as $s) {
                $ans = $jawabans->firstWhere('id_soal', $s->id);
                $val = '-';
                
                if ($ans && $ans->jawaban_siswa !== null && $ans->jawaban_siswa !== '') {
                    $inp = $ans->jawaban_siswa;
                    
                    if ($s->jenis_soal == 1) {
                        $opsi = $semua_opsi->firstWhere('id', $inp);
                        if ($opsi) {
                            $text = trim(strip_tags($opsi->body));
                            $val = $text ? $text : '[Gambar/Media]';
                        } else {
                            $val = $inp;
                        }
                    } elseif ($s->jenis_soal == 5 || $s->jenis_soal == 2) {
                        $val = trim(strip_tags($inp));
                    } else {
                        $val = 'Terjawab';
                    }
                    
                    // Tambahkan indikator benar/salah atau nilai
                    if ($s->jenis_soal != 2) {
                        $val .= $ans->is_benar ? ' (Benar)' : ' (Salah)';
                    } else {
                        $val .= " \n(Skor: ".($ans->nilai ?? 0).")";
                        $sheet->getStyle($col . $row)->getAlignment()->setWrapText(true);
                    }
                }
                
                $sheet->setCellValue($col . $row, $val);
                $col++;
            }
            $row++;
        }
        
        // Styling headers
        $lastCol = $sheet->getHighestColumn();
        $sheet->getStyle('A5:'.$lastCol.'5')->getFont()->setBold(true);
        $sheet->getStyle('A5:'.$lastCol.'5')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF0F0F0');
        
        foreach(range('A','D') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }
        
        $endCol = $lastCol;
        $endCol++; 
        for($c = 'E'; $c !== $endCol; $c++) {
            $sheet->getColumnDimension($c)->setWidth(30);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Jawaban_Ujian_'.$jadwal_id.'.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($temp_file);

        return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);
    }

    private function migrateJawabanCi4ToLaravel($ujian_id, $soals) {
        try {
            $exists = DB::table('tbl_jawaban_siswa')->where('id_ujian_siswa', $ujian_id)->exists();
            if ($exists) return true;

            $pg = DB::table('jawaban_pg_ujian_siswa')->where('ujian_id', $ujian_id)->get()->groupBy('soal_id');
            $pgm = DB::table('jawaban_pgmulti_ujian_siswa')->where('ujian_id', $ujian_id)->get()->groupBy('soal_id');
            $pgc = DB::table('jawaban_pgcouple_ujian_siswa')->where('ujian_id', $ujian_id)->get()->groupBy('soal_id');
            $pgtf = DB::table('jawaban_pgtf_ujian_siswa')->where('ujian_id', $ujian_id)->get()->groupBy('soal_id');
            $sh = DB::table('jawaban_shortentry_ujian_siswa')->where('ujian_id', $ujian_id)->get()->groupBy('soal_id');
            $es = DB::table('jawaban_esai_ujian_siswa')->where('ujian_id', $ujian_id)->get()->groupBy('soal_id');

            $batch = [];
            $no = 1;
            foreach($soals as $s) {
                $s = (array)$s;
                $sid = $s['id'];
                $jenis = (int)$s['jenis_soal'];
                $ans_str = null;
                $nilai = 0;
                $is_benar = 0;
                
                if ($jenis == 1 && isset($pg[$sid])) {
                    $ans = $pg[$sid]->first();
                    $ans_str = $ans->opsi_id;
                    $is_benar = $ans->is_benar ?? 0;
                } elseif ($jenis == 3 && isset($pgm[$sid])) {
                    $ans_str = json_encode($pgm[$sid]->pluck('opsi_id')->toArray());
                } elseif ($jenis == 4 && isset($pgc[$sid])) {
                    $arr = [];
                    foreach($pgc[$sid] as $a) $arr[$a->couple_id] = $a->opsi_id;
                    $ans_str = json_encode($arr);
                } elseif ($jenis == 6 && isset($pgtf[$sid])) {
                    $arr = [];
                    foreach($pgtf[$sid] as $a) $arr[$a->opsi_id] = $a->tf;
                    $ans_str = json_encode($arr);
                } elseif ($jenis == 5 && isset($sh[$sid])) {
                    $ans = $sh[$sid]->first();
                    $ans_str = $ans->respond;
                    $is_benar = $ans->is_benar ?? 0;
                } elseif ($jenis == 2 && isset($es[$sid])) {
                    $ans = $es[$sid]->first();
                    $ans_str = $ans->esai;
                    $nilai = $ans->nilai ?? 0;
                }

                $batch[] = [
                    'id_ujian_siswa' => $ujian_id,
                    'id_soal' => $sid,
                    'jawaban_siswa' => $ans_str,
                    'nomor_urut' => $no++,
                    'is_benar' => $is_benar,
                    'nilai' => $nilai,
                    'ragu' => 0
                ];
            }
            if (!empty($batch)) {
                DB::table('tbl_jawaban_siswa')->insert($batch);
                DB::table('ujian_siswa')->where('id', $ujian_id)->update(['soal_generated' => 1]);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
