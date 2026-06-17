<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth-helper.php';

$pdo = db();
$count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();

if ($count > 0) {
    http_response_code(403);
    exit('Setup sudah dilakukan.');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Token keamanan tidak valid.';
    } else {
        $nama = trim($_POST['nama'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($nama === '' || $username === '' || $password === '') {
            $error = 'Semua field wajib diisi.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('
                INSERT INTO users (nama, username, password, role, status)
                VALUES (:nama, :username, :password, :role, :status)
            ');
            $stmt->execute([
                'nama' => $nama,
                'username' => $username,
                'password' => $hash,
                'role' => 'bendahara',
                'status' => 'aktif',
            ]);
            header('Location: ' . base_url('auth/login.php'));
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Setup Admin - <?php echo htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
</head>
<body class="auth-page">
<div class="auth-card">
    <h1>Setup Admin</h1>
    <p>Buat akun bendahara pertama untuk mengakses sistem.</p>
    <?php if ($error) : ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <form method="post" data-validate>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
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
        <button type="submit" class="btn btn-primary">Buat Admin</button>
    </form>
</div>
<script src="<?php echo base_url('assets/js/validation.js'); ?>"></script>
</body>
</html>
