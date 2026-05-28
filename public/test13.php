<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$query = \App\Models\JadwalUjian::with(['draftUjian', 'guru', 'jenisUjian', 'tahunAjaran', 'ruangan', 'kelas'])->orderBy('id', 'desc');
$jadwalUjian = $query->paginate(10);
$jadwalUjian->getCollection()->transform(function ($jadwal) {
    $kelas_concat = \Illuminate\Support\Facades\DB::table('ujian_siswa as us')
                            ->join('tbl_siswa as ms', 'ms.id', '=', 'us.siswa_id')
                            ->join('tbl_kelas as mk', 'mk.id', '=', 'ms.kelas_id')
                            ->where('us.jadwal_id', $jadwal->id)
                            ->selectRaw("GROUP_CONCAT(DISTINCT mk.nama_kelas SEPARATOR ', ') as kelas")
                            ->value('kelas');
    $jadwal->kelas_concat = $kelas_concat;
    return $jadwal;
});

echo json_encode($jadwalUjian);
