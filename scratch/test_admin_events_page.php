<?php
session_start();
$_SESSION['admin_authenticated'] = true;
$_SESSION['admin_email'] = 'admintest@gmail.com';
$_SESSION['admin_name'] = 'System Administrator';
$_GET['page'] = 'events';

ob_start();
require __DIR__ . '/../Admin/index.php';
$output = ob_get_clean();

echo "Rendered bytes: " . strlen($output) . "\n";
echo "Contains admin-event-card: " . (strpos($output, 'admin-event-card') !== false ? 'YES' : 'NO') . "\n";
echo "Contains stat-tile-icon: " . (strpos($output, 'stat-tile-icon') !== false ? 'YES' : 'NO') . "\n";
echo "Contains admin-eyebrow-pill: " . (strpos($output, 'admin-eyebrow-pill') !== false ? 'YES' : 'NO') . "\n";
echo "Contains admin-event-pill: " . (strpos($output, 'admin-event-pill') !== false ? 'YES' : 'NO') . "\n";
echo "Contains admin-status-pill: " . (strpos($output, 'admin-status-pill') !== false ? 'YES' : 'NO') . "\n";
