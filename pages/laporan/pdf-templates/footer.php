<?php
$signatureDate = $signatureDate ?? date('d F Y');
?>
<table class="pdf-signature" cellspacing="0" cellpadding="0" border="0">
    <tr>
        <td class="pdf-signature__cell">
            <div class="pdf-signature__label">Mengetahui,<br>Ketua Karang Taruna 001</div>
            <div class="pdf-signature__space"></div>
            <div class="pdf-signature__name">(____________________)</div>
        </td>
        <td class="pdf-signature__cell">
            <div class="pdf-signature__label">Jakarta, <?php echo htmlspecialchars($signatureDate, ENT_QUOTES, 'UTF-8'); ?><br>Bendahara</div>
            <div class="pdf-signature__space"></div>
            <div class="pdf-signature__name">(____________________)</div>
        </td>
    </tr>
</table>
