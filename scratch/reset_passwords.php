<?php
require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

$db = db();
$h = password_hash('password123', PASSWORD_DEFAULT);
$db->query("UPDATE staff_accounts SET password_hash = '{$h}'");
$db->query("UPDATE admin_accounts SET password_hash = '{$h}'");
echo "Staff and Admin passwords set to password123\n";
