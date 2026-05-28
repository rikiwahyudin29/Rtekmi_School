<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

$jadwals = DB::table('tbl_jadwal_ujian')->get();
$jadwalKelas = DB::table('tbl_jadwal_kelas')->get();
$siswas = DB::table('tbl_siswa')->get();

echo "<h3>Jadwal Ujian:</h3><pre>";
print_r($jadwals);
echo "</pre><h3>Jadwal Kelas:</h3><pre>";
print_r($jadwalKelas);
echo "</pre><h3>Siswa:</h3><pre>";
print_r($siswas);
echo "</pre>";
