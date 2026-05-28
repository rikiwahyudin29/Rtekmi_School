<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$jadwalUjian = \App\Models\JadwalUjian::with(['jenisUjian', 'guru', 'ruangan'])
    ->orderBy('id', 'desc')
    ->get();

$jadwalUjian->transform(function ($jadwal) {
    $kelas_concat = \Illuminate\Support\Facades\DB::table('ujian_siswa as us')
                            ->join('tbl_siswa as ms', 'ms.id', '=', 'us.siswa_id')
                            ->join('tbl_kelas as mk', 'mk.id', '=', 'ms.kelas_id')
                            ->where('us.jadwal_id', $jadwal->id)
                            ->selectRaw("GROUP_CONCAT(DISTINCT mk.nama_kelas SEPARATOR ', ') as kelas")
                            ->value('kelas');
    $jadwal->kelas_concat = $kelas_concat;
    return $jadwal;
});

foreach($jadwalUjian as $j) {
    echo "ID: " . $j->id . " - KELAS: " . ($j->kelas_concat ?? 'NULL') . "\n";
}
