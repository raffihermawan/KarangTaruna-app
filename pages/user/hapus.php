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
    header('Location: ' . base_url('pages/user/index.php'));
    exit;
}

$pdo = db();
$id = (int) ($_POST['id'] ?? 0);

$stmt = $pdo->prepare('UPDATE users SET status = :status WHERE id = :id');
$stmt->execute(['status' => 'nonaktif', 'id' => $id]);

set_flash('success', 'User berhasil dinonaktifkan.');
header('Location: ' . base_url('pages/user/index.php'));
exit;
