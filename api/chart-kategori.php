<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth-helper.php';
require_once __DIR__ . '/../helpers/query-helper.php';

require_login();

header('Content-Type: application/json');

$pdo = db();
$statusFilter = table_has_column($pdo, 'transaksi', 'status')
        ? 'COALESCE(t.status, "approved") = "approved"'
        : '1 = 1';
$stmt = $pdo->query('
    SELECT kt.divisi, SUM(t.jumlah) AS total
    FROM transaksi t
    JOIN kategori_transaksi kt ON kt.id = t.kategori_id
        WHERE ' . $statusFilter . '
      AND t.jenis = "pengeluaran"
    GROUP BY kt.divisi
    ORDER BY total DESC
');

$labels = [];
$values = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $label = str_replace('_', ' ', $row['divisi']);
    $labels[] = ucwords($label);
    $values[] = (float) $row['total'];
}

echo json_encode([
    'labels' => $labels,
    'values' => $values,
]);
