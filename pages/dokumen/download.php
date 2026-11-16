<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';

require_login();

$pdo = db();
$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM dokumen WHERE id = :id');
$stmt->execute(['id' => $id]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$doc) {
    set_flash('danger', 'Dokumen tidak ditemukan.');
    header('Location: ' . base_url('pages/dokumen/index.php'));
    exit;
}

$filePath = __DIR__ . '/../../uploads/dokumen/' . $doc['nama_file'];

if (!is_file($filePath)) {
    set_flash('danger', 'File tidak ditemukan di server.');
    header('Location: ' . base_url('pages/dokumen/index.php'));
    exit;
}

// Tentukan nama file unduhan yang user-friendly
$ext = pathinfo($doc['nama_file'], PATHINFO_EXTENSION);
$downloadName = preg_replace('/[^a-zA-Z0-9_\-\. ]/', '_', $doc['nama_dokumen']) . '.' . $ext;

// Set header untuk force-download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));

readfile($filePath);
exit;
