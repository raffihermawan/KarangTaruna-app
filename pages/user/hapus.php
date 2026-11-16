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
$currentUserId = (int) (current_user()['id'] ?? 0);

if ($id <= 0) {
    set_flash('danger', 'User tidak valid.');
    header('Location: ' . base_url('pages/user/index.php'));
    exit;
}

if ($id === $currentUserId) {
    set_flash('danger', 'Tidak bisa menghapus akun yang sedang digunakan.');
    header('Location: ' . base_url('pages/user/index.php'));
    exit;
}

$logged_in_user_id = (int) (current_user()['id'] ?? 0);
$logged_in_role = current_user()['role'] ?? null;
$target_user_id = (int) ($_REQUEST['id'] ?? 0);

if ($target_user_id) {
    if ($target_user_id === $logged_in_user_id) {
        set_flash('danger', 'Anda tidak bisa menghapus akun sendiri!');
        header('Location: ' . base_url('pages/user/index.php'));
        exit;
    }

    $query_target = $pdo->prepare('SELECT role FROM users WHERE id = :id');
    $query_target->execute(['id' => $target_user_id]);
    $data_target = $query_target->fetch(PDO::FETCH_ASSOC);
    $target_role = $data_target['role'] ?? '';

    if ($logged_in_role === $target_role) {
        set_flash('danger', 'Akses Ditolak: Anda tidak dapat menghapus user dengan role yang sama!');
        header('Location: ' . base_url('pages/user/index.php'));
        exit;
    }
}

$stmt = $pdo->prepare('SELECT foto FROM users WHERE id = :id');
$stmt->execute(['id' => $id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    set_flash('danger', 'User tidak ditemukan.');
    header('Location: ' . base_url('pages/user/index.php'));
    exit;
}

if (!empty($user['foto'])) {
    $photoPath = __DIR__ . '/../../' . ltrim($user['foto'], '/');
    if (is_file($photoPath)) {
        unlink($photoPath);
    }
}

$stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
$stmt->execute(['id' => $id]);

set_flash('success', 'User berhasil dihapus.');
header('Location: ' . base_url('pages/user/index.php'));
exit;
