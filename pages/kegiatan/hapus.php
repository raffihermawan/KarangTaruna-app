<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';

require_role('bendahara');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

if (!validate_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('danger', 'Token keamanan tidak valid.');
    header('Location: ' . base_url('pages/kegiatan/index.php'));
    exit;
}

$pdo = db();
$id = (int) ($_POST['id'] ?? 0);

$stmt = $pdo->prepare('DELETE FROM kegiatan WHERE id = :id');
$stmt->execute(['id' => $id]);

set_flash('success', 'Kegiatan berhasil dihapus.');
header('Location: ' . base_url('pages/kegiatan/index.php'));
exit;
