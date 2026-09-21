<?php
$m = @new mysqli('bsns.online', 'u763176290_health_del_sys', 'qfA*ZwVyDzBpz36', 'u763176290_HDS', 3306);
echo $m->connect_error ?: ('CONNECTED! Server: ' . $m->server_info . PHP_EOL);
