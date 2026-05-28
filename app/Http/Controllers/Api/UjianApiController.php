<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\JadwalUjian;
use App\Models\Siswa;
use App\Models\UjianSiswa;

class UjianApiController extends Controller
{
    // Helper untuk cari ID Siswa (Dikirim dari HP)
    private function getSiswaInfo($nisn)
    {
        return DB::table('tbl_siswa')
            ->select('id', 'kelas_id')
            ->where('nisn', $nisn)
            ->first();
    }

    // ==========================================
    // 1. AMBIL JADWAL UJIAN AKTIF
    // ==========================================
    public function getJadwal(Request $request)
    {
        $nisn = $request->input('nisn');
        $siswa = $this->getSiswaInfo($nisn);

        if (!$siswa) {
            return response()->json(['status' => false, 'message' => 'Siswa tidak ditemukan.'], 404);
        }

        // Tarik langsung dari tabel ujian_siswa 
        $ujian = DB::table('ujian_siswa as us')
            ->select(
                'us.id as id_ujian_siswa',
                'us.status as status_pengerjaan',
                'us.nilai_pg',
                'us.nilai_esai',
                'ju.id as id_jadwal',
                'ju.waktu_mulai',
                'ju.waktu_selesai',
                'ju.durasi',
                'ju.token',
                'ju.setting_token',
                'ju.nama_ujian as judul_ujian',
                'm.nama_mapel',
                'g.nama_lengkap as pengawas'
            )
            ->join('tbl_jadwal_ujian as ju', 'ju.id', '=', 'us.jadwal_id')
            ->leftJoin('draft_ujian as du', 'du.id', '=', 'ju.id_bank_soal')
            ->leftJoin('tbl_mapel as m', 'm.id', '=', 'du.mapel_id')
            ->leftJoin('tbl_guru as g', 'g.id', '=', 'ju.id_guru')
            ->where('us.siswa_id', $siswa->id)
            ->where('ju.status', 1)
            ->orderBy('ju.waktu_mulai', 'desc')
            ->get();

        return response()->json(['status' => true, 'data' => $ujian], 200);
    }

