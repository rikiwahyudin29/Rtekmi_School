<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class UjianController extends Controller
{
    private function getSiswaData()
    {
        $user = Auth::user();
        if (!$user) return null;
        
        $siswa = DB::table('tbl_siswa')->where('user_id', $user->id)->first();
            
        if (!$siswa) {
            $siswa = DB::table('tbl_siswa')->where('nisn', $user->username)->first();
            if ($siswa) {
                DB::table('tbl_siswa')->where('id', $siswa->id)->update(['user_id' => $user->id]);
            }
        }
        return $siswa;
    }

    private function getSiswaId()
    {
        $siswa = $this->getSiswaData();
        return $siswa ? $siswa->id : null;
    }

    private function getKelasId()
    {
        $siswa = $this->getSiswaData();
        return $siswa ? $siswa->kelas_id : 0;
    }

    // 1. DAFTAR UJIAN & HISTORI NILAI
    public function index()
    {
        $id_siswa = $this->getSiswaId();
        $id_kelas = $this->getKelasId();
        
        if (!$id_siswa) {
            return redirect()->route('dashboard')->with('error', 'Sesi tidak valid atau Anda bukan terdaftar sebagai siswa aktif.');
        }
        
        $now = date('Y-m-d H:i:s');
        
        // Ambil Data Jadwal + Setting Show Score
        $ujian = DB::table('ujian_siswa')
            ->select(
                'tbl_jadwal_ujian.id as id_jadwal',
                'tbl_jadwal_ujian.nama_ujian as judul_ujian',
                'tbl_jadwal_ujian.waktu_mulai',
                'tbl_jadwal_ujian.waktu_selesai',
                'tbl_jadwal_ujian.durasi',
                'tbl_jadwal_ujian.token',
                'tbl_jadwal_ujian.setting_show_score', 
                'tbl_jadwal_ujian.setting_token', 
                'draft_ujian.id as id_bank_soal',
                'draft_ujian.nama as nama_draft',
                'tbl_mapel.nama_mapel',
                'tbl_guru.nama_lengkap as nama_guru',
                'ujian_siswa.id as id_sesi',
                'ujian_siswa.status as status_ujian_siswa',
                'ujian_siswa.hasCheating as is_locked',
                'ujian_siswa.nilai_pg',
                'ujian_siswa.nilai_esai',
                'ujian_siswa.end_at as waktu_submit',
                'ujian_siswa.start_at'
            )
            ->join('tbl_jadwal_ujian', 'tbl_jadwal_ujian.id', '=', 'ujian_siswa.jadwal_id')
            ->join('draft_ujian', 'draft_ujian.id', '=', 'tbl_jadwal_ujian.id_bank_soal')
            ->join('tbl_mapel', 'tbl_mapel.id', '=', 'draft_ujian.mapel_id')
            ->leftJoin('tbl_guru', 'tbl_guru.id', '=', 'tbl_jadwal_ujian.id_guru') 
            ->where('ujian_siswa.siswa_id', $id_siswa)
            ->where('tbl_jadwal_ujian.status_ujian', 'AKTIF') 
            ->orderBy('tbl_jadwal_ujian.waktu_mulai', 'DESC')
            ->get()
            ->map(function ($u) use ($now) {
                $u = (array) $u;
                
                $u['status_ujian'] = $u['status_ujian_siswa'] !== null ? $u['status_ujian_siswa'] : 0; 
                $u['nilai_saya'] = (floatval($u['nilai_pg'] ?? 0)) + (floatval($u['nilai_esai'] ?? 0));
                
                if ($u['is_locked'] == 1) $u['status_ujian_text'] = 'TERKUNCI';
                else if ($u['status_ujian_siswa'] == 2) $u['status_ujian_text'] = 'SELESAI';
                else if (empty($u['start_at'])) $u['status_ujian_text'] = 'BELUM_KERJA';
                else $u['status_ujian_text'] = 'MENGERJAKAN';

                // Status Waktu Global
                if ($now < $u['waktu_mulai']) $u['status_waktu'] = 'BELUM_MULAI';
                elseif ($now > $u['waktu_selesai'] && $u['status_ujian_text'] !== 'SELESAI') $u['status_waktu'] = 'TERLEWAT';
                else $u['status_waktu'] = 'BERLANGSUNG';
                
                if ($u['status_ujian_text'] === 'SELESAI') {
                    $u['status_waktu'] = 'SELESAI';
                }

                $u['id'] = $u['id_jadwal']; 
                return $u;
            });

        return Inertia::render('Siswa/Ujian/Index', [
            'ujian' => $ujian
        ]);
    }

    // 2. HALAMAN KONFIRMASI TOKEN
    public function konfirmasi($idJadwal)
    {
        $jadwal = DB::table('tbl_jadwal_ujian')
            ->select('tbl_jadwal_ujian.*', 'draft_ujian.nama as nama_draft', 'tbl_mapel.nama_mapel')
            ->join('draft_ujian', 'draft_ujian.id', '=', 'tbl_jadwal_ujian.id_bank_soal')
            ->join('tbl_mapel', 'tbl_mapel.id', '=', 'draft_ujian.mapel_id')
            ->where('tbl_jadwal_ujian.id', $idJadwal)->first();
            
        if (!$jadwal) return redirect()->route('siswa.ujian.index');
        
        $jadwalArray = (array) $jadwal;
        
        return Inertia::render('Siswa/Ujian/Konfirmasi', [
            'jadwal' => $jadwalArray
        ]);
    }

    // 3. MULAI SESI UJIAN BARU
    public function mulai(Request $request)
    {
        $id_jadwal = $request->id_jadwal;
        $token = $request->token;
        
        $jadwal = DB::table('tbl_jadwal_ujian')->where('id', $id_jadwal)->first();
        
        if (!$jadwal) {
            return redirect()->back()->with('error', 'Jadwal tidak ditemukan!');
        }

        // Cek Token
        if ($jadwal->setting_token == 1 && $token !== strtoupper($jadwal->token)) {
            return redirect()->back()->with('error', 'Token yang dimasukkan salah!');
        }

        $id_siswa = $this->getSiswaId();

        // Cek Sesi Existing
        $cek = DB::table('ujian_siswa')
            ->where('jadwal_id', $id_jadwal)
            ->where('siswa_id', $id_siswa)
            ->first();

        if (!$cek) return redirect()->back()->with('error', 'Anda tidak terdaftar di ujian ini.');

        if ($cek->status == 2) return redirect()->route('siswa.ujian.index')->with('error', 'Anda sudah selesai ujian ini.');
        if ($cek->hasCheating == 1) return redirect()->route('siswa.ujian.locked', $cek->id);

        // Jika sudah dimulai, langsung redirect ke halaman kerja
        if (!empty($cek->start_at)) {
            return redirect()->route('siswa.ujian.kerjakan', $cek->id);
        }

        // Buat Sesi Baru (Mulai Ujian)
        $now = time();
        $waktuSelesaiDurasi = strtotime("+$jadwal->durasi minutes");
        $jadwalWaktuSelesai = strtotime($jadwal->waktu_selesai);
        
        $waktuFinal = ($waktuSelesaiDurasi < $jadwalWaktuSelesai) ? $waktuSelesaiDurasi : $jadwalWaktuSelesai;

        DB::table('ujian_siswa')->where('id', $cek->id)->update([
            'status'       => 1, // Sedang Mengerjakan
            'hasCheating'  => 0,
            'start_at'     => $now,
            'end_at'       => $waktuFinal,
            'ip_address'   => substr($request->ip(), 0, 50)
        ]);

        $idUjian = $cek->id;

        // Generate Soal jika belum ada
        if ($cek->soal_generated != 1) {
            $draft = DB::table('draft_ujian')->where('id', $jadwal->id_bank_soal)->first();

            // Generate Soal ke Tabel Jawaban Siswa
            $soalPG = DB::table('soal_data')->where('bank_id', $draft->bank_soal_id)->where('jenis_soal', 1)->get()->toArray();
            $soalEsai = DB::table('soal_data')->where('bank_id', $draft->bank_soal_id)->where('jenis_soal', 2)->get()->toArray();
            $soalPGMulti = DB::table('soal_data')->where('bank_id', $draft->bank_soal_id)->where('jenis_soal', 3)->get()->toArray();
            $soalPGFriend = DB::table('soal_data')->where('bank_id', $draft->bank_soal_id)->where('jenis_soal', 4)->get()->toArray();
            $soalShort = DB::table('soal_data')->where('bank_id', $draft->bank_soal_id)->where('jenis_soal', 5)->get()->toArray();
            $soalPGTF = DB::table('soal_data')->where('bank_id', $draft->bank_soal_id)->where('jenis_soal', 6)->get()->toArray();

            if ($draft->acak_soal == 1) {
                shuffle($soalPG);
                shuffle($soalEsai);
                shuffle($soalPGMulti);
                shuffle($soalPGFriend);
                shuffle($soalShort);
                shuffle($soalPGTF);
            }

            $soalPG = array_slice($soalPG, 0, $draft->visible_pg);
            $soalEsai = array_slice($soalEsai, 0, $draft->visible_esai);
            $soalPGMulti = array_slice($soalPGMulti, 0, $draft->visible_pgmulti);
            $soalPGFriend = array_slice($soalPGFriend, 0, $draft->visible_pgcouple);
            $soalShort = array_slice($soalShort, 0, $draft->visible_shortentry);
            $soalPGTF = array_slice($soalPGTF, 0, $draft->visible_pgtf);

            $semuaSoal = array_merge($soalPG, $soalPGMulti, $soalPGFriend, $soalShort, $soalPGTF, $soalEsai);

            $batch = []; 
            $no = 1;
            foreach ($semuaSoal as $s) { 
                $batch[] = [
                    'id_ujian_siswa' => $idUjian, 
                    'id_soal' => $s->id, 
                    'nomor_urut' => $no++,
                    'ragu' => 0,
                    'is_benar' => 0,
                    'nilai' => 0,
                ]; 
            }

            if (count($batch) > 0) {
                DB::table('tbl_jawaban_siswa')->insert($batch);
                DB::table('ujian_siswa')->where('id', $idUjian)->update(['soal_generated' => 1]);
            }
        }

        return redirect()->route('siswa.ujian.kerjakan', $idUjian);
    }

    // 4. HALAMAN MENGERJAKAN SOAL
    public function kerjakan($idUjianSiswa)
    {
        $id_siswa = $this->getSiswaId();
        
        $sesi = DB::table('ujian_siswa')->where('id', $idUjianSiswa)->first();
        if (!$sesi || $sesi->siswa_id != $id_siswa) return redirect()->route('siswa.ujian.index');
        
        if ($sesi->status == 2) return redirect()->route('siswa.ujian.index')->with('error', 'Ujian telah selesai.');
        if ($sesi->hasCheating == 1) return redirect()->route('siswa.ujian.locked', $idUjianSiswa);

        // Update Heartbeat (IP Address as activity tracker)
        DB::table('ujian_siswa')->where('id', $idUjianSiswa)->update(['ip_address' => substr(request()->ip(), 0, 50)]);

        $jadwal = DB::table('tbl_jadwal_ujian')->where('id', $sesi->jadwal_id)->first();
        $draft = DB::table('draft_ujian')->where('id', $jadwal->id_bank_soal)->first();

        // Ambil Soal & Jawaban
        $listSoal = DB::table('tbl_jawaban_siswa')
            ->select('tbl_jawaban_siswa.*', 'tbl_jawaban_siswa.id_soal as soal_id', 'tbl_jawaban_siswa.id_ujian_siswa as ujian_siswa_id', 'soal_data.question as pertanyaan', 'soal_data.jenis_soal', 'soal_data.shortentry')
            ->join('soal_data', 'soal_data.id', '=', 'tbl_jawaban_siswa.id_soal')
            ->where('id_ujian_siswa', $idUjianSiswa)
            ->orderBy('tbl_jawaban_siswa.nomor_urut', 'ASC')
            ->get()->map(function($item) {
                return (array) $item;
            })->toArray();

        // Merge dengan Cache Redis
        $keyRedis = "ujian_tmp_" . $idUjianSiswa;
        $cached = Cache::get($keyRedis); 
        
        if ($cached && is_array($cached)) {
            foreach ($listSoal as &$item) {
                if (isset($cached[$item['soal_id']])) {
                    $item['jawaban_siswa'] = $cached[$item['soal_id']]['jawaban_siswa'] ?? $item['jawaban_siswa'];
                    $item['ragu'] = $cached[$item['soal_id']]['ragu'] ?? $item['ragu'];
                }
            }
        }

        // Ambil Opsi Jawaban
        foreach ($listSoal as &$item) {
            // Jika soal PG, PG Kompleks, PG TF, atau Menjodohkan
            if (in_array($item['jenis_soal'], [1, 3, 6])) {
                $builder = DB::table('soal_opsi')->select('soal_opsi.*', 'body as teks')->where('soal_id', $item['soal_id']);
                if ($draft->acak_opsi == 1 && $item['jenis_soal'] == 1) $builder->inRandomOrder(); 
                else $builder->orderBy('id', 'ASC');
                
                $item['opsi'] = $builder->get()->toArray();
            } else if ($item['jenis_soal'] == 4) {
                // Pasangan
                $item['opsi_kiri'] = DB::table('soal_data_couple')->select('soal_data_couple.*', 'body as teks')->where('soal_id', $item['soal_id'])->get()->toArray();
                $item['opsi_kanan'] = DB::table('soal_opsi')->select('soal_opsi.*', 'body as teks')->where('soal_id', $item['soal_id'])->whereNotNull('soal_couple_id')->get()->toArray();
            }
        }

        return Inertia::render('Siswa/Ujian/Kerjakan', [
            'sesi' => $sesi, 
            'soal' => $listSoal, 
            'jadwal' => $jadwal,
            'draft' => $draft
        ]);
    }

    // 5. SIMPAN JAWABAN (AJAX) - ANTI HILANG/KEHAPUS
    public function simpanJawaban(Request $request)
    {
        $idUjian = $request->id_ujian_siswa;
        $idSoal  = $request->soal_id; 
        
        $newData = []; 
        
        if ($request->has('jawaban_siswa')) {
            $val = $request->jawaban_siswa;
            if (is_array($val)) {
                $newData['jawaban_siswa'] = json_encode($val);
            } else {
                $newData['jawaban_siswa'] = $val;
            }
        }

        if ($request->has('ragu')) {
            $newData['ragu'] = $request->ragu ? 1 : 0;
        }

        if (!empty($newData)) {
            // Simpan ke Cache
            $key = "ujian_tmp_" . $idUjian;
            $curr = Cache::get($key, []);
            
            if (isset($curr[$idSoal])) {
                $newData = array_merge($curr[$idSoal], $newData); 
            }
            $curr[$idSoal] = $newData; 
            Cache::put($key, $curr, now()->addHours(3)); 

            // Simpan ke Database Permanen
            DB::table('tbl_jawaban_siswa')
                ->where('id_ujian_siswa', $idUjian)
                ->where('id_soal', $idSoal)
                ->update($newData);
        }

        return response()->json([
            'status'     => 'success',
            'pesan'      => 'Jawaban berhasil disimpan'
        ]);
    }

    // 6. SELESAI UJIAN & HITUNG NILAI
    public function selesaiUjian(Request $request)
    {
        $idUjian = $request->id_ujian_siswa;
        $isAuto  = $request->is_auto; // Flag auto submit jika waktu habis

        $sesi = DB::table('ujian_siswa')->where('id', $idUjian)->first();
        if (!$sesi) return response()->json(['status' => 'error', 'message' => 'Sesi tidak ditemukan']);
        if ($sesi->status == 2) return response()->json(['status' => 'success', 'redirect' => route('siswa.ujian.index')]);

        $jadwal = DB::table('tbl_jadwal_ujian')->where('id', $sesi->jadwal_id)->first();
        $draft = DB::table('draft_ujian')->where('id', $jadwal->id_bank_soal)->first();

        // Cek Minimal Waktu
        if ($isAuto != '1') {
            $minMenit = $draft->minFinishTime ?? 0;
            $waktuMulai = $sesi->start_at ?? time(); 
            $selisih = floor((time() - $waktuMulai) / 60);
            
            if ($selisih < $minMenit) {
                return response()->json([
                    'status' => 'error', 
                    'message' => "Belum bisa selesai. Minimal waktu pengerjaan: $minMenit menit. Anda baru berjalan $selisih menit."
                ]);
            }
        }

        // Hapus Cache Redis
        Cache::forget("ujian_tmp_" . $idUjian);

        // Ambil Jawaban untuk Scoring
        $list = DB::table('tbl_jawaban_siswa')
            ->select('tbl_jawaban_siswa.*', 'tbl_jawaban_siswa.id_soal as soal_id', 'tbl_jawaban_siswa.id_ujian_siswa as ujian_siswa_id', 'soal_data.jenis_soal')
            ->join('soal_data', 'soal_data.id', '=', 'tbl_jawaban_siswa.id_soal')
            ->where('id_ujian_siswa', $idUjian)
            ->get();
            
        $skorTot = 0; $bobotTot = 0; $ben = 0; $sal = 0; $kos = 0;

        foreach($list as $j) {
            $sc = 0; $cor = 0;
            $inp = $j->jawaban_siswa;
            $bobot = 0;

            // Logic Scoring
            if ($j->jenis_soal == 1) { // PG
                $bobot = $jadwal->bobot_pg ?? 1;
                if ($inp !== null && $inp !== '') {
                    if(DB::table('soal_opsi')->where(['id'=>$inp, 'is_key'=>1])->exists()) { $sc=1; $cor=1; }
                }
            } 
            elseif ($j->jenis_soal == 6) { // Benar Salah (Matrix)
                $bobot = $jadwal->bobot_pg ?? 1;
                if ($inp) {
                    $obj = json_decode($inp, true); // {opsi_id: "1" atau "0"}
                    $tot = DB::table('soal_opsi')->where('soal_id', $j->soal_id)->count();
                    $hit = 0;
                    if($obj && is_array($obj)){
                        foreach($obj as $k => $v){
                            if(DB::table('soal_opsi')->where(['id'=>$k, 'is_key'=>$v])->exists()) $hit++;
                        }
                        if($tot>0){ $sc = $hit/$tot; if($sc==1) $cor=1; }
                    }
                }
            } 
            elseif ($j->jenis_soal == 3) { // PG Kompleks
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
                        if($arrStr == $kunci){ $sc=1; $cor=1; } 
                    }
                }
            } 
            elseif ($j->jenis_soal == 4) { // Menjodohkan
                $bobot = $jadwal->bobot_pg ?? 1;
                if ($inp) {
                    $obj = json_decode($inp, true); // {id_kiri: id_kanan}
                    $tot = DB::table('soal_data_couple')->where('soal_id', $j->soal_id)->count(); 
                    $hit = 0;
                    if($obj && is_array($obj)){ 
                        foreach($obj as $k => $v){ 
                            if(DB::table('soal_opsi')->where(['id'=>$v, 'soal_couple_id'=>$k])->exists()) $hit++; 
                        } 
                        if($tot>0){ $sc = $hit/$tot; if($sc==1) $cor=1; } 
                    }
                }
            } 
            elseif ($j->jenis_soal == 5) { // Isian Singkat
                $bobot = $jadwal->bobot_esai ?? 1;
                if ($inp !== null && $inp !== '') {
                    $kunci = DB::table('soal_opsi')->where(['soal_id'=>$j->soal_id,'is_key'=>1])->first();
                    if($kunci && trim(strtolower($inp)) == trim(strtolower($kunci->body))) { $sc=1; $cor=1; }
                }
            }
            elseif ($j->jenis_soal == 2) { // Esai
                $bobot = $jadwal->bobot_esai ?? 1;
            }

            // Hitung Statistik Benar/Salah/Kosong
            if($sc==0 && (empty($inp))) $kos++; 
            elseif($sc==0) $sal++; 
            else $ben++;

            // Hitung Skor Berbobot
            $nilai = $sc * $bobot; 
            $skorTot += $nilai; 
            $bobotTot += $bobot;
            
            DB::table('tbl_jawaban_siswa')->where('id', $j->id)->update(['is_benar'=>$cor, 'nilai'=>$nilai]);
        }

        // Nilai Akhir (Skala 100)
        $final = ($bobotTot > 0) ? round(($skorTot / $bobotTot) * 100, 2) : 0;
        
        // Update Status Selesai
        DB::table('ujian_siswa')->where('id', $idUjian)->update([
            'status'       => 2, // Selesai Ujian
            'end_at'       => time(), 
            'nilai_pg'     => $final,
            'pg_benar'     => $ben,
        ]);

        return response()->json(['status' => 'success', 'redirect' => route('siswa.ujian.index')]);
    }

    public function catatPelanggaran(Request $request) 
    {
        $id = $request->id_ujian_siswa; 
        $jenis = $request->jenis;
        
        $sesi = DB::table('ujian_siswa')
            ->select('ujian_siswa.*', 'tbl_jadwal_ujian.setting_strict', 'tbl_jadwal_ujian.setting_max_violation')
            ->join('tbl_jadwal_ujian', 'tbl_jadwal_ujian.id', '=', 'ujian_siswa.jadwal_id')
            ->where('ujian_siswa.id', $id)
            ->first();
        
        // Jika ujian sudah selesai/tidak ada
        if(!$sesi || $sesi->status == 2) {
            return response()->json([
                'status'     => 'finished',
            ]);
        }
        
        if($sesi->setting_strict == 1) {
            if($jenis == 'timeout') {
                DB::table('ujian_siswa')->where('id', $id)->update(['hasCheating' => 1]);
                return response()->json([
                    'status'     => 'locked', 
                    'msg'        => 'Waktu toleransi habis. Ujian terkunci.',
                ]);
            }
            
            // Simplified violation logic: kick immediately if strict since no violation counter exists
            DB::table('ujian_siswa')->where('id', $id)->update(['status' => 2, 'hasCheating' => 1]); // Status 2 = Selesai (Kicked)
            return response()->json([
                'status'     => 'kicked', 
                'msg'        => 'Anda didiskualifikasi karena melakukan pelanggaran.',
            ]);
        }
        
        return response()->json([
            'status'     => 'warning',
            'msg'        => 'Peringatan! Anda terdeteksi keluar dari layar ujian!',
            'sisa_nyawa' => 0,    
        ]);
    }

    public function locked($idUjianSiswa) {
        $id_siswa = $this->getSiswaId();
        $sesi = DB::table('ujian_siswa')->where('id', $idUjianSiswa)->first();
        
        if (!$sesi || $sesi->siswa_id != $id_siswa) return redirect()->route('siswa.ujian.index');
        
        return Inertia::render('Siswa/Ujian/Locked', [
            'sesi' => $sesi
        ]);
    }
}
