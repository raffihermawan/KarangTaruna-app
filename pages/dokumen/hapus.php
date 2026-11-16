<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';

require_role(['ketua', 'bendahara']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

if (!validate_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('danger', 'Token keamanan tidak valid.');
    header('Location: ' . base_url('pages/dokumen/index.php'));
    exit;
}

$pdo = db();
$id = (int) ($_POST['id'] ?? 0);

// Ambil data dokumen untuk menghapus file fisik
$stmt = $pdo->prepare('SELECT nama_file FROM dokumen WHERE id = :id');
$stmt->execute(['id' => $id]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);

if ($doc) {
    // Hapus file fisik dari server
    $filePath = __DIR__ . '/../../uploads/dokumen/' . $doc['nama_file'];
    if (is_file($filePath)) {
        unlink($filePath);
    }

    // Hapus record dari database
    $stmt = $pdo->prepare('DELETE FROM dokumen WHERE id = :id');
    $stmt->execute(['id' => $id]);

    set_flash('success', 'Dokumen berhasil dihapus.');
} else {
    set_flash('danger', 'Dokumen tidak ditemukan.');
}

header('Location: ' . base_url('pages/dokumen/index.php'));
exit;
