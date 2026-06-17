<?php
$output = shell_exec('git log -p -n 5');
file_put_contents('git_log_output.txt', $output);
