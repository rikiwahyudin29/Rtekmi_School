<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $siswa = DB::table('tbl_siswa')->where('nisn', '0104603302')->first();
    if ($siswa) {
        $ujian = DB::table('ujian_siswa')->where('siswa_id', $siswa->id)->orderBy('id', 'desc')->first();
        if ($ujian) {
            echo "Ujian ID: " . $ujian->id . "\n";
            echo "Nilai PG: " . $ujian->nilai_pg . "\n";
            echo "Nilai Esai: " . $ujian->nilai_esai . "\n";
            
            $jadwal = DB::table('tbl_jadwal_ujian')->where('id', $ujian->jadwal_id)->first();
            echo "Bobot Esai Jadwal: " . $jadwal->bobot_esai . "\n";
            
            $draft = DB::table('draft_ujian')->where('id', $jadwal->id_bank_soal)->first();
            echo "Draft Ujian Object:\n";
            print_r($draft);

            $jawaban_esai = DB::table('tbl_jawaban_siswa')
                ->join('soal_data', 'soal_data.id', '=', 'tbl_jawaban_siswa.id_soal')
                ->where('id_ujian_siswa', $ujian->id)
                ->where('soal_data.jenis_soal', 2)
                ->select('tbl_jawaban_siswa.*')
                ->get();
            echo "Total Jawaban Esai: " . count($jawaban_esai) . "\n";
            foreach($jawaban_esai as $j) {
                echo "Soal ID: " . $j->id_soal . " | Nilai: " . $j->nilai . "\n";
            }
        }
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

