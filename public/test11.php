<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$peserta = \App\Models\UjianSiswa::with(['siswa', 'siswa.kelas'])
    ->where('jadwal_id', 42)
    ->get()
    ->map(function ($u) {
        return [
            'ujian_siswa_id' => $u->id,
            'siswa_id_fk' => $u->siswa_id,
            'id' => $u->siswa->id ?? null,
            'nama' => $u->siswa->nama_lengkap ?? '-',
            'nis' => $u->siswa->nis ?? '-',
            'nisn' => $u->siswa->nisn ?? '-',
            'kelas' => $u->siswa->kelas->nama_kelas ?? null,
        ];
    });

echo "PESERTA JADWAL 42:\n";
echo json_encode(['data' => $peserta]);
