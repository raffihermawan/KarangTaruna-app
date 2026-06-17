<?php
/** @var float|int $saldoNow */
/** @var float|int $totalPemasukan */
/** @var float|int $totalPengeluaran */
/** @var int $kegiatanAktif */

// Pastikan fungsi format_rupiah ada untuk menghindari crash jika helper belum termuat
if (!function_exists('format_rupiah')) {
    function format_rupiah($angka) {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
}

// Inisialisasi data ke dalam array metrics
$metrics = [
    ['label' => 'Saldo Saat Ini', 'value' => format_rupiah($saldoNow)],
    ['label' => 'Total Pemasukan', 'value' => format_rupiah($totalPemasukan)],
    ['label' => 'Total Pengeluaran', 'value' => format_rupiah($totalPengeluaran)],
    ['label' => 'Kegiatan Aktif', 'value' => (string) $kegiatanAktif],
];
?>
<table class="pdf-metrics" cellspacing="0" cellpadding="0" border="0">
    <tr>
        <?php foreach ($metrics as $metric) : ?>
            <td class="pdf-metrics__cell">
                <div class="pdf-metrics__label"><?php echo htmlspecialchars($metric['label'], ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="pdf-metrics__value"><?php echo htmlspecialchars($metric['value'], ENT_QUOTES, 'UTF-8'); ?></div>
            </td>
        <?php endforeach; ?>
    </tr>
</table>
