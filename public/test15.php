<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/admin/cbt/jadwal-ujian/42/print-nilai', 'GET');
$response = $kernel->handle($request);
echo $response->getStatusCode();
echo "\n";
echo $response->getContent();
