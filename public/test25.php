<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/admin/cbt/jadwal-ujian/42/progress', 'POST', [
    'kelas' => 'all',
    'status' => 'all'
]);
// Bypass CSRF by mocking auth or just using controller directly
try {
    $controller = new \App\Http\Controllers\Admin\CBT\DetailJadwalController();
    $result = $controller->progress($request, 42);
    echo "SUCCESS\n";
    echo substr($result->getContent(), 0, 500);
} catch (\Exception $e) {
    echo "ERROR:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
