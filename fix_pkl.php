<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tahun_ajaran_aktif = \App\Models\TahunAjaran::where('status', 'Aktif')->first();
$semester_int = ($tahun_ajaran_aktif && $tahun_ajaran_aktif->semester === 'Genap') ? 2 : 1;

$updated = \App\Models\RaporPkl::where('semester', 1)->update(['semester' => $semester_int]);
echo "Updated $updated rows to semester $semester_int\n";
