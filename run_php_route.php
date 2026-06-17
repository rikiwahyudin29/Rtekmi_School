<?php
$output = shell_exec('php artisan route:list --path=profile');
file_put_contents('git_show_profile.txt', $output);
