<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$all = DB::table('ujian_siswa')->get();
foreach($all as $u) {
    echo "ID: {$u->id}, Status: {$u->status}, Start: {$u->start_at}, End: {$u->end_at}, Jadwal: {$u->jadwal_id}, Siswa: {$u->siswa_id}<br>";
}
