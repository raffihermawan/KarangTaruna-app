<?php

global $botToken, $chatId;

$text =
"👋 Selamat datang di Bot Karang Taruna 001 Lebak Bulus

Bot ini terhubung dengan Sistem Informasi Keuangan Organisasi.

📌 Command tersedia:

/saldo - Cek saldo kas
/help - Bantuan";

telegram_send_message($chatId, $text);