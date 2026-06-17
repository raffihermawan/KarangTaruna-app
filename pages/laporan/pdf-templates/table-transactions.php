<?php
/** @var array $transactions */
/** @var float|int $saldoNow */

$transactions = $transactions ?? [];

// Mulai saldo dari 0 (running saldo)
$runningSaldo = 0.0;
$counter = 0;
?>
<table class="pdf-table" cellspacing="0" cellpadding="0" border="0">
    <thead>
        <tr>
            <th class="col-center" style="width:6%">No</th>
            <th class="col-center" style="width:14%">Tanggal</th>
            <th class="col-left" style="width:34%">Keterangan / Kegiatan</th>
            <th class="col-right" style="width:14%">Pemasukan</th>
            <th class="col-right" style="width:14%">Pengeluaran</th>
            <th class="col-right" style="width:18%">Saldo</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($transactions as $trx) : ?>
            <?php
            $counter++;

            $pemasukan = $trx['jenis'] === 'pemasukan' ? (float) $trx['jumlah'] : 0.0;
            $pengeluaran = $trx['jenis'] === 'pengeluaran' ? (float) $trx['jumlah'] : 0.0;

            // HITUNG SALDO BERJALAN
            $runningSaldo += $pemasukan;
            $runningSaldo -= $pengeluaran;

            // Saldo untuk baris ini
            $saldoBarisIni = $runningSaldo;

            $description = trim((string) ($trx['kegiatan_nama'] ?: '-')) . ' - ' . trim((string) $trx['kategori_nama']) . ' (' . $trx['jenis'] . ')';
            ?>
            <tr>
                <td class="col-center"><?php echo $counter; ?></td>
                <td class="col-center"><?php echo htmlspecialchars(format_tanggal($trx['tanggal']), ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="col-left"><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="col-right"><?php echo $pemasukan > 0 ? format_rupiah($pemasukan) : '-'; ?></td>
                <td class="col-right"><?php echo $pengeluaran > 0 ? format_rupiah($pengeluaran) : '-'; ?></td>
                <td class="col-right" style="font-weight: bold;"><?php echo format_rupiah($saldoBarisIni); ?></td>
            </tr>
        <?php endforeach; ?>

        <?php if ($counter === 0) : ?>
            <tr>
                <td colspan="6" class="col-center pdf-table__empty">Tidak ada data transaksi.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>