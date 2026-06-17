<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth-helper.php';
require_once __DIR__ . '/../helpers/query-helper.php';

require_login();

header('Content-Type: application/json');

$pdo = db();
$start = new DateTime('first day of this month');
$start->modify('-11 months');
$statusFilter = table_has_column($pdo, 'transaksi', 'status')
    ? 'COALESCE(status, "approved") = "approved"'
    : '1 = 1';

$labels = [];
$pemasukan = [];
$pengeluaran = [];

$lookup = [];
$stmt = $pdo->prepare('
    SELECT DATE_FORMAT(tanggal, "%Y-%m") AS ym,
           SUM(CASE WHEN jenis = "pemasukan" THEN jumlah ELSE 0 END) AS pemasukan,
           SUM(CASE WHEN jenis = "pengeluaran" THEN jumlah ELSE 0 END) AS pengeluaran
    FROM transaksi
        WHERE ' . $statusFilter . '
      AND tanggal >= :start
    GROUP BY ym
    ORDER BY ym ASC
');
$stmt->execute(['start' => $start->format('Y-m-01')]);
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $lookup[$row['ym']] = $row;
}

for ($i = 0; $i < 12; $i++) {
    $ym = $start->format('Y-m');
    $labels[] = $start->format('M Y');
    $row = $lookup[$ym] ?? ['pemasukan' => 0, 'pengeluaran' => 0];
    $pemasukan[] = (float) ($row['pemasukan'] ?? 0);
    $pengeluaran[] = (float) ($row['pengeluaran'] ?? 0);
    $start->modify('+1 month');
}

echo json_encode([
    'labels' => $labels,
    'pemasukan' => $pemasukan,
    'pengeluaran' => $pengeluaran,
]);
