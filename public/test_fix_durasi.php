<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

$jadwals = DB::table('tbl_jadwal_ujian')->get();
$fixedCount = 0;

foreach($jadwals as $jadwal) {
    $draft = DB::table('draft_ujian')->where('id', $jadwal->id_bank_soal)->first();
    if ($draft && $draft->timeout > 0) {
        DB::table('tbl_jadwal_ujian')->where('id', $jadwal->id)->update(['durasi' => $draft->timeout]);
        $fixedCount++;
    }
}

echo "Fixed $fixedCount jadwal ujian durasi.";
