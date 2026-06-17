<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';

require_login();

$currentUser = current_user() ?: [];
$pdo = db();
$stmt = $pdo->prepare('SELECT id, nama, username, role, foto, telegram_chat_id FROM users WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $currentUser['id'] ?? 0]);
$user = $stmt->fetch(PDO::FETCH_ASSOC) ?: $currentUser;

$role = $user['role'] ?? '';
$roleLabel = $role === 'bendahara' ? 'Bendahara' : ($role === 'ketua' ? 'Ketua' : 'Anggota');
$username = $user['username'] ?? '-';
$email = strpos($username, '@') !== false ? $username : '-';
$telegramChatId = trim((string) ($user['telegram_chat_id'] ?? ''));
$telegramLink = 'https://t.me/BotKarangTaruna_bot?start=hubungkan_' . (int) ($user['id'] ?? 0);

$page_title = 'Profil';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <div class="card">
        <div class="card-title">Profil Pengguna</div>
        <table class="table">
            <tr><th>Nama</th><td><?php echo htmlspecialchars($user['nama'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td></tr>
            <tr><th>Jabatan</th><td><?php echo htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8'); ?></td></tr>
            <tr><th>Username</th><td><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></td></tr>
            <tr><th>Email</th><td><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></td></tr>
        </table>
    </div>

    <div class="card" style="margin-top: 18px;">
        <div class="card-title">Integrasi Notifikasi Telegram</div>
        <?php if ($telegramChatId === '') : ?>
            <p class="text-muted" style="font-size: 13px; margin-top: 0;">
                Hubungkan akun Anda dengan Telegram Bot untuk menerima notifikasi login keamanan dan kode OTP 2FA secara real-time.
            </p>
            <a href="<?php echo htmlspecialchars($telegramLink, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn btn-primary">
                Hubungkan ke Telegram
            </a>
        <?php else : ?>
            <div class="alert alert-success" style="margin-bottom: 0;">
                <div>
                    <strong>Akun Terhubung!</strong> (Chat ID: <?php echo htmlspecialchars($telegramChatId, ENT_QUOTES, 'UTF-8'); ?>)
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
