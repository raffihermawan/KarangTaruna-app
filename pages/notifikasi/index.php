<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';
require_once __DIR__ . '/../../helpers/format-helper.php';

require_login();

$pdo = db();
$stmt = $pdo->query('
    SELECT t.*, k.nama AS kategori_nama
    FROM transaksi t
    JOIN kategori_transaksi k ON k.id = t.kategori_id
    ORDER BY t.tanggal DESC, t.id DESC
    LIMIT 8
');
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Notifikasi';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <div class="card">
        <div class="card-title">Notifikasi Aktivitas</div>
        <?php if (!$transaksi) : ?>
            <div class="alert alert-warning">Belum ada aktivitas terbaru.</div>
        <?php else : ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Aktivitas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transaksi as $trx) : ?>
                        <tr>
                            <td><?php echo format_tanggal($trx['tanggal']); ?></td>
                            <td>
                                <?php echo ucfirst(htmlspecialchars($trx['jenis'], ENT_QUOTES, 'UTF-8')); ?>
                                <?php echo format_rupiah($trx['jumlah']); ?>
                                berhasil dicatat (<?php echo htmlspecialchars($trx['kategori_nama'], ENT_QUOTES, 'UTF-8'); ?>)
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
