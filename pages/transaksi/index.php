<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';
require_once __DIR__ . '/../../helpers/format-helper.php';

require_login();

$pdo = db();
$jenis = $_GET['jenis'] ?? '';
$kategori = $_GET['kategori_id'] ?? '';
$kegiatan = $_GET['kegiatan_id'] ?? '';
$tanggalMulai = $_GET['tanggal_mulai'] ?? '';
$tanggalAkhir = $_GET['tanggal_akhir'] ?? '';
$search = trim($_GET['search'] ?? '');

$where = [];
$params = [];

if ($jenis !== '') {
    $where[] = 't.jenis = :jenis';
    $params['jenis'] = $jenis;
}
if ($kategori !== '') {
    $where[] = 't.kategori_id = :kategori';
    $params['kategori'] = $kategori;
}
if ($kegiatan !== '') {
    $where[] = 't.kegiatan_id = :kegiatan';
    $params['kegiatan'] = $kegiatan;
}
if ($tanggalMulai !== '' && $tanggalAkhir !== '') {
    $where[] = 't.tanggal BETWEEN :mulai AND :akhir';
    $params['mulai'] = $tanggalMulai;
    $params['akhir'] = $tanggalAkhir;
}
if ($search !== '') {
    $where[] = 't.keterangan LIKE :search';
    $params['search'] = '%' . $search . '%';
}

$sql = 'SELECT t.*, k.nama AS kategori_nama, keg.nama AS kegiatan_nama
        FROM transaksi t
        JOIN kategori_transaksi k ON k.id = t.kategori_id
        LEFT JOIN kegiatan keg ON keg.id = t.kegiatan_id';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY t.tanggal DESC, t.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

$kategoriList = $pdo->query('SELECT id, nama, jenis FROM kategori_transaksi ORDER BY nama')->fetchAll(PDO::FETCH_ASSOC);
$kegiatanList = $pdo->query('SELECT id, nama FROM kegiatan ORDER BY nama')->fetchAll(PDO::FETCH_ASSOC);
$role = current_user()['role'] ?? '';

$page_title = 'Transaksi';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <?php include __DIR__ . '/../../includes/alert.php'; ?>
    <div class="card">
        <div class="card-title">Filter Transaksi</div>
        <form method="get" class="filters">
            <label>
                Jenis
                <select name="jenis">
                    <option value="">Semua</option>
                    <option value="pemasukan" <?php echo $jenis === 'pemasukan' ? 'selected' : ''; ?>>Pemasukan</option>
                    <option value="pengeluaran" <?php echo $jenis === 'pengeluaran' ? 'selected' : ''; ?>>Pengeluaran</option>
                </select>
            </label>
            <label>
                Kategori
                <select name="kategori_id">
                    <option value="">Semua</option>
                    <?php foreach ($kategoriList as $kat) : ?>
                        <option value="<?php echo $kat['id']; ?>" <?php echo (string) $kategori === (string) $kat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($kat['nama'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Kegiatan
                <select name="kegiatan_id">
                    <option value="">Semua</option>
                    <?php foreach ($kegiatanList as $keg) : ?>
                        <option value="<?php echo $keg['id']; ?>" <?php echo (string) $kegiatan === (string) $keg['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($keg['nama'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Tanggal Mulai
                <input type="date" name="tanggal_mulai" value="<?php echo htmlspecialchars($tanggalMulai, ENT_QUOTES, 'UTF-8'); ?>">
            </label>
            <label>
                Tanggal Akhir
                <input type="date" name="tanggal_akhir" value="<?php echo htmlspecialchars($tanggalAkhir, ENT_QUOTES, 'UTF-8'); ?>">
            </label>
            <label>
                Search
                <input type="text" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Keterangan">
            </label>
            <button type="submit" class="btn btn-primary">Terapkan</button>
            <a href="<?php echo base_url('pages/transaksi/index.php'); ?>" class="btn">Reset</a>
        </form>
    </div>

    <div class="card">
        <div class="card-title">Daftar Transaksi</div>
        <?php if ($role === 'bendahara') : ?>
            <div class="actions">
                <a href="<?php echo base_url('pages/transaksi/tambah.php'); ?>" class="btn btn-success">Tambah Transaksi</a>
            </div>
        <?php endif; ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th>Kategori</th>
                    <th>Kegiatan</th>
                    <th>PIC</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$transaksi) : ?>
                    <tr><td colspan="8">Tidak ada data transaksi.</td></tr>
                <?php else : ?>
                    <?php foreach ($transaksi as $trx) : ?>
                        <?php $status = $trx['status'] ?? 'approved'; ?>
                        <tr>
                            <td><?php echo format_tanggal($trx['tanggal']); ?></td>
                            <td>
                                <span class="badge <?php echo $trx['jenis'] === 'pemasukan' ? 'badge-success' : 'badge-danger'; ?>">
                                    <?php echo htmlspecialchars($trx['jenis'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($trx['kategori_nama'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($trx['kegiatan_nama'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($trx['pic'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo format_rupiah($trx['jumlah']); ?></td>
                            <td>
                                <span class="badge <?php echo $status === 'approved' ? 'badge-success' : ($status === 'rejected' ? 'badge-danger' : 'badge-warning'); ?>">
                                    <?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                                <?php if ($status === 'rejected' && !empty($trx['catatan_penolakan'])) : ?>
                                    <div style="margin-top:6px;font-size:12px;color:var(--muted);">
                                        <?php echo nl2br(htmlspecialchars($trx['catatan_penolakan'], ENT_QUOTES, 'UTF-8')); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="<?php echo base_url('pages/transaksi/detail.php?id=' . $trx['id']); ?>" class="btn">Detail</a>
                                <?php if ($role === 'bendahara' && $status === 'pending') : ?>
                                    <a href="<?php echo base_url('pages/transaksi/edit.php?id=' . $trx['id']); ?>" class="btn btn-primary">Edit</a>
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