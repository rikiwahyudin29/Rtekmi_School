<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Update existing records to match the new status schema
// 1. Old Selesai (status=1) -> New Selesai (status=2)
$affected1 = DB::table('ujian_siswa')->where('status', 1)->update(['status' => 2]);
echo "Updated $affected1 records from status 1 to 2 (Selesai)<br>";

// 2. Old Sedang Mengerjakan (status=0 and start_at IS NOT NULL) -> New Sedang Mengerjakan (status=1)
$affected2 = DB::table('ujian_siswa')->where('status', '0')->whereNotNull('start_at')->update(['status' => 1]);
echo "Updated $affected2 records from status 0 to 1 (Sedang Mengerjakan)<br>";

echo "DONE.";
