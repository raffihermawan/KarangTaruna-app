<?php
$signatureDate = $signatureDate ?? date('d F Y');
$namaKetua = trim((string) ($namaKetua ?? ''));
$namaBendahara = trim((string) ($namaBendahara ?? ''));
$tampilkanTandaTangan = (bool) ($tampilkanTandaTangan ?? true);
$signatureKetuaPath = $signatureKetuaPath ?? null;
$signatureBendaharaPath = $signatureBendaharaPath ?? null;
if (!$tampilkanTandaTangan) {
    return;
}
?>
<table class="pdf-signature" cellspacing="0" cellpadding="0" border="0">
    <tr>
        <td class="pdf-signature__cell">
            <div class="pdf-signature__label">Mengetahui,<br>Ketua <?php echo htmlspecialchars($organizationName ?? 'Karang Taruna', ENT_QUOTES, 'UTF-8'); ?></div>
            <?php if ($signatureKetuaPath && is_file($signatureKetuaPath)) : ?>
                <img src="data:image/<?php echo htmlspecialchars(pathinfo($signatureKetuaPath, PATHINFO_EXTENSION), ENT_QUOTES, 'UTF-8'); ?>;base64,<?php echo base64_encode(file_get_contents($signatureKetuaPath)); ?>" style="height: 55px; max-width: 150px; object-fit: contain;" alt="Tanda tangan Ketua">
            <?php else : ?>
            <div class="pdf-signature__space"></div>
            <?php endif; ?>
            <div class="pdf-signature__name">(<?php echo htmlspecialchars($namaKetua ?: '____________________', ENT_QUOTES, 'UTF-8'); ?>)</div>
        </td>
        <td class="pdf-signature__cell">
            <div class="pdf-signature__label">Jakarta, <?php echo htmlspecialchars($signatureDate, ENT_QUOTES, 'UTF-8'); ?><br>Bendahara</div>
            <?php if ($signatureBendaharaPath && is_file($signatureBendaharaPath)) : ?>
                <img src="data:image/<?php echo htmlspecialchars(pathinfo($signatureBendaharaPath, PATHINFO_EXTENSION), ENT_QUOTES, 'UTF-8'); ?>;base64,<?php echo base64_encode(file_get_contents($signatureBendaharaPath)); ?>" style="height: 55px; max-width: 150px; object-fit: contain;" alt="Tanda tangan Bendahara">
            <?php else : ?>
            <div class="pdf-signature__space"></div>
            <?php endif; ?>
            <div class="pdf-signature__name">(<?php echo htmlspecialchars($namaBendahara ?: '____________________', ENT_QUOTES, 'UTF-8'); ?>)</div>
        </td>
    </tr>
</table>
