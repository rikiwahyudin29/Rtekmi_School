<?php
$dir = __DIR__ . '/resources/js/Pages/Profile/Partials/';
$files = scandir($dir);
foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'vue') {
        $path = $dir . $file;
        $content = file_get_contents($path);
        
        // Replace typical Breeze colors with Siakad colors
        $content = str_replace('text-indigo-600', 'text-primary-600', $content);
        $content = str_replace('text-indigo-700', 'text-primary-700', $content);
        $content = str_replace('bg-indigo-50', 'bg-primary-50', $content);
        $content = str_replace('hover:file:bg-indigo-100', 'hover:file:bg-primary-100', $content);
        $content = str_replace('focus:ring-indigo-500', 'focus:ring-primary-500', $content);
        $content = str_replace('focus:border-indigo-500', 'focus:border-primary-500', $content);
        $content = str_replace('border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm', 'border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-xl shadow-sm', $content);
        
        file_put_contents($path, $content);
    }
}
echo "Done replacing colors in Partials.";
