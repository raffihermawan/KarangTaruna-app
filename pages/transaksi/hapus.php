<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';
require_once __DIR__ . '/../../helpers/query-helper.php';

require_role('bendahara');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Metode tidak diizinkan.');
}

if (!validate_csrf($_POST['csrf_token'] ?? null)) {
    set_flash('danger', 'Token keamanan tidak valid.');
    header('Location: ' . base_url('pages/transaksi/index.php'));
    exit;
}

$pdo = db();
$id = (int) ($_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM transaksi WHERE id = :id');
$stmt->execute(['id' => $id]);
$transaksi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaksi) {
    set_flash('danger', 'Transaksi tidak ditemukan.');
    header('Location: ' . base_url('pages/transaksi/index.php'));
    exit;
}

if (($transaksi['status'] ?? 'pending') !== 'pending') {
    set_flash('danger', 'Transaksi yang sudah diproses tidak dapat dihapus.');
    header('Location: ' . base_url('pages/transaksi/detail.php?id=' . $id));
    exit;
}

$buktiStmt = $pdo->prepare('SELECT * FROM bukti_transaksi WHERE transaksi_id = :id');
$buktiStmt->execute(['id' => $id]);
$bukti = $buktiStmt->fetch(PDO::FETCH_ASSOC);

$pdo->beginTransaction();
try {
    if ($bukti) {
        $path = __DIR__ . '/../../' . $bukti['path_file'];
        if (is_file($path)) {
            unlink($path);
        }
    }

    $delete = $pdo->prepare('DELETE FROM transaksi WHERE id = :id');
    $delete->execute(['id' => $id]);

    $pdo->commit();
    set_flash('success', 'Transaksi berhasil dihapus.');
} catch (Exception $e) {
    $pdo->rollBack();
    set_flash('danger', $e->getMessage());
}

header('Location: ' . base_url('pages/transaksi/index.php'));
exit;
