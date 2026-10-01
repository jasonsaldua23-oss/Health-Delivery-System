<?php

declare(strict_types=1);

/**
 * Serves patient / infant photos: Patients/photo.php?f=patient_xxx.jpg
 *
 * The file in Patients/uploads is used when present. If a deployment removed it, the database copy
 * (patient_photo_store) is served and written back to disk. Photos found on disk but not yet in the
 * database are copied in, so they survive the next deployment too.
 */

require_once __DIR__ . '/../shared/bootstrap.php';
require_once __DIR__ . '/../shared/database.php';

// Patient photos are only for signed-in staff, admins and patients
$signedIn = !empty($_SESSION['staff_authenticated'])
    || !empty($_SESSION['admin_authenticated'])
    || !empty($_SESSION['patient_id']);
if (!$signedIn) {
    http_response_code(403);
    exit;
}
// Photos never change once taken; release the session lock so a page can load many at once
session_write_close();

$fileName = photo_file_name((string) ($_GET['f'] ?? ''));
if ($fileName === '') {
    http_response_code(404);
    exit;
}

$diskPath = photo_upload_disk_path($fileName);
$mime = photo_mime_for($fileName);
$data = null;

if (is_file($diskPath)) {
    $data = file_get_contents($diskPath);
    if ($data !== false && $data !== '' && !photo_in_store($fileName)) {
        save_photo_to_store($fileName, $data);
    }
} else {
    $stored = fetch_photo_from_store($fileName);
    if ($stored !== null) {
        $data = (string) $stored['data'];
        $mime = (string) ($stored['mime'] ?: $mime);
        if (is_dir(dirname($diskPath))) {
            @file_put_contents($diskPath, $data);
        }
    }
}

if ($data === null || $data === false || $data === '') {
    http_response_code(404);
    exit;
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . strlen($data));
header('Cache-Control: private, max-age=86400');
header('X-Content-Type-Options: nosniff');
echo $data;
