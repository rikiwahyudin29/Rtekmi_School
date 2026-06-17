<?php
$output = shell_exec('git status');
file_put_contents('git_status.txt', $output);
$output2 = shell_exec('git log -n 1 --decorate');
file_put_contents('git_log_decorate.txt', $output2);
