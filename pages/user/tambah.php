<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';

require_role(['bendahara', 'ketua']);

$pdo = db();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Token keamanan tidak valid.';
    } else {
        $nama = trim($_POST['nama'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'anggota';
        $status = $_POST['status'] ?? 'aktif';

        if ($nama === '' || $username === '' || $password === '') {
            $error = 'Nama, username, dan password wajib diisi.';
        } elseif (!in_array($role, ['bendahara', 'ketua', 'anggota'], true)) {
            $error = 'Role user tidak valid.';
        } elseif (!in_array($status, ['aktif', 'nonaktif'], true)) {
            $error = 'Status user tidak valid.';
        } else {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username');
            $stmt->execute(['username' => $username]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Username sudah digunakan.';
            } else {
                $hash = password_hash($password, PASSWORD_BCRYPT);
                $insert = $pdo->prepare('
                    INSERT INTO users (nama, username, password, role, status)
                    VALUES (:nama, :username, :password, :role, :status)
                ');
                $insert->execute([
                    'nama' => $nama,
                    'username' => $username,
                    'password' => $hash,
                    'role' => $role,
                    'status' => $status,
                ]);
                set_flash('success', 'User berhasil ditambahkan.');
                header('Location: ' . base_url('pages/user/index.php'));
                exit;
            }
        }
    }
}

$page_title = 'Tambah User';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <div class="card">
        <div class="card-title">Tambah User</div>
        <?php if ($error) : ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <form method="post" data-validate class="user-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-grid">
                <label>
                    Nama
                    <input type="text" name="nama" required>
                </label>
                <label>
                    Username
                    <input type="text" name="username" required>
                </label>
                <label>
                    Password
                    <input type="password" name="password" required>
                </label>
                <label>
                    Role
                    <select name="role">
                        <option value="bendahara">Bendahara</option>
                        <option value="ketua">Ketua</option>
                        <option value="anggota">Anggota</option>
                    </select>
                </label>
                <label>
                    Status
                    <select name="status">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </label>
            </div>
            <div class="actions user-actions">
                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="<?php echo base_url('pages/user/index.php'); ?>" class="btn">Batal</a>
            </div>
        </form>
    </div>
</div>
<script src="<?php echo base_url('assets/js/validation.js'); ?>"></script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
