<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/query-helper.php';
require_once __DIR__ . '/../helpers/telegram-helper.php';

$botToken = telegram_bot_token();

if ($botToken === '') {
    http_response_code(500);
    exit('Telegram token belum dikonfigurasi.');
}

/*
|--------------------------------------------------------------------------
| AMBIL DATA DARI TELEGRAM
|--------------------------------------------------------------------------
*/
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['message'])) {
    exit;
}

$chatId = $data['message']['chat']['id'];
$message = trim($data['message']['text'] ?? '');
$firstName = trim((string) ($data['message']['from']['first_name'] ?? 'Pengguna'));

if (strpos($message, '/start') === 0) {
    $pdo = db();
    $parts = explode(' ', $message, 2);
    $reply = '';

    if (isset($parts[1]) && strpos($parts[1], 'hubungkan_') === 0) {
        $userId = (int) str_replace('hubungkan_', '', $parts[1]);

        if ($userId > 0) {
            $stmt = $pdo->prepare('UPDATE users SET telegram_chat_id = :chat_id WHERE id = :user_id');
            $stmt->execute([
                'chat_id' => (string) $chatId,
                'user_id' => $userId,
            ]);

            if ($stmt->rowCount() > 0) {
                $reply = "🎉 <b>KONEKSI TELEGRAM BERHASIL!</b>\n\nHalo " . $firstName . ",\nAkun Telegram Anda sekarang resmi terhubung dengan sistem website Karang Taruna.\n\nKode keamanan OTP Login dan Log Transaksi akan dikirimkan ke akun ini.";
            } else {
                $reply = "⚠️ <b>Gagal Menghubungkan:</b> Gagal memperbarui data. Akun mungkin sudah terhubung atau ID tidak ditemukan.";
            }
        } else {
            $reply = "⚠️ <b>Gagal Menghubungkan:</b> parameter deep link tidak valid.";
        }
    } else {
        $reply = "Halo " . $firstName . "!\nSelamat datang di Bot Monitoring Kas Karang Taruna.\n\nUntuk menyambungkan notifikasi OTP personal, silakan masuk ke dashboard website Anda, buka menu 'Profil', lalu klik tombol 'Hubungkan ke Telegram'.";
    }

    telegram_send_message($chatId, $reply, 'HTML');
    exit;
}

/*
|--------------------------------------------------------------------------
| LOAD COMMAND
|--------------------------------------------------------------------------
*/
switch ($message) {

    case '/start':
        require __DIR__ . '/commands/start.php';
        break;

    case '/help':
        require __DIR__ . '/commands/help.php';
        break;

    case '/saldo':
        require __DIR__ . '/commands/saldo.php';
        break;

    default:
        kirimPesan(
            $botToken,
            $chatId,
            "❌ Command tidak dikenal.\n\nKetik /help untuk melihat daftar command."
        );
        break;
}


/*
|--------------------------------------------------------------------------
| FUNCTION SEND MESSAGE
|--------------------------------------------------------------------------
*/
function kirimPesan($token, $chatId, $text)
{
    $url = "https://api.telegram.org/bot{$token}/sendMessage";

    $data = [
        'chat_id' => $chatId,
        'text' => $text,
    ];

    $options = [
        'http' => [
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
        ]
    ];

    $context = stream_context_create($options);

    @file_get_contents($url, false, $context);
}