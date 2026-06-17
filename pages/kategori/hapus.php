<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';

require_role(['bendahara', 'ketua']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

if (!validate_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('danger', 'Token keamanan tidak valid.');
    header('Location: ' . base_url('pages/kategori/index.php'));
    exit;
}

$pdo = db();
$id = (int) ($_POST['id'] ?? 0);

$countStmt = $pdo->prepare('SELECT COUNT(*) FROM transaksi WHERE kategori_id = :id');
$countStmt->execute(['id' => $id]);
if ($countStmt->fetchColumn() > 0) {
    set_flash('danger', 'Kategori masih digunakan dalam transaksi.');
    header('Location: ' . base_url('pages/kategori/index.php'));
    exit;
}

$delete = $pdo->prepare('DELETE FROM kategori_transaksi WHERE id = :id');
$delete->execute(['id' => $id]);

set_flash('success', 'Kategori berhasil dihapus.');
header('Location: ' . base_url('pages/kategori/index.php'));
exit;
