<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';
require_once __DIR__ . '/../../helpers/format-helper.php';
require_once __DIR__ . '/../../helpers/query-helper.php';

require_login();

$pdo = db();
$tanggalMulai = $_GET['tanggal_mulai'] ?? '';
$tanggalAkhir = $_GET['tanggal_akhir'] ?? '';
$jenis = $_GET['jenis'] ?? '';

$where = [];
$params = [];
$statusFilter = table_has_column($pdo, 'transaksi', 'status')
    ? 'COALESCE(t.status, "approved") = "approved"'
    : '1 = 1';

if ($statusFilter !== '1 = 1') {
    $where[] = $statusFilter;
}
if ($tanggalMulai !== '' && $tanggalAkhir !== '') {
    $where[] = 't.tanggal BETWEEN :mulai AND :akhir';
    $params['mulai'] = $tanggalMulai;
    $params['akhir'] = $tanggalAkhir;
}
if ($jenis !== '') {
    $where[] = 't.jenis = :jenis';
    $params['jenis'] = $jenis;
}

$sql = 'SELECT t.*, k.nama AS kategori_nama, keg.nama AS kegiatan_nama
        FROM transaksi t
        JOIN kategori_transaksi k ON k.id = t.kategori_id
        LEFT JOIN kegiatan keg ON keg.id = t.kegiatan_id';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY t.tanggal DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalPemasukan = 0;
$totalPengeluaran = 0;
foreach ($transaksi as $trx) {
    if ($trx['jenis'] === 'pemasukan') {
        $totalPemasukan += (float) $trx['jumlah'];
    } else {
        $totalPengeluaran += (float) $trx['jumlah'];
    }
}

$queryString = http_build_query([
    'tanggal_mulai' => $tanggalMulai,
    'tanggal_akhir' => $tanggalAkhir,
    'jenis' => $jenis,
]);
$role = current_user()['role'] ?? '';

$page_title = 'Laporan Keuangan';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <div class="card">
        <div class="card-title">Filter Laporan</div>
        <form method="get" class="filters laporan-filters">
            <label>
                Tanggal Mulai
                <input type="date" name="tanggal_mulai" value="<?php echo htmlspecialchars($tanggalMulai, ENT_QUOTES, 'UTF-8'); ?>">
            </label>
            <label>
                Tanggal Akhir
                <input type="date" name="tanggal_akhir" value="<?php echo htmlspecialchars($tanggalAkhir, ENT_QUOTES, 'UTF-8'); ?>">
            </label>
            <label>
                Jenis
                <select name="jenis">
                    <option value="">Semua</option>
                    <option value="pemasukan" <?php echo $jenis === 'pemasukan' ? 'selected' : ''; ?>>Pemasukan</option>
                    <option value="pengeluaran" <?php echo $jenis === 'pengeluaran' ? 'selected' : ''; ?>>Pengeluaran</option>
                </select>
            </label>
            <button type="submit" class="btn btn-primary laporan-btn">Terapkan</button>
            <a href="<?php echo base_url('pages/laporan/index.php'); ?>" class="btn laporan-btn">Reset</a>
        </form>
    </div>

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

    <div class="card">
        <div class="card-title">Daftar Transaksi</div>
        <?php if ($role !== 'anggota') : ?>
            <div class="actions laporan-actions">
                <a href="<?php echo base_url('pages/laporan/export-pdf.php?' . $queryString); ?>" class="btn btn-primary">Export PDF</a>
                <a href="<?php echo base_url('pages/laporan/export-excel.php?' . $queryString); ?>" class="btn btn-success">Export Excel</a>
            </div>
        <?php endif; ?>
        <div class="table-wrap laporan-table-wrap">
        <table class="table laporan-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th>Kategori</th>
                    <th>Kegiatan</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$transaksi) : ?>
                    <tr><td colspan="5">Tidak ada data.</td></tr>
                <?php else : ?>
                    <?php foreach ($transaksi as $trx) : ?>
                        <tr>
                            <td><?php echo format_tanggal($trx['tanggal']); ?></td>
                            <td><?php echo htmlspecialchars($trx['jenis'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($trx['kategori_nama'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($trx['kegiatan_nama'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo format_rupiah($trx['jumlah']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
