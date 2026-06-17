<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';

require_role(['bendahara', 'ketua']);

$pdo = db();
$id = (int) ($_GET['id'] ?? 0);
$token = $_GET['csrf_token'] ?? null;

if ($id <= 0 || !validate_csrf($token)) {
    set_flash('danger', 'Permintaan hapus foto tidak valid.');
    header('Location: ' . base_url('pages/anggota/index.php'));
    exit;
}

$stmt = $pdo->prepare('SELECT foto FROM anggota WHERE id = :id');
$stmt->execute(['id' => $id]);
$anggota = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$anggota) {
    set_flash('danger', 'Anggota tidak ditemukan.');
    header('Location: ' . base_url('pages/anggota/index.php'));
    exit;
}

$fotoValue = trim((string) ($anggota['foto'] ?? ''));
if ($fotoValue !== '') {
    $relativePath = str_replace('\\', '/', ltrim($fotoValue, '/\\'));
    $absolutePath = __DIR__ . '/../../' . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

    if (is_file($absolutePath)) {
        @unlink($absolutePath);
    }

    $updateStmt = $pdo->prepare('UPDATE anggota SET foto = NULL WHERE id = :id');
    $updateStmt->execute(['id' => $id]);

    set_flash('success', 'Foto profil anggota berhasil dihapus.');
} else {
    set_flash('warning', 'Anggota tidak memiliki foto profil.');
}

header('Location: ' . base_url('pages/anggota/edit.php?id=' . $id));
exit;
