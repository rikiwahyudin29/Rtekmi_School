<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$jadwal_id = 41;

$kelas_concat = \Illuminate\Support\Facades\DB::table('ujian_siswa as us')
    ->join('tbl_siswa as ms', 'ms.id', '=', 'us.siswa_id')
    ->join('tbl_kelas as mk', 'mk.id', '=', 'ms.kelas_id')
    ->where('us.jadwal_id', $jadwal_id)
    ->selectRaw("GROUP_CONCAT(DISTINCT mk.nama_kelas SEPARATOR ', ') as kelas")
    ->value('kelas');

$total_siswa = \App\Models\UjianSiswa::where('jadwal_id', $jadwal_id)->count();

echo json_encode([
    'kelas_concat' => $kelas_concat,
    'total_siswa' => $total_siswa
]);