    // ==========================================
    // 2. VALIDASI TOKEN & DOWNLOAD SEMUA SOAL
    // ==========================================
    public function downloadSoal(Request $request)
    {
        try {
            $nisn      = $request->input('nisn');
            $id_jadwal = $request->input('id_jadwal');
            $token     = strtoupper((string)($request->input('token', '')));

            if (empty($nisn) || empty($id_jadwal)) {
                return response()->json(['status' => false, 'message' => 'NISN atau ID Jadwal kosong.'], 400);
            }

            $siswa = $this->getSiswaInfo($nisn);
            if (!$siswa) return response()->json(['status' => false, 'message' => 'Siswa tidak ditemukan.'], 404);

            $jadwal = DB::table('tbl_jadwal_ujian')->where('id', $id_jadwal)->first();
            if (!$jadwal) return response()->json(['status' => false, 'message' => 'Jadwal tidak valid.'], 404);

            $dbToken = strtoupper((string)($jadwal->token ?? ''));
            if (isset($jadwal->setting_token) && $jadwal->setting_token == 1 && $token !== $dbToken) {
                return response()->json(['status' => false, 'message' => 'Token Ujian Salah!'], 400);
            }

            // ANTI-KECURANGAN: Cek Waktu Berakhir di Server
            $waktu_selesai_ujian = strtotime($jadwal->waktu_selesai);
            if (time() > $waktu_selesai_ujian) {
                return response()->json(['status' => false, 'message' => 'Gagal! Jadwal ujian ini sudah ditutup.'], 400);
            }

            $sesi = DB::table('ujian_siswa')->where(['jadwal_id' => $id_jadwal, 'siswa_id' => $siswa->id])->first();
            if (!$sesi) return response()->json(['status' => false, 'message' => 'Anda tidak terdaftar di ujian ini.'], 400);
            if ($sesi->status == 2) return response()->json(['status' => false, 'message' => 'Anda sudah menyelesaikan ujian ini.'], 400);

            if ($sesi->status == 0) {
                $durasi_menit = (int)($jadwal->durasi ?? 90);
                $start_at = time();
                $end_at = $start_at + ($durasi_menit * 60);

                if ($end_at > $waktu_selesai_ujian) {
                    $end_at = $waktu_selesai_ujian;
                }

                DB::table('ujian_siswa')->where('id', $sesi->id)->update([
                    'status' => 1,
                    'start_at' => $start_at, 
                    'end_at' => $end_at,
                    'jwt' => 'UNLOCKED_BY_ADMIN' 
                ]);
            }

            $soal = DB::table('draft_soal as ds')
                ->select('sd.id as id_soal', 'sd.jenis_soal', 'sd.question as teks_soal') 
                ->join('soal_data as sd', 'sd.id', '=', 'ds.soal_id')
                ->where('ds.draft_id', $jadwal->id_bank_soal)
                ->get()
                ->map(function($item) { return (array) $item; })
                ->toArray();

            $draft = DB::table('draft_ujian')->where('id', $jadwal->id_bank_soal)->first();
            if ($draft && isset($draft->acak_soal) && $draft->acak_soal == 1) {
                shuffle($soal);
            }

            $baseUrlServer = rtrim(url('/'), '/'); 
            
            $searchPath1 = '../../../uploads/';
            $searchPath2 = '../../uploads/';
            $replacePath = $baseUrlServer . '/uploads/';

            foreach ($soal as &$s) {
                if (!empty($s['teks_soal'])) {
                    $s['teks_soal'] = str_replace($searchPath1, $replacePath, $s['teks_soal']);
                    $s['teks_soal'] = str_replace($searchPath2, $replacePath, $s['teks_soal']);
                }

                $queryOpsi = DB::table('soal_opsi')
                    ->select('id as id_opsi', 'body as teks_opsi') 
                    ->where('soal_id', $s['id_soal']);
                    
                if ($draft && isset($draft->acak_opsi) && $draft->acak_opsi == 1 && in_array($s['jenis_soal'], [1, 3])) {
                    $queryOpsi->inRandomOrder();
                }
                
                $s['opsi'] = $queryOpsi->get()->map(function($item) { return (array) $item; })->toArray();

                foreach ($s['opsi'] as &$o) {
                    if (!empty($o['teks_opsi'])) {
                        $o['teks_opsi'] = str_replace($searchPath1, $replacePath, $o['teks_opsi']);
                        $o['teks_opsi'] = str_replace($searchPath2, $replacePath, $o['teks_opsi']);
                    }
                }

                if ($s['jenis_soal'] == '4') {
                    $s['couple'] = DB::table('soal_data_couple')
                        ->select('id as id_couple', 'body as teks_couple')
                        ->where('soal_id', $s['id_soal'])
                        ->get()->map(function($item) { return (array) $item; })->toArray();
                        
                    foreach ($s['couple'] as &$c) {
                         if (!empty($c['teks_couple'])) {
                             $c['teks_couple'] = str_replace($searchPath1, $replacePath, $c['teks_couple']);
                             $c['teks_couple'] = str_replace($searchPath2, $replacePath, $c['teks_couple']);
                         }
                    }
                } else {
                    $s['couple'] = [];
                }
            }

            $timeout_asli = $draft->timeout ?? 90;
            $min_finish = $draft->minFinishTime ?? 0;

            return response()->json([
                'status' => true,
                'message' => 'Soal berhasil didownload',
                'id_ujian_siswa' => $sesi->id, 
                'durasi' => $timeout_asli, 
                'min_finish' => $min_finish, 
                'data_soal' => $soal
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false, 
                'message' => 'FATAL ERROR: ' . $e->getMessage() . ' | Baris: ' . $e->getLine()
            ], 500);
        }
    }

