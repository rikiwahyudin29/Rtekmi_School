<?php
$output = shell_exec('git diff c678a85e^ c678a85e -- resources/js/Layouts/DashboardLayout.vue');
file_put_contents('git_diff_c678.txt', $output);
