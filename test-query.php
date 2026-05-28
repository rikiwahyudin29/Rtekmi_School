<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Siswa;

$siswaQuery = Siswa::with('kelas');
echo "Total Siswa in DB: " . Siswa::count() . "\n";
echo "Total Siswa after query: " . $siswaQuery->count() . "\n";
$masterSiswa = $siswaQuery->get();

$terdaftarIds = [];

$data = $masterSiswa->map(function ($s) use ($terdaftarIds) {
    return [
        'id' => $s->id,
        'nama' => $s->nama_lengkap,
        'nis' => $s->nis,
        'nisn' => $s->nisn,
        'exists' => in_array($s->id, $terdaftarIds)
    ];
});

echo "Mapped data count: " . $data->count() . "\n";
echo "First item: " . json_encode($data->first()) . "\n";
