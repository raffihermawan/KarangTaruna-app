<?php
/** @var string|null $logoPath */
/** @var string $organizationName */
/** @var string $organizationAddress */
/** @var string $organizationEmail */
/** @var string $periodText */
/** @var string $organizationBank */
/** @var string $organizationAccount */

$logoPath = (string) ($logoPath ?? '');
$organizationName = (string) ($organizationName ?? 'Karang Taruna 001');
$organizationAddress = trim((string) ($organizationAddress ?? ''));
$organizationEmail = trim((string) ($organizationEmail ?? ''));
$periodText = (string) ($periodText ?? '');
$organizationBank = trim((string) ($organizationBank ?? ''));
$organizationAccount = trim((string) ($organizationAccount ?? ''));
?>
<table style="width: 100%; border-collapse: collapse; border-bottom: 3px double #000000; padding-bottom: 10px; margin-bottom: 20px;">
    <tr>
    <td style="width: 15%; text-align: center; vertical-align: middle;">
    <?php if (!empty($logoPath) && is_file($logoPath)) : ?>
    <?php
    $imageType = pathinfo($logoPath, PATHINFO_EXTENSION);
    $imageData = base64_encode(file_get_contents($logoPath));
    $src = 'data:image/' . $imageType . ';base64,' . $imageData;
    ?>

    <img src="<?php echo $src; ?>"
         style="height: 65px; width: auto; max-width: 65px; object-fit: contain;"
         alt="Logo">

<?php else : ?>            
                <div style="width: 55px; height: 55px; background-color: #e2e8f0; border-radius: 50%; margin: 0 auto;"></div>
            <?php endif; ?>
        </td>
        <td style="width: 85%; text-align: center; vertical-align: middle; padding-right: 15px;">
            <strong style="font-size: 14px; text-transform: uppercase;"><?php echo htmlspecialchars($organizationName, ENT_QUOTES, 'UTF-8'); ?></strong><br>
            <strong style="font-size: 11px; text-transform: uppercase;">KELURAHAN LEBAK BULUS - KECAMATAN CILANDAK</strong><br>
            <strong style="font-size: 11px; text-transform: uppercase;">KOTA ADMINISTRASI JAKARTA SELATAN</strong><br>
            <span style="font-size: 9px; font-style: italic;">
                Sekretariat: <?php echo htmlspecialchars($organizationAddress ?: '-', ENT_QUOTES, 'UTF-8'); ?>
                <?php if ($organizationEmail): ?> | Email: <?php echo htmlspecialchars($organizationEmail, ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>
                <?php if ($organizationBank || $organizationAccount): ?> | Bank: <?php echo htmlspecialchars($organizationBank ?: '-', ENT_QUOTES, 'UTF-8'); ?> | Rek: <?php echo htmlspecialchars($organizationAccount ?: '-', ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>
            </span>
        </td>
    </tr>
</table>

<div style="text-align: center; margin-top: 10px; margin-bottom: 15px;">
    <strong style="font-size: 13px; text-transform: uppercase;">LAPORAN KEUANGAN BULANAN BENDAHARA</strong><br>
    <span style="font-size: 10px; font-weight: bold;"><?php echo htmlspecialchars($periodText, ENT_QUOTES, 'UTF-8'); ?></span>
</div>
