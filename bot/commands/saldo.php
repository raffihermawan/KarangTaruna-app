<?php

global $chatId;

$pdo = db();
$saldo = get_saldo($pdo);
$text = "Saldo kas saat ini: Rp" . number_format($saldo, 0, ',', '.');

telegram_send_message($chatId, $text);