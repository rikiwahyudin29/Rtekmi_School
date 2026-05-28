<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$query = \App\Models\JadwalUjian::with(['draftUjian', 'jenisUjian', 'ruangan'])->orderBy('id', 'desc');
$jadwalUjian = $query->paginate(1);
$jadwalUjian->getCollection()->transform(function ($jadwal) {
    $jadwal->kelas_concat = "X IPS 1";
    return $jadwal;
});

echo "JSON DUMP:\n";
echo json_encode($jadwalUjian);
