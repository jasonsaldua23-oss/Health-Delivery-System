<?php
require_once __DIR__ . '/../shared/database.php';

$admin = fetch_admin_account_by_email('admintest@gmail.com');
echo "Admin found: " . json_encode($admin) . "\n";

$updateData = [
    'admin_name' => 'Admin User Updated',
    'office_name' => 'Bacolod City Health Department - Central Administration',
    'email' => 'admintest@gmail.com',
    'contact_number' => '09171234567',
    'recovery_email' => 'admin.recovery@gmail.com',
    'password' => '',
];

$res = update_admin_account_details((int)$admin['id'], $updateData);
echo "Update admin result: " . ($res ? "SUCCESS" : "FAILED") . "\n";
