<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';
require_once __DIR__ . '/../../helpers/format-helper.php';

require_login();

$pdo = db();
$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('
    SELECT t.*, k.nama AS kategori_nama, keg.nama AS kegiatan_nama, u.nama AS approved_by_name
    FROM transaksi t
    JOIN kategori_transaksi k ON k.id = t.kategori_id
    LEFT JOIN kegiatan keg ON keg.id = t.kegiatan_id
    LEFT JOIN users u ON u.id = t.approved_by
    WHERE t.id = :id
');
$stmt->execute(['id' => $id]);
$transaksi = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaksi) {
    http_response_code(404);
    exit('Transaksi tidak ditemukan.');
}

$buktiStmt = $pdo->prepare('SELECT * FROM bukti_transaksi WHERE transaksi_id = :id LIMIT 1');
$buktiStmt->execute(['id' => $id]);
$bukti = $buktiStmt->fetch(PDO::FETCH_ASSOC);

$status = $transaksi['status'] ?? 'approved';
$role = current_user()['role'] ?? '';
$page_title = 'Detail Transaksi';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <?php include __DIR__ . '/../../includes/alert.php'; ?>
    <div class="card">
        <div class="card-title">Detail Transaksi</div>
        <table class="table">
            <tr><th>Tanggal</th><td><?php echo format_tanggal($transaksi['tanggal']); ?></td></tr>
            <tr><th>Jenis</th><td><?php echo htmlspecialchars($transaksi['jenis'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
            <tr><th>Kategori</th><td><?php echo htmlspecialchars($transaksi['kategori_nama'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
            <tr><th>Kegiatan</th><td><?php echo htmlspecialchars($transaksi['kegiatan_nama'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td></tr>
            <tr><th>PIC</th><td><?php echo htmlspecialchars($transaksi['pic'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
            <tr><th>Jumlah</th><td><?php echo format_rupiah($transaksi['jumlah']); ?></td></tr>
            <tr><th>Status</th><td>
                <span class="badge <?php echo $status === 'approved' ? 'badge-success' : ($status === 'rejected' ? 'badge-danger' : 'badge-warning'); ?>">
                    <?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>
                </span>
            </td></tr>
            <?php if (!empty($transaksi['approved_by_name'])) : ?>
                <tr><th>Diproses Oleh</th><td><?php echo htmlspecialchars($transaksi['approved_by_name'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($transaksi['approved_at'])) : ?>
                <tr><th>Waktu Approval</th><td><?php echo htmlspecialchars(date('d M Y H:i', strtotime($transaksi['approved_at'])), ENT_QUOTES, 'UTF-8'); ?></td></tr>
            <?php endif; ?>
            <?php if ($status === 'rejected' && !empty($transaksi['catatan_penolakan'])) : ?>
                <tr><th>Catatan Penolakan</th><td><?php echo nl2br(htmlspecialchars($transaksi['catatan_penolakan'], ENT_QUOTES, 'UTF-8')); ?></td></tr>
            <?php endif; ?>
            <tr><th>Keterangan</th><td><?php echo nl2br(htmlspecialchars($transaksi['keterangan'], ENT_QUOTES, 'UTF-8')); ?></td></tr>
        </table>
        <?php if ($bukti) : ?>
            <div class="card-title">Bukti Transaksi</div>
            <?php
                $fileUrl = base_url($bukti['path_file']);
                $isImage = in_array($bukti['tipe_file'], ['image/jpeg', 'image/png'], true);
            ?>
            <?php if ($isImage) : ?>
                <img src="<?php echo $fileUrl; ?>" alt="Bukti" style="max-width: 100%; border-radius: 8px;">
            <?php else : ?>
                <a href="<?php echo $fileUrl; ?>" class="btn btn-primary" target="_blank" rel="noopener">Lihat Dokumen</a>
            <?php endif; ?>
        <?php endif; ?>
        <div class="actions">
            <a href="<?php echo base_url('pages/transaksi/index.php'); ?>" class="btn">Kembali</a>
            <?php if ($role === 'bendahara' && $status === 'pending') : ?>
                <a href="<?php echo base_url('pages/transaksi/edit.php?id=' . $id); ?>" class="btn btn-primary">Edit</a>
                <form method="post" action="<?php echo base_url('pages/transaksi/hapus.php'); ?>"
                      data-confirm="Hapus transaksi ini?"
                      data-confirm-title="Hapus Transaksi"
                      data-confirm-ok="Ya, hapus"
                      data-confirm-icon="warning">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            <?php elseif ($role === 'ketua' && $status === 'pending') : ?>
                <a href="<?php echo base_url('pages/transaksi/persetujuan.php'); ?>" class="btn btn-primary">Ke Persetujuan</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