    // ==========================================
    // 3. TERIMA JAWABAN (BULK) & AUTO SCORE
    // ==========================================
    public function submitJawaban(Request $request)
    {
        try {
            $id_ujian_siswa = $request->input('id_ujian_siswa'); 
            $jawaban_array  = $request->input('data_jawaban', []); 
            
            if (empty($jawaban_array)) {
                return response()->json(['status' => false, 'message' => 'Data jawaban kosong atau rusak.'], 400);
            }

            $sesi = DB::table('ujian_siswa')->where('id', $id_ujian_siswa)->first();
            if (!$sesi) return response()->json(['status' => false, 'message' => 'Sesi ujian tidak valid.'], 404);
            if ($sesi->status == 2) return response()->json(['status' => false, 'message' => 'Ujian ini sudah selesai.'], 400);

            DB::beginTransaction();

            // Hapus jawaban sebelumnya jika ada (untuk replace bersih)
            DB::table('tbl_jawaban_siswa')->where('id_ujian_siswa', $id_ujian_siswa)->delete();

            $batch_jawaban = [];
            foreach ($jawaban_array as $j) {
                $soal_id    = $j['soal_id'] ?? '';
                $jenis_soal = (string)($j['jenis_soal'] ?? '');
                $jawaban    = $j['jawaban'] ?? ''; 

                if (empty($jawaban) || empty($soal_id)) continue; 

                $formatted_jawaban = '';
                
                switch ($jenis_soal) {
                    case '1': 
                        $formatted_jawaban = $jawaban; 
                        break;
                    case '2': 
                    case '5': 
                        $formatted_jawaban = $jawaban; 
                        break;
                    case '3': 
                        $arr = [];
                        foreach (explode(',', $jawaban) as $val) {
                            if (trim($val) !== '') $arr[] = trim($val);
                        }
                        $formatted_jawaban = json_encode($arr);
                        break;
                    case '6': 
                        $arr = [];
                        foreach (explode(',', $jawaban) as $val) {
                            if (trim($val) !== '') {
                                $ex = explode('-', $val);
                                if(count($ex) == 2) $arr[$ex[0]] = $ex[1];
                            }
                        }
                        $formatted_jawaban = json_encode($arr);
                        break;
                    case '4': 
                        $arr = [];
                        foreach (explode(',', $jawaban) as $val) {
                            if (trim($val) !== '') {
                                $ex = explode('-', $val);
                                if(count($ex) == 2) $arr[$ex[0]] = $ex[1];
                            }
                        }
                        $formatted_jawaban = json_encode($arr);
                        break;
                }

                $batch_jawaban[] = [
                    'id_ujian_siswa' => $id_ujian_siswa,
                    'id_soal' => $soal_id,
                    'jawaban_siswa' => $formatted_jawaban,
                    'is_benar' => 0,
                    'nilai' => 0
                ];
            }

            if (!empty($batch_jawaban)) {
                DB::table('tbl_jawaban_siswa')->insert($batch_jawaban);
            }

            // AUTO-KOREKSI (Persis seperti di DetailJadwalController)
            $jadwal = DB::table('tbl_jadwal_ujian')->where('id', $sesi->jadwal_id)->first();
            $draft = DB::table('draft_ujian')->where('id', $jadwal->id_bank_soal)->first();
            
            $list = DB::table('tbl_jawaban_siswa')
                ->select('tbl_jawaban_siswa.*', 'soal_data.jenis_soal')
                ->join('soal_data', 'soal_data.id', '=', 'tbl_jawaban_siswa.id_soal')
                ->where('id_ujian_siswa', $id_ujian_siswa)
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
                        $tot = DB::table('soal_opsi')->where('soal_id', $j->id_soal)->count();
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
                        $kunciObj = DB::table('soal_opsi')->select('id')->where(['soal_id'=>$j->id_soal,'is_key'=>1])->get();
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
                        $tot = DB::table('soal_data_couple')->where('soal_id', $j->id_soal)->count(); 
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
                        $kunci = DB::table('soal_opsi')->where(['soal_id'=>$j->id_soal,'is_key'=>1])->first();
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
                ->where('id_ujian_siswa', $id_ujian_siswa)->where('soal_data.jenis_soal', 2)->sum('nilai');
            $jml_esai = $draft->visible_esai ?? 0;
            $bobot_esai = $jadwal->bobot_esai > 0 ? $jadwal->bobot_esai : ($draft->bobot_esai ?? 0);
            $final_esai = $jml_esai > 0 ? ($total_nilai_esai / ($jml_esai * 100)) * $bobot_esai : 0;

            DB::table('ujian_siswa')->where('id', $id_ujian_siswa)->update([
                'status' => 2,
                'end_at' => time(),
                'pg_benar' => $ben_pg,
                'shortentry_benar' => $ben_short,
                'pgmulti_benar' => $ben_pgmulti,
                'pgtf_benar' => $ben_pgtf,
                'pgcouple_benar' => $ben_pgcouple,
                'nilai_pg' => round($final_pg, 2),
                'nilai_esai' => round($final_esai, 2)
            ]);

            DB::commit();

            return response()->json(['status' => true, 'message' => 'Ujian Selesai! Jawaban tersinkronisasi.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false, 
                'message' => 'FATAL ERROR: ' . $e->getMessage() . ' | Baris: ' . $e->getLine()
            ], 500);
        }
    }

    // ==========================================
    // 4. API CEK WAKTU & STATUS 
    // ==========================================
    public function cekWaktu(Request $request)
    {
        $id_ujian_siswa = $request->input('id_ujian_siswa');
        $sesi = DB::table('ujian_siswa')->where('id', $id_ujian_siswa)->first();
        if (!$sesi) return response()->json(['status' => false], 404);

        $jadwal = DB::table('tbl_jadwal_ujian')->where('id', $sesi->jadwal_id)->first();
        $waktu_selesai_ujian = strtotime($jadwal->waktu_selesai);
        
        $end_at = $sesi->end_at;
        if (empty($end_at)) $end_at = $waktu_selesai_ujian;

        $sisa_waktu_detik = $end_at - time();
        if ($sisa_waktu_detik < 0) $sisa_waktu_detik = 0;

        $is_unlocked_by_admin = (empty($sesi->jwt) || $sesi->jwt === 'UNLOCKED_BY_ADMIN');

        if ($is_unlocked_by_admin) {
            DB::table('ujian_siswa')->where('id', $sesi->id)->update(['jwt' => 'ANDROID_APP_SESSION']);
        }

        return response()->json([
            'status'            => true,
            'sisa_waktu_milis'  => $sisa_waktu_detik * 1000,
            'status_pengerjaan' => (int)$sesi->status,
            'is_unlocked'       => $is_unlocked_by_admin 
        ], 200);
    }
}
