<?php
$lines = file('../storage/logs/laravel.log');
$errors = [];
for($i=count($lines)-1; $i>=0; $i--) {
    if(strpos($lines[$i], 'local.ERROR:') !== false) {
        $errors[] = $lines[$i];
        if(count($errors) >= 2) break;
    }
}
echo implode("\n", $errors);
