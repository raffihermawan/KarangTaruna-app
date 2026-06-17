<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';
require_once __DIR__ . '/../../helpers/format-helper.php';

require_login();

$pdo = db();
$kegiatan = $pdo->query('SELECT * FROM kegiatan ORDER BY tanggal_mulai DESC')->fetchAll(PDO::FETCH_ASSOC);
$role = current_user()['role'] ?? '';

$page_title = 'Kegiatan';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <?php include __DIR__ . '/../../includes/alert.php'; ?>
    <div class="card">
        <div class="card-title">Daftar Kegiatan</div>
        <?php if ($role === 'bendahara') : ?>
            <div class="actions">
                <a href="<?php echo base_url('pages/kegiatan/tambah.php'); ?>" class="btn btn-success">Tambah Kegiatan</a>
            </div>
        <?php endif; ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Divisi</th>
                    <th>Periode</th>
                    <th>Status</th>
                    <th>Anggaran</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$kegiatan) : ?>
                    <tr><td colspan="6">Belum ada kegiatan.</td></tr>
                <?php else : ?>
                    <?php foreach ($kegiatan as $keg) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($keg['nama'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars(str_replace('_', ' ', $keg['divisi']), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo format_tanggal($keg['tanggal_mulai']); ?> - <?php echo format_tanggal($keg['tanggal_selesai']); ?></td>
                            <td><?php echo htmlspecialchars($keg['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo format_rupiah($keg['anggaran']); ?></td>
                            <td class="actions">
                                <a href="<?php echo base_url('pages/kegiatan/detail.php?id=' . $keg['id']); ?>" class="btn">Detail</a>
                                <?php if ($role === 'bendahara') : ?>
                                    <a href="<?php echo base_url('pages/kegiatan/edit.php?id=' . $keg['id']); ?>" class="btn btn-primary">Edit</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
