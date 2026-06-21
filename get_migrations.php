<?php
$files = glob(__DIR__.'/database/migrations/2026_{05_28,05_29,06_*}*.php', GLOB_BRACE);
$output = "";
foreach ($files as $file) {
    $output .= "\n\n--- " . basename($file) . " ---\n";
    $output .= file_get_contents($file);
}
file_put_contents(__DIR__.'/migration_dump.txt', $output);
echo "Done";
