<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/query-helper.php';
require_once __DIR__ . '/../helpers/auth-helper.php';

require_login();

header('Content-Type: application/json');

$pdo = db();
$saldo = get_saldo($pdo);
$bulan = (int) date('m');
$tahun = (int) date('Y');
$statusFilter = table_has_column($pdo, 'transaksi', 'status')
    ? 'COALESCE(status, "approved") = "approved"'
    : '1 = 1';

$stmt = $pdo->prepare('
    SELECT
        SUM(CASE WHEN jenis = "pemasukan" THEN jumlah ELSE 0 END) AS pemasukan,
        SUM(CASE WHEN jenis = "pengeluaran" THEN jumlah ELSE 0 END) AS pengeluaran
    FROM transaksi
    WHERE ' . $statusFilter . '
      AND MONTH(tanggal) = :bulan AND YEAR(tanggal) = :tahun
');
$stmt->execute(['bulan' => $bulan, 'tahun' => $tahun]);
$data = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['pemasukan' => 0, 'pengeluaran' => 0];

$kegiatanAktif = (int) $pdo->query("SELECT COUNT(*) FROM kegiatan WHERE status = 'aktif'")->fetchColumn();
$lastUpdate = $pdo->query('SELECT MAX(updated_at) FROM transaksi WHERE ' . $statusFilter)->fetchColumn();

echo json_encode([
    'saldo' => $saldo,
    'pemasukan' => (float) ($data['pemasukan'] ?? 0),
    'pengeluaran' => (float) ($data['pengeluaran'] ?? 0),
    'kegiatan_aktif' => $kegiatanAktif,
    'updated_at' => $lastUpdate,
]);
