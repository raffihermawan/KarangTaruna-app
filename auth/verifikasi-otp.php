<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth-helper.php';
require_once __DIR__ . '/../helpers/query-helper.php';

require_once __DIR__ . '/../helpers/telegram-helper.php';

$pdo = db();
$tempUserId = (int) ($_SESSION['temp_user_id'] ?? 0);

if ($tempUserId <= 0) {
    header('Location: ' . base_url('auth/login.php'));
    exit;
}

$stmt = $pdo->prepare('SELECT id, nama, username, role, foto, otp_code, otp_expiry FROM users WHERE id = :id AND status = :status LIMIT 1');
$stmt->execute([
    'id' => $tempUserId,
    'status' => 'aktif',
]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    unset($_SESSION['temp_user_id'], $_SESSION['temp_otp_sent_at']);
    header('Location: ' . base_url('auth/login.php'));
    exit;
}

$error = '';
$success = '';
$canResend = empty($_SESSION['temp_otp_sent_at']) || (time() - (int) $_SESSION['temp_otp_sent_at']) > 60;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Token keamanan tidak valid.';
    } else {
        $inputOtp = trim($_POST['otp_code'] ?? '');

        if ($inputOtp === '' || !preg_match('/^\d{6}$/', $inputOtp)) {
            $error = 'OTP harus berupa 6 digit angka.';
        } elseif (empty($user['otp_code']) || empty($user['otp_expiry'])) {
            $error = 'OTP tidak tersedia. Silakan login ulang.';
        } elseif (strtotime($user['otp_expiry']) < time()) {
            $error = 'Kode OTP sudah kedaluwarsa. Silakan login ulang.';
        } elseif (!password_verify($inputOtp, (string) $user['otp_code'])) {
            $error = 'Kode OTP salah.';
        } else {
            $clearStmt = $pdo->prepare('
                UPDATE users
                SET otp_code = NULL,
                    otp_expiry = NULL
                WHERE id = :id
            ');
            $clearStmt->execute(['id' => $user['id']]);

            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => $user['id'],
                'nama' => $user['nama'],
                'username' => $user['username'],
                'role' => $user['role'],
                'foto' => $user['foto'],
            ];
            unset($_SESSION['temp_user_id'], $_SESSION['temp_otp_sent_at']);

            header('Location: ' . base_url('pages/dashboard/index.php'));
            exit;
        }
    }
}

if (isset($_GET['resend']) && $canResend && !empty($user['telegram_chat_id'])) {
    $otp = (string) random_int(100000, 999999);
    $expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));
    $otpHash = password_hash($otp, PASSWORD_DEFAULT);

    $otpStmt = $pdo->prepare('
        UPDATE users
        SET otp_code = :otp_code,
            otp_expiry = :otp_expiry
        WHERE id = :id
    ');
    $otpStmt->execute([
        'otp_code' => $otpHash,
        'otp_expiry' => $expiry,
        'id' => $user['id'],
    ]);

    kirim_telegram_otp_login($user['telegram_chat_id'], $user['nama'], $otp);
    $_SESSION['temp_otp_sent_at'] = time();
    $success = 'Kode OTP baru sudah dikirim ke Telegram Anda.';
}

$page_title = 'Verifikasi OTP';
$settings = get_pengaturan($pdo);
$logoPath = $settings['logo'] ?? null;
$logoSrc = $logoPath ? base_url($logoPath) : base_url('assets/img/karang-taruna-logo.png');
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi OTP - <?php echo htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
</head>
<body class="auth-page">
<div class="auth-card">
    <div class="auth-header">
        <div class="auth-logo">
            <img src="<?php echo htmlspecialchars($logoSrc, ENT_QUOTES, 'UTF-8'); ?>" alt="Logo Karang Taruna">
        </div>
        <h1 class="auth-title">Verifikasi Keamanan</h1>
        <p class="auth-subtitle">Kode OTP sudah dikirim ke Telegram Anda. Masukkan 6 digit kode di bawah ini.</p>
    </div>

    <?php if ($success) : ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if ($error) : ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form method="post" class="auth-form">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
        <label>
            Kode OTP
            <input type="text" name="otp_code" inputmode="numeric" maxlength="6" placeholder="000000" autocomplete="one-time-code" required>
        </label>
        <button type="submit" class="btn btn-primary">Verifikasi & Login</button>
    </form>

    <div class="actions" style="margin-top: 14px;">
        <?php if ($canResend && !empty($user['telegram_chat_id'])) : ?>
            <a href="<?php echo base_url('auth/verifikasi-otp.php?resend=1'); ?>" class="btn">Kirim Ulang OTP</a>
        <?php endif; ?>
        <a href="<?php echo base_url('auth/login.php'); ?>" class="btn btn-danger">Batal</a>
    </div>
</div>
</body>
</html>