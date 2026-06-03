<?php
$files = [
    __DIR__.'/app/Http/Controllers/Admin/Keuangan/PembayaranController.php',
    __DIR__.'/app/Http/Controllers/Admin/Keuangan/PengeluaranController.php',
    __DIR__.'/app/Http/Controllers/Admin/Keuangan/NotifTagihanController.php'
];

foreach ($files as $file) {
    $content = file_get_contents($file);
    // Also remove the comma before updated_at if it's there
    $content = preg_replace("/,\s*'updated_at'\s*=>\s*now\(\)/", "", $content);
    file_put_contents($file, $content);
}
echo "Done";
