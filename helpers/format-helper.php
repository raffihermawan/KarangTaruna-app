<?php
function format_rupiah($amount): string
{
    $value = is_numeric($amount) ? (float) $amount : 0;
    return 'Rp ' . number_format($value, 0, ',', '.');
}

function format_tanggal(?string $date): string
{
    if (!$date) {
        return '-';
    }

    return date('d-m-Y', strtotime($date));
}
