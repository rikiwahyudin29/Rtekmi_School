<?php
$output = shell_exec('git log -p -n 3');
file_put_contents('git_show_d942a3d.txt', $output);
