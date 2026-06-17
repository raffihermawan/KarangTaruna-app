<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';

require_login();

$pdo = db();
$error = '';
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Token keamanan tidak valid.';
    } else {
        $oldPassword = $_POST['password_lama'] ?? '';
        $newPassword = $_POST['password_baru'] ?? '';
        $confirmPassword = $_POST['konfirmasi_password'] ?? '';

        if ($oldPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $error = 'Semua field wajib diisi.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'Konfirmasi password tidak sama.';
        } else {
            $stmt = $pdo->prepare('SELECT password FROM users WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $user['id']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || !password_verify($oldPassword, $row['password'])) {
                $error = 'Password lama tidak sesuai.';
            } else {
                $hash = password_hash($newPassword, PASSWORD_BCRYPT);
                $update = $pdo->prepare('UPDATE users SET password = :password WHERE id = :id');
                $update->execute(['password' => $hash, 'id' => $user['id']]);
                set_flash('success', 'Password berhasil diperbarui.');
                header('Location: ' . base_url('pages/security/change-password.php'));
                exit;
            }
        }
    }
}

$page_title = 'Ganti Kata Sandi';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <?php include __DIR__ . '/../../includes/alert.php'; ?>
    <div class="card">
        <div class="card-title">Ganti Kata Sandi</div>
        <?php if ($error) : ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <form method="post" data-validate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <label>
                Password Lama
                <input type="password" name="password_lama" required>
            </label>
            <label>
                Password Baru
                <input type="password" name="password_baru" required>
            </label>
            <label>
                Konfirmasi Password Baru
                <input type="password" name="konfirmasi_password" required>
            </label>
            <div class="actions">
                <button type="submit" class="btn btn-success">Perbarui</button>
            </div>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
