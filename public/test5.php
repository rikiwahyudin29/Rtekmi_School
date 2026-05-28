<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$jadwal_id = 41;
try {
    $jadwal = \App\Models\JadwalUjian::with(['draftUjian.mapel', 'ruangan', 'jenisUjian'])->findOrFail($jadwal_id);
    echo "Jadwal loaded successfully.\n";
    $peserta = \App\Models\UjianSiswa::with(['siswa.kelas'])->where('jadwal_id', $jadwal_id)->get();
    echo "Peserta loaded successfully.\n";
    echo json_encode(['success' => true]);
} catch (\Exception $e) {
    echo "Exception: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine() . "\n";
}
