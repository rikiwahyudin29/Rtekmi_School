<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $request = Illuminate\Http\Request::create('/admin/cbt/jadwal-ujian', 'GET');
    $controller = new \App\Http\Controllers\Admin\CBT\JadwalUjianController();
    $result = $controller->index($request);
    
    // Inertia::render returns a Response object
    echo "SUCCESS\n";
    $page = $result->toResponse($request)->original;
    // Look at page props
    $props = $page['props'] ?? $page->getData()['page']['props'];
    $jadwal = $props['jadwalUjian']['data'][0] ?? null;
    echo "kelas_concat = " . ($jadwal['kelas_concat'] ?? 'MISSING') . "\n";
    echo "total_siswa = " . ($jadwal['total_siswa'] ?? 'MISSING') . "\n";

} catch (\Exception $e) {
    echo "ERROR:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
