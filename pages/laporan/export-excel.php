<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';
require_once __DIR__ . '/../../helpers/query-helper.php';
require_once __DIR__ . '/../../helpers/format-helper.php';

require_role(['bendahara', 'ketua']);

$autoload = __DIR__ . '/../../vendor/autoload.php';
$useSpreadsheet = false;
if (file_exists($autoload)) {
    require_once $autoload;
    $useSpreadsheet = class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet');
}

if (!$useSpreadsheet) {
    $basePath = __DIR__ . '/../../vendor/phpspreadsheet/src/PhpSpreadsheet';
    $autoloaderPath = $basePath . '/Autoloader.php';
    if (file_exists($autoloaderPath)) {
        require_once $autoloaderPath;
        if (class_exists('\PhpOffice\PhpSpreadsheet\Autoloader')) {
            \PhpOffice\PhpSpreadsheet\Autoloader::register();
            $useSpreadsheet = class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet');
        }
    }
}

$pdo = db();
$tanggalMulai = $_GET['tanggal_mulai'] ?? '';
$tanggalAkhir = $_GET['tanggal_akhir'] ?? '';
$jenis = $_GET['jenis'] ?? '';
$statusFilter = table_has_column($pdo, 'transaksi', 'status')
    ? 'COALESCE(t.status, "approved") = "approved"'
    : '1 = 1';

$where = [];
$params = [];
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
if ($statusFilter !== '1 = 1') {
    $where[] = $statusFilter;
}
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY t.tanggal DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);
$settings = get_pengaturan($pdo);
$organizationName = $settings['nama_organisasi'] ?? APP_NAME;
$organizationAddress = trim((string) ($settings['alamat'] ?? ''));
$organizationEmail = trim((string) ($settings['email'] ?? ''));
$organizationBank = trim((string) ($settings['nama_bank'] ?? ''));
$organizationAccount = trim((string) ($settings['no_rekening'] ?? ''));
$activeYear = trim((string) ($settings['tahun_aktif'] ?? ''));
$logoPath = null;
if (!empty($settings['logo'])) {
    $candidate = __DIR__ . '/../../' . ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $settings['logo']), DIRECTORY_SEPARATOR);
    if (is_file($candidate)) {
        $logoPath = $candidate;
    }
}
if ($logoPath === null) {
    $fallbackLogo = __DIR__ . '/../../assets/img/karang-taruna-logo.png';
    if (is_file($fallbackLogo)) {
        $logoPath = $fallbackLogo;
    }
}

