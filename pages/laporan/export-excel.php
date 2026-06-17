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

    $sheet->mergeCells('B1:E1');
    $sheet->mergeCells('B2:E2');
    $sheet->mergeCells('B3:E3');
    $sheet->setCellValue('B1', $organizationName);
    $sheet->setCellValue('B2', 'Sistem Aplikasi Keuangan Karang Taruna 001 Lebak Bulus');
    $sheet->setCellValue('B3', $organizationAddress !== '' ? $organizationAddress : 'Laporan Keuangan');
    $sheet->getStyle('B1:B3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
    $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('B2')->getFont()->setBold(true)->setSize(11);
    $sheet->getStyle('B3')->getFont()->setSize(10);
    $sheet->getStyle('B1:E3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
    $sheet->getRowDimension(1)->setRowHeight(24);
    $sheet->getRowDimension(2)->setRowHeight(20);
    $sheet->getRowDimension(3)->setRowHeight(18);

    $sheet->mergeCells('A5:E5');
    $sheet->setCellValue('A5', 'Periode Laporan: ' . (($tanggalMulai !== '' && $tanggalAkhir !== '') ? format_tanggal($tanggalMulai) . ' s.d. ' . format_tanggal($tanggalAkhir) : 'Semua Periode'));
    $sheet->getStyle('A5')->getFont()->setItalic(true);

    $headers = ['Tanggal', 'Jenis', 'Kategori', 'Kegiatan', 'Jumlah'];
    $sheet->fromArray($headers, null, 'A7');

    $row = 8;
    foreach ($transaksi as $trx) {
        $sheet->setCellValue('A' . $row, format_tanggal($trx['tanggal']));
        $sheet->setCellValue('B' . $row, $trx['jenis']);
        $sheet->setCellValue('C' . $row, $trx['kategori_nama']);
        $sheet->setCellValue('D' . $row, $trx['kegiatan_nama'] ?: '-');
        $sheet->setCellValue('E' . $row, (float) $trx['jumlah']);
        $sheet->getStyle('E' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $row++;
    }

    // Style header row
    $sheet->getStyle('A7:E7')->getFont()->setBold(true);
    $sheet->getStyle('A7:E7')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('EEEEEE');
    $sheet->getStyle('A7:E' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    // Column widths
    $sheet->getColumnDimension('A')->setWidth(14);
    $sheet->getColumnDimension('B')->setWidth(12);
    $sheet->getColumnDimension('C')->setWidth(30);
    $sheet->getColumnDimension('D')->setWidth(30);
    $sheet->getColumnDimension('E')->setWidth(16);
    // Alignments
    $sheet->getStyle('A7:A' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('E8:E' . ($row - 1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
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

echo '<table border="1" cellpadding="4">';
echo '<tr><th colspan="5">' . htmlspecialchars($organizationName, ENT_QUOTES, 'UTF-8') . '</th></tr>';
echo '<tr><th colspan="5">Sistem Aplikasi Keuangan Karang Taruna 001 Lebak Bulus</th></tr>';
echo '<tr><th colspan="5">' . htmlspecialchars($organizationAddress !== '' ? $organizationAddress : 'Laporan Keuangan', ENT_QUOTES, 'UTF-8') . '</th></tr>';
echo '<tr><th colspan="5">Periode Laporan: ' . htmlspecialchars(($tanggalMulai !== '' && $tanggalAkhir !== '') ? format_tanggal($tanggalMulai) . ' s.d. ' . format_tanggal($tanggalAkhir) : 'Semua Periode', ENT_QUOTES, 'UTF-8') . '</th></tr>';
echo '<tr><th>Tanggal</th><th>Jenis</th><th>Kategori</th><th>Kegiatan</th><th>Jumlah</th></tr>';
foreach ($transaksi as $trx) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars(format_tanggal($trx['tanggal']), ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td>' . htmlspecialchars($trx['jenis'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td>' . htmlspecialchars($trx['kategori_nama'], ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td>' . htmlspecialchars($trx['kegiatan_nama'] ?: '-', ENT_QUOTES, 'UTF-8') . '</td>';
    echo '<td>' . htmlspecialchars(format_rupiah($trx['jumlah']), ENT_QUOTES, 'UTF-8') . '</td>';
    echo '</tr>';
}
echo '</table>';
exit;
