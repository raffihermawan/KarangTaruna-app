<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';

require_role(['bendahara', 'ketua']);

$pdo = db();
$id = (int) ($_GET['id'] ?? 0);
$error = '';

$stmt = $pdo->prepare('SELECT id, nama, username, role, status FROM users WHERE id = :id');
$stmt->execute(['id' => $id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    exit('User tidak ditemukan.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Token keamanan tidak valid.';
    } else {
        $nama = trim($_POST['nama'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'anggota';
        $status = $_POST['status'] ?? 'aktif';

        if ($nama === '' || $username === '') {
            $error = 'Nama dan username wajib diisi.';
        } else {
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :username AND id != :id');
            $stmt->execute(['username' => $username, 'id' => $id]);
            if ($stmt->fetchColumn() > 0) {
                $error = 'Username sudah digunakan.';
            } else {
                $fields = [
                    'nama' => $nama,
                    'username' => $username,
                    'role' => $role,
                    'status' => $status,
                    'id' => $id,
                ];
                $sql = 'UPDATE users SET nama = :nama, username = :username, role = :role, status = :status';
                if ($password !== '') {
                    $sql .= ', password = :password';
                    $fields['password'] = password_hash($password, PASSWORD_BCRYPT);
                }
                $sql .= ' WHERE id = :id';

                $update = $pdo->prepare($sql);
                $update->execute($fields);
                set_flash('success', 'User berhasil diperbarui.');
                header('Location: ' . base_url('pages/user/index.php'));
                exit;
            }
        }
    }
}

$page_title = 'Edit User';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <div class="card">
        <div class="card-title">Edit User</div>
        <?php if ($error) : ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <form method="post" data-validate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-grid">
                <label>
                    Nama
                    <input type="text" name="nama" value="<?php echo htmlspecialchars($user['nama'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </label>
                <label>
                    Username
                    <input type="text" name="username" value="<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </label>
                <label>
                    Password Baru (Opsional)
                    <input type="password" name="password">
                </label>
                <label>
                    Role
                    <select name="role">
                        <option value="bendahara" <?php echo $user['role'] === 'bendahara' ? 'selected' : ''; ?>>Bendahara</option>
                        <option value="ketua" <?php echo $user['role'] === 'ketua' ? 'selected' : ''; ?>>Ketua</option>
                        <option value="anggota" <?php echo $user['role'] === 'anggota' ? 'selected' : ''; ?>>Anggota</option>
                    </select>
                </label>
                <label>
                    Status
                    <select name="status">
                        <option value="aktif" <?php echo $user['status'] === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="nonaktif" <?php echo $user['status'] === 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                    </select>
                </label>
            </div>
            <div class="actions">
                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="<?php echo base_url('pages/user/index.php'); ?>" class="btn">Batal</a>
            </div>
        </form>
    </div>
</div>
<script src="<?php echo base_url('assets/js/validation.js'); ?>"></script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
