<?php
$m = new mysqli('127.0.0.1', 'root', '', 'u763176290_hds');
echo "Tables in u763176290_hds:" . PHP_EOL;
$res = $m->query('SHOW TABLES');
while ($r = $res->fetch_row()) {
    $tbl = $r[0];
    $c = $m->query("SELECT COUNT(*) FROM `{$tbl}`")->fetch_row()[0];
    echo " - {$tbl} ({$c} rows)" . PHP_EOL;
}