if ($useSpreadsheet) {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Laporan Keuangan');

    if ($logoPath) {
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Logo organisasi');
        $drawing->setPath($logoPath);
        $drawing->setHeight(55);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(8);
        $drawing->setOffsetY(8);
        $drawing->setWorksheet($sheet);
    }

    $sheet->mergeCells('B1:F1');
    $sheet->mergeCells('B2:F2');
    $sheet->mergeCells('B3:F3');
    $sheet->setCellValue('B1', $organizationName);
    $sheet->setCellValue('B2', $organizationEmail !== '' ? 'Email: ' . $organizationEmail : 'Laporan Keuangan');
    $organizationDetails = $organizationAddress !== '' ? $organizationAddress : 'Laporan Keuangan';
    if ($organizationBank !== '' || $organizationAccount !== '') {
        $organizationDetails .= ' | Bank: ' . ($organizationBank !== '' ? $organizationBank : '-') . ' | Rek: ' . ($organizationAccount !== '' ? $organizationAccount : '-');
    }
    $sheet->setCellValue('B3', $organizationDetails);
    $sheet->getStyle('B1:B3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(11);
    $sheet->getStyle('B3')->getFont()->setSize(10);
    $sheet->getStyle('B1:F3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
    $sheet->getRowDimension(1)->setRowHeight(24);
    $sheet->getRowDimension(2)->setRowHeight(20);
    $sheet->getRowDimension(3)->setRowHeight(18);

    $sheet->mergeCells('A5:F5');
    $sheet->setCellValue('A5', 'Periode Laporan: ' . (($tanggalMulai !== '' && $tanggalAkhir !== '') ? format_tanggal($tanggalMulai) . ' s.d. ' . format_tanggal($tanggalAkhir) : 'Semua Periode') . ($activeYear !== '' ? ' | Tahun Aktif: ' . $activeYear : ''));
    $sheet->getStyle('A5')->getFont()->setItalic(true);

    $headers = ['No', 'Tanggal', 'Keterangan / Kegiatan', 'Pemasukan', 'Pengeluaran', 'Saldo'];
    $sheet->fromArray($headers, null, 'A7');

    $row = 8;
    $runningSaldo = 0.0;
    $totalPemasukan = 0.0;
    $totalPengeluaran = 0.0;

    foreach ($transaksi as $trx) {
        $pemasukan = $trx['jenis'] === 'pemasukan' ? (float) $trx['jumlah'] : 0.0;
        $pengeluaran = $trx['jenis'] === 'pengeluaran' ? (float) $trx['jumlah'] : 0.0;

        $runningSaldo += $pemasukan;
        $runningSaldo -= $pengeluaran;

        $totalPemasukan += $pemasukan;
        $totalPengeluaran += $pengeluaran;

        $description = trim((string) ($trx['kegiatan_nama'] ?: '-')) . ' - ' . trim((string) $trx['kategori_nama']) . ' (' . $trx['jenis'] . ')';

        $sheet->setCellValue('A' . $row, $row - 7);
        $sheet->setCellValue('B' . $row, format_tanggal($trx['tanggal']));
        $sheet->setCellValue('C' . $row, $description);
        $sheet->setCellValue('D' . $row, $pemasukan > 0 ? $pemasukan : 0);
        $sheet->setCellValue('E' . $row, $pengeluaran > 0 ? $pengeluaran : 0);
        $sheet->setCellValue('F' . $row, $runningSaldo);

        $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $row++;
    }

    $summaryStartRow = $row;
    $sheet->setCellValue('A' . $summaryStartRow, 'Total Pemasukan');
    $sheet->setCellValue('D' . $summaryStartRow, $totalPemasukan);
    $sheet->getStyle('D' . $summaryStartRow)->getNumberFormat()->setFormatCode('"Rp "#,##0');

    $sheet->setCellValue('A' . ($summaryStartRow + 1), 'Total Pengeluaran');
    $sheet->setCellValue('E' . ($summaryStartRow + 1), $totalPengeluaran);
    $sheet->getStyle('E' . ($summaryStartRow + 1))->getNumberFormat()->setFormatCode('"Rp "#,##0');

    $sheet->setCellValue('A' . ($summaryStartRow + 2), 'Saldo Akhir');
    $sheet->setCellValue('F' . ($summaryStartRow + 2), $totalPemasukan - $totalPengeluaran);
    $sheet->getStyle('F' . ($summaryStartRow + 2))->getNumberFormat()->setFormatCode('"Rp "#,##0');

    $lastSummaryRow = $summaryStartRow + 2;

    // Style header row
    $sheet->getStyle('A7:F7')->getFont()->setBold(true);
    $sheet->getStyle('A7:F7')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('EEEEEE');
    $sheet->getStyle('A7:F' . $lastSummaryRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    // Column widths
    $sheet->getColumnDimension('A')->setWidth(8);
    $sheet->getColumnDimension('B')->setWidth(14);
    $sheet->getColumnDimension('C')->setWidth(46);
    $sheet->getColumnDimension('D')->setWidth(18);
    $sheet->getColumnDimension('E')->setWidth(18);
    $sheet->getColumnDimension('F')->setWidth(18);
    // Alignments
    $sheet->getStyle('A8:A' . $lastSummaryRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('B8:B' . $lastSummaryRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('D8:F' . $lastSummaryRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
    $sheet->getStyle('A' . $summaryStartRow . ':A' . $lastSummaryRow)->getFont()->setBold(true);
    // Freeze header
    $sheet->freezePane('A8');

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="laporan-keuangan.xlsx"');
    $writer->save('php://output');
    exit;
}

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="laporan-keuangan.xls"');
header('Cache-Control: max-age=0');

$counter = 0;
$runningSaldo = 0.0;
$totalPemasukan = 0.0;
$totalPengeluaran = 0.0;

echo '<table border="1" cellpadding="4" cellspacing="0">';
echo '<tr><th colspan="6">' . htmlspecialchars($organizationName, ENT_QUOTES, 'UTF-8') . '</th></tr>';
echo '<tr><th colspan="6">' . htmlspecialchars($organizationEmail !== '' ? 'Email: ' . $organizationEmail : 'Laporan Keuangan', ENT_QUOTES, 'UTF-8') . '</th></tr>';
$organizationDetails = $organizationAddress !== '' ? $organizationAddress : 'Laporan Keuangan';
if ($organizationBank !== '' || $organizationAccount !== '') {
    $organizationDetails .= ' | Bank: ' . ($organizationBank !== '' ? $organizationBank : '-') . ' | Rek: ' . ($organizationAccount !== '' ? $organizationAccount : '-');
}
echo '<tr><th colspan="6">' . htmlspecialchars($organizationDetails, ENT_QUOTES, 'UTF-8') . '</th></tr>';
$periodLabel = (($tanggalMulai !== '' && $tanggalAkhir !== '') ? format_tanggal($tanggalMulai) . ' s.d. ' . format_tanggal($tanggalAkhir) : 'Semua Periode') . ($activeYear !== '' ? ' | Tahun Aktif: ' . $activeYear : '');
echo '<tr><th colspan="6">Periode Laporan: ' . htmlspecialchars($periodLabel, ENT_QUOTES, 'UTF-8') . '</th></tr>';
echo '<tr><th>No</th><th>Tanggal</th><th>Keterangan / Kegiatan</th><th>Pemasukan</th><th>Pengeluaran</th><th>Saldo</th></tr>';

foreach ($transaksi as $trx) {
    $counter++;
    $pemasukan = $trx['jenis'] === 'pemasukan' ? (float) $trx['jumlah'] : 0.0;
    $pengeluaran = $trx['jenis'] === 'pengeluaran' ? (float) $trx['jumlah'] : 0.0;

    $runningSaldo += $pemasukan;
    $runningSaldo -= $pengeluaran;

    $totalPemasukan += $pemasukan;
    $totalPengeluaran += $pengeluaran;

    $description = trim((string) ($trx['kegiatan_nama'] ?: '-')) . ' - ' . trim((string) $trx['kategori_nama']) . ' (' . $trx['jenis'] . ')';

    echo '<tr>';
    echo '<td>' . htmlspecialchars((string) $counter, ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td>' . htmlspecialchars(format_tanggal($trx['tanggal']), ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td>' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td style="text-align:right;">Rp ' . htmlspecialchars(number_format($pemasukan > 0 ? $pemasukan : 0, 0, ',', '.'), ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td style="text-align:right;">Rp ' . htmlspecialchars(number_format($pengeluaran > 0 ? $pengeluaran : 0, 0, ',', '.'), ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td style="text-align:right; font-weight:bold;">Rp ' . htmlspecialchars(number_format($runningSaldo, 0, ',', '.'), ENT_QUOTES, 'UTF-8') . '</td>';
    echo '</tr>';
}

if ($counter === 0) {
    echo '<tr><td colspan="6" style="text-align:center;">Tidak ada data transaksi.</td></tr>';
} else {
    echo '<tr><td colspan="3" style="font-weight:bold;">Total Pemasukan</td><td style="text-align:right; font-weight:bold;">Rp ' . htmlspecialchars(number_format($totalPemasukan, 0, ',', '.'), ENT_QUOTES, 'UTF-8') . '</td><td colspan="2"></td></tr>';
    echo '<tr><td colspan="3" style="font-weight:bold;">Total Pengeluaran</td><td colspan="2" style="text-align:right; font-weight:bold;">Rp ' . htmlspecialchars(number_format($totalPengeluaran, 0, ',', '.'), ENT_QUOTES, 'UTF-8') . '</td><td></td></tr>';
    echo '<tr><td colspan="3" style="font-weight:bold;">Saldo Akhir</td><td colspan="3" style="text-align:right; font-weight:bold;">Rp ' . htmlspecialchars(number_format($totalPemasukan - $totalPengeluaran, 0, ',', '.'), ENT_QUOTES, 'UTF-8') . '</td></tr>';
}

echo '</table>';
exit;
