<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';

require_role(['bendahara', 'ketua']);

$pdo = db();
$kategori = $pdo->query('SELECT * FROM kategori_transaksi ORDER BY nama ASC')->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Kategori Transaksi';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <?php include __DIR__ . '/../../includes/alert.php'; ?>
    <div class="card">
        <div class="card-title">Kategori Transaksi</div>
        <div class="actions">
            <a href="<?php echo base_url('pages/kategori/tambah.php'); ?>" class="btn btn-success">Tambah Kategori</a>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Jenis</th>
                    <th>Divisi</th>
                    <th>Deskripsi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$kategori) : ?>
                    <tr><td colspan="5">Belum ada kategori.</td></tr>
                <?php else : ?>
                    <?php foreach ($kategori as $row) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($row['jenis'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars(str_replace('_', ' ', $row['divisi']), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($row['deskripsi'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="actions">
                                <a href="<?php echo base_url('pages/kategori/edit.php?id=' . $row['id']); ?>" class="btn btn-primary">Edit</a>
                                <form method="post" action="<?php echo base_url('pages/kategori/hapus.php'); ?>"
                                      data-confirm="Hapus kategori ini?"
                                      data-confirm-title="Hapus Kategori"
                                      data-confirm-ok="Ya, hapus"
                                      data-confirm-icon="warning">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
