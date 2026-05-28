<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    $controller = new \App\Http\Controllers\Admin\CBT\DetailJadwalController();
    $result = $controller->printNilai(42);
    echo "SUCCESS\n";
} catch (\Exception $e) {
    echo "ERROR:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
