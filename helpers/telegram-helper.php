<?php


function telegram_bot_token(): string
{
    $token = $_ENV['TELEGRAM_TOKEN'] ?? getenv('TELEGRAM_TOKEN');
    if ($token !== false && trim($token) !== '') {
        return trim($token);
    }

    $legacyToken = $_ENV['TELEGRAM_BOT_TOKEN'] ?? getenv('TELEGRAM_BOT_TOKEN');
    if ($legacyToken !== false && trim($legacyToken) !== '') {
        return trim($legacyToken);
    }

    return '';
}

function telegram_send_message($chatId, string $message, ?string $parseMode = null): bool
{
    $botToken = telegram_bot_token();
    if (trim((string) $chatId) === '' || $botToken === '') {
        return false;
    }

    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
    $data = [
        'chat_id' => $chatId,
        'text' => $message,
    ];

    if ($parseMode !== null && $parseMode !== '') {
        $data['parse_mode'] = $parseMode;
    }

    $options = [
        'http' => [
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
            'timeout' => 10,
        ]
    ];

    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);

    return $result !== false;
}

function kirim_telegram_login($chatId, $nama, $role)
{
    date_default_timezone_set('Asia/Jakarta');

    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | DETEKSI BROWSER
    |--------------------------------------------------------------------------
    */
    $browser = 'Tidak diketahui';

    // Opera harus di atas Chrome karena user-agent Opera mengandung "Chrome"
    if (strpos($userAgent, 'OPR') !== false || strpos($userAgent, 'Opera') !== false) {
        $browser = 'Opera';
    } elseif (strpos($userAgent, 'Edg') !== false) {
        $browser = 'Microsoft Edge';
    } elseif (strpos($userAgent, 'Chrome') !== false) {
        $browser = 'Chrome';
    } elseif (strpos($userAgent, 'Firefox') !== false) {
        $browser = 'Firefox';
    } elseif (
        strpos($userAgent, 'Safari') !== false &&
        strpos($userAgent, 'Chrome') === false
    ) {
        $browser = 'Safari';
    }

    /*
    |--------------------------------------------------------------------------
    | DETEKSI DEVICE / OS
    |--------------------------------------------------------------------------
    */
    $device = 'Tidak diketahui';

    if (strpos($userAgent, 'Windows NT 10.0') !== false) {
        $device = 'Windows 10';
    } elseif (strpos($userAgent, 'Windows NT 11.0') !== false) {
        $device = 'Windows 11';
    } elseif (strpos($userAgent, 'Android') !== false) {
        $device = 'Android';
    } elseif (strpos($userAgent, 'iPhone') !== false) {
        $device = 'iPhone (iOS)';
    } elseif (strpos($userAgent, 'iPad') !== false) {
        $device = 'iPad (iOS)';
    } elseif (strpos($userAgent, 'Mac OS X') !== false) {
        $device = 'MacOS';
    } elseif (strpos($userAgent, 'Linux') !== false) {
        $device = 'Linux';
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';

    // Kalau localhost (::1) biar lebih enak dibaca
    if ($ip === '::1') {
        $ip = 'Localhost';
    }

    $tanggal = date('d F Y');
    $jam = date('H:i') . ' WIB';

    /*
    |--------------------------------------------------------------------------
    | TEMPLATE PESAN TELEGRAM
    |--------------------------------------------------------------------------
    */
    $message = "🔐 KARANG TARUNA LOGIN\n\n";
    $message .= "Kami mendeteksi adanya aktivitas login pada akun Anda.\n\n";
    $message .= "👤 Akun: {$nama}\n";
    $message .= "🛡 Role: {$role}\n";
    $message .= "🌐 Browser: {$browser}\n";
    $message .= "💻 Device: {$device}\n";
    $message .= "🌍 IP Address: {$ip}\n";
    $message .= "📅 Tanggal: {$tanggal}\n";
    $message .= "🕒 Jam: {$jam}\n\n";
    $message .= "Jika ini bukan Anda, segera ubah password akun.";

    telegram_send_message($chatId, $message);
}

function kirim_telegram_otp_login($chatId, string $nama, string $otp): bool
{
    date_default_timezone_set('Asia/Jakarta');

    $message = "🔐 <b>KODE VERIFIKASI OTP LOGIN</b>\n\n";
    $message .= "Halo <b>" . htmlspecialchars($nama, ENT_QUOTES, 'UTF-8') . "</b>, berikut kode OTP Anda untuk login ke Sistem Karang Taruna:\n\n";
    $message .= "<b>{$otp}</b>\n\n";
    $message .= "Kode ini berlaku selama 5 menit. Jangan bagikan kode ini kepada siapa pun.";

    return telegram_send_message($chatId, $message, 'HTML');
}

function kirim_telegram_transaksi(
    $chatId,
    $jenis,
    $jumlah,
    $kategori,
    $kegiatan,
    $inputBy
) {
    date_default_timezone_set('Asia/Jakarta');

    $tanggal = date('d F Y');
    $jam = date('H:i') . ' WIB';

    $icon = $jenis === 'pemasukan' ? '🟢' : '🔴';

    $message = "💰 TRANSAKSI KEUANGAN\n\n";
    $message .= "Terdapat transaksi baru pada sistem.\n\n";
    $message .= "{$icon} Jenis: " . ucfirst($jenis) . "\n";
    $message .= "💵 Nominal: Rp" . number_format($jumlah, 0, ',', '.') . "\n";
    $message .= "📂 Kategori: {$kategori}\n";
    $message .= "📝 Kegiatan: " . ($kegiatan ?: '-') . "\n";
    $message .= "👤 Diinput oleh: {$inputBy}\n";
    $message .= "📅 Tanggal: {$tanggal}\n";
    $message .= "🕒 Jam: {$jam}\n\n";
    $message .= "Silakan cek dashboard untuk detail lebih lanjut.";

    telegram_send_message($chatId, $message);
}