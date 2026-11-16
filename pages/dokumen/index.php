<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';
require_once __DIR__ . '/../../helpers/format-helper.php';

require_login();

$pdo = db();
$role = current_user()['role'] ?? '';

$stmt = $pdo->query('
    SELECT d.*, u.nama AS uploader_nama
    FROM dokumen d
    JOIN users u ON u.id = d.uploaded_by
    ORDER BY d.tgl_upload DESC
');
$dokumen = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Dokumen/Berkas';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <?php include __DIR__ . '/../../includes/alert.php'; ?>
    <div class="card">
        <div class="card-title">Daftar Dokumen / Berkas Organisasi</div>
        <?php if (in_array($role, ['ketua', 'bendahara'], true)) : ?>
            <div class="actions dokumen-actions">
                <a href="<?php echo base_url('pages/dokumen/create.php'); ?>" class="btn btn-success">Tambah Berkas</a>
            </div>
        <?php endif; ?>
        <div class="table-wrap dokumen-table-wrap">
        <table class="table dokumen-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Dokumen</th>
                    <th>Jenis</th>
                    <th>Diupload Oleh</th>
                    <th>Tanggal Upload</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$dokumen) : ?>
                    <tr><td colspan="6">Belum ada dokumen.</td></tr>
                <?php else : ?>
                    <?php $no = 1; ?>
                    <?php foreach ($dokumen as $doc) : ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($doc['nama_dokumen'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <span class="badge badge-primary">
                                    <?php echo htmlspecialchars($doc['jenis_dokumen'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($doc['uploader_nama'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo date('d M Y H:i', strtotime($doc['tgl_upload'])); ?></td>
                            <td class="actions">
                                <a href="<?php echo base_url('pages/dokumen/download.php?id=' . $doc['id']); ?>" class="btn btn-primary">Unduh</a>
                                <?php if (in_array($role, ['ketua', 'bendahara'], true)) : ?>
                                    <form method="post" action="<?php echo base_url('pages/dokumen/hapus.php'); ?>" style="display:inline;" data-confirm="Yakin ingin menghapus dokumen ini?" data-confirm-title="Hapus Dokumen" data-confirm-ok="Hapus" data-confirm-icon="warning">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="id" value="<?php echo $doc['id']; ?>">
                                        <button type="submit" class="btn btn-danger">Hapus</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
