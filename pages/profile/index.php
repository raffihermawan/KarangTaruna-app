<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';
require_once __DIR__ . '/../../helpers/query-helper.php';

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
$organization = get_pengaturan($pdo);
$organizationLogo = trim((string) ($organization['logo'] ?? ''));
$organizationLogo = $organizationLogo !== '' ? base_url($organizationLogo) : '';

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

<div class="card" style="margin-top: 18px;">
    <div class="card-title">Profil Organisasi</div>
    <?php if ($organizationLogo !== '') : ?>
        <div style="margin-bottom: 16px; text-align: center;">
            <img src="<?php echo htmlspecialchars($organizationLogo, ENT_QUOTES, 'UTF-8'); ?>" alt="Logo organisasi" style="max-width: 120px; max-height: 120px; object-fit: contain;">
        </div>
    <?php endif; ?>
    <table class="table">
        <tr><th>Nama Organisasi</th><td><?php echo htmlspecialchars($organization['nama_organisasi'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td></tr>
        <tr><th>Alamat</th><td><?php echo htmlspecialchars($organization['alamat'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td></tr>
        <tr><th>Email</th><td><?php echo htmlspecialchars($organization['email'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td></tr>
        <tr><th>Bank</th><td><?php echo htmlspecialchars($organization['nama_bank'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td></tr>
        <tr><th>Nomor Rekening</th><td><?php echo htmlspecialchars($organization['no_rekening'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td></tr>
        <tr><th>Tahun Aktif</th><td><?php echo htmlspecialchars($organization['tahun_aktif'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td></tr>
        <tr><th>Ketua</th><td><?php echo htmlspecialchars($organization['nama_ketua'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td></tr>
        <tr><th>Bendahara</th><td><?php echo htmlspecialchars($organization['nama_bendahara'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td></tr>
    </table>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
