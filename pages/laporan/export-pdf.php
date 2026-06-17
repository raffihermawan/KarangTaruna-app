<?php
use Dompdf\Dompdf;
use Dompdf\Options;

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';
require_once __DIR__ . '/../../helpers/query-helper.php';
require_once __DIR__ . '/../../helpers/format-helper.php';

require_role(['bendahara', 'ketua']);

// Pastikan vendor autoload Dompdf dipanggil dengan benar
$autoload = __DIR__ . '/../../../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
} else {
    // Jika masih tidak ketemu, gunakan fallback alternative root path
    require_once $_SERVER['DOCUMENT_ROOT'] . '/KarangTaruna-app/vendor/autoload.php';
}

function render_pdf_template(string $templateFile, array $data = []): string
{
    extract($data, EXTR_SKIP);
    ob_start();
    include $templateFile;
    return (string) ob_get_clean();
}

function resolve_logo_path(array $settings): ?string
{
    $candidates = [];
    $projectRoot = realpath(__DIR__ . '/../../');

    if (!empty($settings['logo'])) {
        $storedLogo = trim((string) $settings['logo']);
        $storedLogo = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $storedLogo);
        $storedLogo = ltrim($storedLogo, DIRECTORY_SEPARATOR);

        if ($projectRoot !== false) {
            $candidates[] = $projectRoot . DIRECTORY_SEPARATOR . $storedLogo;
        }

        $documentRootCandidate = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), DIRECTORY_SEPARATOR);
        if ($documentRootCandidate !== '') {
            $candidates[] = $documentRootCandidate . DIRECTORY_SEPARATOR . 'KarangTaruna-app' . DIRECTORY_SEPARATOR . $storedLogo;
        }
    }

    $fallbackLogo = __DIR__ . '/../../assets/img/karang-taruna-logo.png';
    $candidates[] = $fallbackLogo;

    foreach ($candidates as $candidate) {
        $normalizedCandidate = $candidate;
        if ($normalizedCandidate !== '' && is_file($normalizedCandidate)) {
            $realPath = realpath($normalizedCandidate);
            return $realPath !== false ? $realPath : $normalizedCandidate;
        }
    }

    return null;
}

function format_report_period_text(?string $tanggalMulai, ?string $tanggalAkhir): string
{
    if ($tanggalMulai !== null && $tanggalAkhir !== null && $tanggalMulai !== '' && $tanggalAkhir !== '') {
        return 'Periode: ' . format_tanggal($tanggalMulai) . ' s.d. ' . format_tanggal($tanggalAkhir);
    }
    return 'Periode: ' . date('F Y');
}

// Inisialisasi Database
$pdo = db();
$tanggalMulai = $_GET['tanggal_mulai'] ?? '';
$tanggalAkhir = $_GET['tanggal_akhir'] ?? '';
$jenis = $_GET['jenis'] ?? '';
$statusFilter = table_has_column($pdo, 'transaksi', 'status')
    ? 'COALESCE(t.status, "approved") = "approved"'
    : '1 = 1';

// Query Data Transaksi Utama
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
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Load Settings Organisasi
$settings = get_pengaturan($pdo);
$organizationName = $settings['nama_organisasi'] ?? 'Karang Taruna 001';
$organizationAddress = trim((string) ($settings['alamat'] ?? ''));
$organizationEmail = trim((string) ($settings['email'] ?? ''));
$logoPath = resolve_logo_path($settings);
$periodText = format_report_period_text($tanggalMulai, $tanggalAkhir);
$signatureDate = date('d F Y');

// Hitung Saldo & Ringkasan Keuangan
$saldoNow = 0.0;
if (function_exists('get_saldo')) {
    try {
        $saldoNow = (float) get_saldo($pdo);
    } catch (Throwable $e) {
        $saldoNow = 0.0;
    }
}

$sqlTotals = 'SELECT SUM(CASE WHEN jenis = "pemasukan" THEN jumlah ELSE 0 END) AS pemasukan,
                     SUM(CASE WHEN jenis = "pengeluaran" THEN jumlah ELSE 0 END) AS pengeluaran
              FROM transaksi t';
