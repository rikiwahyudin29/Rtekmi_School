<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$schema = Illuminate\Support\Facades\Schema::getColumnListing('tbl_guru');
print_r($schema);
