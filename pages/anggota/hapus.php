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
    header('Location: ' . base_url('pages/anggota/index.php'));
    exit;
}

$pdo = db();
$id = (int) ($_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT foto FROM anggota WHERE id = :id');
$stmt->execute(['id' => $id]);
$anggota = $stmt->fetch(PDO::FETCH_ASSOC);

if ($anggota && $anggota['foto']) {
    $path = __DIR__ . '/../../' . $anggota['foto'];
    if (is_file($path)) {
        unlink($path);
    }
}

$delete = $pdo->prepare('DELETE FROM anggota WHERE id = :id');
$delete->execute(['id' => $id]);

set_flash('success', 'Anggota berhasil dihapus.');
header('Location: ' . base_url('pages/anggota/index.php'));
exit;
