<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';
require_once __DIR__ . '/../../helpers/format-helper.php';
require_once __DIR__ . '/../../helpers/query-helper.php';

require_login();

$pdo = db();
$saldo = get_saldo($pdo);
$bulan = (int) date('m');
$tahun = (int) date('Y');
$statusFilter = 'COALESCE(status, "approved") = "approved"';

$stmt = $pdo->prepare('
    SELECT
        SUM(CASE WHEN jenis = "pemasukan" THEN jumlah ELSE 0 END) AS pemasukan,
        SUM(CASE WHEN jenis = "pengeluaran" THEN jumlah ELSE 0 END) AS pengeluaran
    FROM transaksi
    WHERE ' . $statusFilter . '
      AND MONTH(tanggal) = :bulan AND YEAR(tanggal) = :tahun
');
$stmt->execute(['bulan' => $bulan, 'tahun' => $tahun]);
$bulanIni = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['pemasukan' => 0, 'pengeluaran' => 0];

$kegiatanAktif = (int) $pdo->query("SELECT COUNT(*) FROM kegiatan WHERE status = 'aktif'")->fetchColumn();
$lastUpdate = $pdo->query('SELECT MAX(updated_at) FROM transaksi WHERE ' . $statusFilter)->fetchColumn();
$lastUpdateFormatted = $lastUpdate ? date('d M Y H:i', strtotime($lastUpdate)) : '-';

$recentStmt = $pdo->query('
    SELECT t.*, k.nama AS kategori_nama, keg.nama AS kegiatan_nama
    FROM transaksi t
    JOIN kategori_transaksi k ON k.id = t.kategori_id
    LEFT JOIN kegiatan keg ON keg.id = t.kegiatan_id
    WHERE COALESCE(t.status, "approved") = "approved"
    ORDER BY t.tanggal DESC, t.id DESC
    LIMIT 5
');
$recentTransaksi = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Dashboard';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <?php include __DIR__ . '/../../includes/alert.php'; ?>

    <div class="dashboard-hero">
        <div>
            <div class="dashboard-title">Dashboard Keuangan</div>
            <p class="dashboard-subtitle">Ringkasan kondisi kas organisasi dan aktivitas terkini.</p>
        </div>
        <div class="dashboard-meta">
            <span class="live-dot" aria-hidden="true"></span>
            <span>Update terakhir: <?php echo htmlspecialchars($lastUpdateFormatted, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><path d="M4 7h16v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 7l2-3h12l2 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 12h4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            </div>
            <div>
                <div class="stat-label">Saldo Saat Ini</div>
                <div class="stat-value"><?php echo format_rupiah($saldo); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon--success" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><path d="M12 4v16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M8 8l4-4 4 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 20h14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            </div>
            <div>
                <div class="stat-label">Pemasukan Bulan Ini</div>
                <div class="stat-value"><?php echo format_rupiah($bulanIni['pemasukan']); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon--danger" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><path d="M12 20V4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M8 16l4 4 4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 4h14" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
            </div>
            <div>
                <div class="stat-label">Pengeluaran Bulan Ini</div>
                <div class="stat-value"><?php echo format_rupiah($bulanIni['pengeluaran']); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon--accent" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><path d="M7 4v3M17 4v3M4 9h16M6 12h5M6 16h9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><rect x="4" y="6" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/></svg>
            </div>
            <div>
                <div class="stat-label">Kegiatan Aktif</div>
                <div class="stat-value"><?php echo $kegiatanAktif; ?></div>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="card card--chart">
            <div class="card-title">Grafik Arus Kas Bulanan</div>
            <div class="chart-container">
                <canvas id="chartArusKas" data-url="<?php echo base_url('api/chart-arus-kas.php'); ?>"></canvas>
            </div>
        </div>

        <div class="card card--chart">
            <div class="card-title">Distribusi Pengeluaran per Divisi</div>
            <div class="chart-container chart-container--sm">
                <canvas id="chartKategori" data-url="<?php echo base_url('api/chart-kategori.php'); ?>"></canvas>
            </div>
        </div>
    </div>

    <div class="card card--table">
        <div class="card-title">5 Transaksi Terbaru</div>
        <div class="table-wrap">
            <table class="table">
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
                    <?php if (!$recentTransaksi) : ?>
                        <tr><td colspan="5">Belum ada transaksi.</td></tr>
                    <?php else : ?>
                        <?php foreach ($recentTransaksi as $trx) : ?>
                            <tr>
                                <td><?php echo format_tanggal($trx['tanggal']); ?></td>
                                <td>
                                    <span class="badge <?php echo $trx['jenis'] === 'pemasukan' ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo htmlspecialchars($trx['jenis'], ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?php echo base_url('assets/js/chart.js'); ?>"></script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
