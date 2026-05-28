<?php
$file = __DIR__.'/../app/Http/Controllers/Admin/CBT/DetailJadwalController.php';
$content = file_get_contents($file);
$content = preg_replace('/\$jadwalData\[\'([^\']+)\'\]/', '$jadwalData->$1', $content);
file_put_contents($file, $content);
echo "Replaced successfully!";
