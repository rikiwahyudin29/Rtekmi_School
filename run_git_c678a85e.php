<?php
$output = shell_exec('git show c678a85e:routes/web.php | findstr /I "rapor"');
file_put_contents('git_show_c678a85e.txt', $output);
