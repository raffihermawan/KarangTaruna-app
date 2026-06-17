<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';
require_once __DIR__ . '/../../helpers/format-helper.php';

require_login();

$pdo = db();
$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM kegiatan WHERE id = :id');
$stmt->execute(['id' => $id]);
$kegiatan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kegiatan) {
    http_response_code(404);
    exit('Kegiatan tidak ditemukan.');
}

$transaksiStmt = $pdo->prepare('
    SELECT t.*, k.nama AS kategori_nama
    FROM transaksi t
    JOIN kategori_transaksi k ON k.id = t.kategori_id
    WHERE t.kegiatan_id = :id
    ORDER BY t.tanggal DESC
');
$transaksiStmt->execute(['id' => $id]);
$transaksi = $transaksiStmt->fetchAll(PDO::FETCH_ASSOC);

$totalPemasukan = 0;
$totalPengeluaran = 0;
foreach ($transaksi as $trx) {
    if ($trx['jenis'] === 'pemasukan') {
        $totalPemasukan += (float) $trx['jumlah'];
    } else {
        $totalPengeluaran += (float) $trx['jumlah'];
    }
}

$role = current_user()['role'] ?? '';
$page_title = 'Detail Kegiatan';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <?php include __DIR__ . '/../../includes/alert.php'; ?>
    <div class="card">
        <div class="card-title">Detail Kegiatan</div>
        <table class="table">
            <tr><th>Nama</th><td><?php echo htmlspecialchars($kegiatan['nama'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
            <tr><th>Divisi</th><td><?php echo htmlspecialchars(str_replace('_', ' ', $kegiatan['divisi']), ENT_QUOTES, 'UTF-8'); ?></td></tr>
            <tr><th>Periode</th><td><?php echo format_tanggal($kegiatan['tanggal_mulai']); ?> - <?php echo format_tanggal($kegiatan['tanggal_selesai']); ?></td></tr>
            <tr><th>Status</th><td><?php echo htmlspecialchars($kegiatan['status'], ENT_QUOTES, 'UTF-8'); ?></td></tr>
            <tr><th>Anggaran</th><td><?php echo format_rupiah($kegiatan['anggaran']); ?></td></tr>
            <tr><th>Deskripsi</th><td><?php echo nl2br(htmlspecialchars($kegiatan['deskripsi'], ENT_QUOTES, 'UTF-8')); ?></td></tr>
        </table>
    </div>

    <div class="card">
        <div class="card-title">Rekapitulasi Keuangan</div>
        <div class="stats-grid">
            <div class="card">
                <div class="card-title">Total Pemasukan</div>
                <div class="stat-value"><?php echo format_rupiah($totalPemasukan); ?></div>
            </div>
            <div class="card">
                <div class="card-title">Total Pengeluaran</div>
                <div class="stat-value"><?php echo format_rupiah($totalPengeluaran); ?></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-title">Transaksi Terkait</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th>Kategori</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$transaksi) : ?>
                    <tr><td colspan="4">Belum ada transaksi terkait.</td></tr>
                <?php else : ?>
                    <?php foreach ($transaksi as $trx) : ?>
                        <tr>
                            <td><?php echo format_tanggal($trx['tanggal']); ?></td>
                            <td><?php echo htmlspecialchars($trx['jenis'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($trx['kategori_nama'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo format_rupiah($trx['jumlah']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="actions">
            <a href="<?php echo base_url('pages/kegiatan/index.php'); ?>" class="btn">Kembali</a>
            <?php if ($role === 'bendahara') : ?>
                <a href="<?php echo base_url('pages/kegiatan/edit.php?id=' . $id); ?>" class="btn btn-primary">Edit</a>
                <form method="post" action="<?php echo base_url('pages/kegiatan/hapus.php'); ?>"
                      data-confirm="Hapus kegiatan ini?"
                      data-confirm-title="Hapus Kegiatan"
                      data-confirm-ok="Ya, hapus"
                      data-confirm-icon="warning">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
