<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$columns = Schema::getColumnListing('tbl_jawaban_siswa');
echo implode(', ', $columns) . '<br>';

$ujianId = 2479; // Erlita's Ujian ID

$listSoal = DB::table('tbl_jawaban_siswa')
            ->select('tbl_jawaban_siswa.*', 'tbl_jawaban_siswa.id_soal as soal_id', 'tbl_jawaban_siswa.id_ujian_siswa as ujian_siswa_id', 'soal_data.question as pertanyaan', 'soal_data.jenis_soal', 'soal_data.shortentry')
            ->join('soal_data', 'soal_data.id', '=', 'tbl_jawaban_siswa.id_soal')
            ->where('id_ujian_siswa', $ujianId)
            ->orderBy('tbl_jawaban_siswa.nomor_urut', 'ASC')
            ->get()->map(function($item) {
                return (array) $item;
            })->toArray();

echo "Total listSoal query result: " . count($listSoal) . "<br>";
if (count($listSoal) > 0) {
    echo "<pre>"; print_r($listSoal[0]); echo "</pre>";
}