$whereTotals = [];
$paramsTotals = [];
if ($statusFilter !== '1 = 1') {
    $whereTotals[] = $statusFilter;
}
if ($tanggalMulai !== '' && $tanggalAkhir !== '') {
    $whereTotals[] = 't.tanggal BETWEEN :mulai AND :akhir';
    $paramsTotals['mulai'] = $tanggalMulai;
    $paramsTotals['akhir'] = $tanggalAkhir;
}
if ($whereTotals) {
    $sqlTotals .= ' WHERE ' . implode(' AND ', $whereTotals);
}
$stmtTotals = $pdo->prepare($sqlTotals);
$stmtTotals->execute($paramsTotals);
$rowTotals = $stmtTotals->fetch(PDO::FETCH_ASSOC) ?: [];
$totalPemasukan = (float) ($rowTotals['pemasukan'] ?? 0);
$totalPengeluaran = (float) ($rowTotals['pengeluaran'] ?? 0);
$kegiatanAktif = (int) ($pdo->query('SELECT COUNT(*) FROM kegiatan WHERE status = "aktif"')->fetchColumn() ?: 0);

// Data yang dilempar ke komponen template
$templateBase = __DIR__ . '/pdf-templates';
$reportData = [
    'organizationName' => $organizationName,
    'organizationAddress' => $organizationAddress,
    'organizationEmail' => $organizationEmail,
    'logoPath' => $logoPath,
    'periodText' => $periodText,
    'saldoNow' => $saldoNow,
    'totalPemasukan' => $totalPemasukan,
    'totalPengeluaran' => $totalPengeluaran,
    'kegiatanAktif' => $kegiatanAktif,
    'transactions' => $transactions,
    'signatureDate' => $signatureDate,
];

// KONFIGURASI DAN EKSEKUSI RE-RENDER DOMPDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->set('defaultFont', 'Arial');
$dompdf = new Dompdf($options);

// Susun Dokumen HTML + Inject CSS Utama agar rapi kotak-kotak
$html = '<!DOCTYPE html><html><head><meta charset="utf-8"><style>';
$html .= '@page { size: A4; margin: 20mm 20mm 20mm 20mm; }';
$html .= 'body { font-family: Arial, sans-serif; font-size: 11px; color: #000; margin: 0; padding: 0; line-height: 1.4; }';

/* Style Tabel Ringkasan Metrik */
$html .= '.pdf-metrics { width: 100%; border-collapse: collapse; margin-top: 15px; margin-bottom: 20px; }';
$html .= '.pdf-metrics__cell { width: 25%; border: 1px solid #000; text-align: center; padding: 10px 5px; vertical-align: middle; }';
$html .= '.pdf-metrics__label { font-size: 9px; font-weight: bold; text-transform: uppercase; margin-bottom: 4px; color: #555; }';
$html .= '.pdf-metrics__value { font-size: 11px; font-weight: bold; }';

/* Style Tabel Transaksi Utama */
$html .= '.pdf-table { width: 100%; border-collapse: collapse; border: 1px solid #000; margin-top: 10px; }';
$html .= '.pdf-table thead { display: table-header-group; background-color: #f5f5f5; }';
$html .= '.pdf-table th, .pdf-table td { border: 1px solid #000; padding: 7px 6px; color: #000; }';
$html .= '.pdf-table th { text-align: center; font-weight: bold; }';
$html .= '.pdf-table tr { page-break-inside: avoid; }';
$html .= '.pdf-table .col-center { text-align: center; }';
$html .= '.pdf-table .col-left { text-align: left; }';
$html .= '.pdf-table .col-right { text-align: right; }';
$html .= '.pdf-table__empty { text-align: center; padding: 20px; font-style: italic; }';

/* Style Lembar Tanda Tangan */
$html .= '.pdf-signature { width: 100%; margin-top: 40px; border-collapse: collapse; page-break-inside: avoid; }';
$html .= '.pdf-signature__cell { width: 50%; text-align: center; vertical-align: top; border: none !important; }';
$html .= '.pdf-signature__space { height: 65px; }';
$html .= '.pdf-signature__name { font-weight: bold; text-decoration: underline; }';
$html .= '</style></head><body>';

// Kompilasi file komponen modular ke dalam bodi HTML
$html .= render_pdf_template($templateBase . '/header.php', $reportData);
$html .= render_pdf_template($templateBase . '/metrics.php', $reportData);
$html .= render_pdf_template($templateBase . '/table-transactions.php', $reportData);
$html .= render_pdf_template($templateBase . '/footer.php', $reportData);
$html .= '</body></html>';

// Jalankan Engine Dompdf
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Stream langsung sebagai file PDF
$dompdf->stream('laporan-keuangan.pdf', ['Attachment' => false]);
exit;
