<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';

require_role(['bendahara', 'ketua']);

$pdo = db();
$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$where = [];
$params = [];

if ($search !== '') {
    $where[] = '(nama LIKE :search OR nik LIKE :search OR jabatan LIKE :search OR no_hp LIKE :search OR email LIKE :search)';
    $params['search'] = '%' . $search . '%';
}

if (in_array($statusFilter, ['aktif', 'nonaktif'], true)) {
    $where[] = 'status = :status';
    $params['status'] = $statusFilter;
}

$sql = 'SELECT * FROM anggota';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY nama ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$anggota = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmtAktif = $pdo->query("SELECT COUNT(*) FROM anggota WHERE status = 'aktif'");
$jumlahAktif = (int) $stmtAktif->fetchColumn();

$page_title = 'Anggota';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <?php include __DIR__ . '/../../includes/alert.php'; ?>
    <div class="card">
        <div class="card-title">Data Anggota</div>
        <div class="card-meta" style="margin-bottom: 16px;">
            <strong>Jumlah Anggota Aktif:</strong> <?php echo (int) $jumlahAktif; ?>
        </div>
        <form method="get" class="actions" style="gap: 10px; flex-wrap: wrap; margin-bottom: 16px;">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Cari nama, NIK, jabatan, HP, email">
            <select name="status">
                <option value="">Semua Status</option>
                <option value="aktif" <?php echo $statusFilter === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                <option value="nonaktif" <?php echo $statusFilter === 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
            </select>
            <button type="submit" class="btn btn-primary">Cari</button>
            <a href="<?php echo base_url('pages/anggota/index.php'); ?>" class="btn">Reset</a>
        </form>
        <div class="actions">
            <a href="<?php echo base_url('pages/anggota/tambah.php'); ?>" class="btn btn-success">Tambah Anggota</a>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th style="min-width: 280px;">Nama</th>
                    <th>NIK</th>
                    <th>Jabatan</th>
                    <th>Divisi</th>
                    <th>No. HP</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$anggota) : ?>
                    <tr><td colspan="7">Belum ada data anggota.</td></tr>
                <?php else : ?>
                    <?php foreach ($anggota as $row) : ?>
                        <?php
                            $namaUser = (string) ($row['nama'] ?? '');
                            $fotoPath = trim((string) ($row['foto'] ?? ''));
                            $avatarUrl = $fotoPath !== ''
                                ? base_url($fotoPath)
                                : 'https://ui-avatars.com/api/?name=' . urlencode($namaUser) . '&background=0D6EFD&color=fff&size=128&bold=true';
                        ?>
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:12px;min-width:0;">
                                    <img
                                        src="<?php echo htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                        alt="Avatar <?php echo htmlspecialchars($namaUser, ENT_QUOTES, 'UTF-8'); ?>"
                                        style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:1px solid #e2e8f0;flex:0 0 36px;"
                                    >
                                    <div style="min-width:0;">
                                        <span style="font-weight:600;color:#1e293b;display:block;font-size:14px;line-height:1.3;word-break:break-word;">
                                            <?php echo htmlspecialchars($namaUser, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                        <?php if (!empty($row['jabatan'])) : ?>
                                            <span style="font-size:11px;color:#64748b;display:block;margin-top:1px;word-break:break-word;">
                                                <?php echo htmlspecialchars($row['jabatan'], ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($row['nik'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($row['jabatan'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars(str_replace('_', ' ', $row['divisi']), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($row['no_hp'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="actions">
                                <a href="<?php echo base_url('pages/anggota/edit.php?id=' . $row['id']); ?>" class="btn btn-primary">Edit</a>
                                <form method="post" action="<?php echo base_url('pages/anggota/hapus.php'); ?>"
                                      data-confirm="Hapus anggota ini?"
                                      data-confirm-title="Hapus Anggota"
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
