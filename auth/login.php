<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth-helper.php';
require_once __DIR__ . '/../helpers/query-helper.php';
require_once __DIR__ . '/../helpers/telegram-helper.php';

$error = '';
$timeout = isset($_GET['timeout']);
$pdo = db();
$hasUser = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$settings = get_pengaturan($pdo);
$logoPath = $settings['logo'] ?? null;
$logoSrc = $logoPath ? base_url($logoPath) : base_url('assets/img/karang-taruna-logo.png');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Token keamanan tidak valid.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Username dan password wajib diisi.';
        } else {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username AND status = :status LIMIT 1');
            $stmt->execute(['username' => $username, 'status' => 'aktif']);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);

                if (!empty($user['telegram_chat_id'])) {
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

                    if (!kirim_telegram_otp_login($user['telegram_chat_id'], $user['nama'], $otp)) {
                        $error = 'Gagal mengirim kode OTP Telegram. Coba lagi.';
                    } else {
                        unset($_SESSION['user']);
                        $_SESSION['temp_user_id'] = $user['id'];
                        $_SESSION['temp_otp_sent_at'] = time();

                        header('Location: ' . base_url('auth/verifikasi-otp.php'));
                        exit;
                    }
                } else {
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'nama' => $user['nama'],
                        'username' => $user['username'],
                        'role' => $user['role'],
                        'foto' => $user['foto'],
                    ];

                    header('Location: ' . base_url('pages/dashboard/index.php'));
                    exit;
                }
            }

            $error = 'Username atau password salah.';
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - <?php echo htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-page">
<div class="auth-card">
    <div class="auth-header">
        <div class="auth-logo">
            <img src="<?php echo htmlspecialchars($logoSrc, ENT_QUOTES, 'UTF-8'); ?>" alt="Logo Karang Taruna">
        </div>
        <h1 class="auth-title">KARANG TARUNA 001 LEBAK BULUS</h1>
        <p class="auth-subtitle">Silakan login untuk melanjutkan.</p>
    </div>
    <?php if ($hasUser === 0) : ?>
        <div class="alert alert-warning">
            Belum ada user. Silakan <a href="<?php echo base_url('auth/setup.php'); ?>">buat admin pertama</a>.
        </div>
    <?php endif; ?>
    <?php if ($timeout) : ?>
        <div class="alert alert-warning">Sesi berakhir karena tidak ada aktivitas.</div>
    <?php endif; ?>
    <?php if ($error) : ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <form method="post" action="" class="auth-form">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
        <label>
            Username
            <input type="text" name="username" required>
        </label>
        <label>
            Password
            <input type="password" name="password" required>
        </label>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
</div>
</body>
</html>
