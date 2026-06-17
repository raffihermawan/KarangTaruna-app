<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth-helper.php';

$pdo = db();
$userCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$role = current_user()['role'] ?? '';

if ($userCount > 0 && $role !== 'bendahara') {
    http_response_code(403);
    exit('Seed hanya bisa dijalankan oleh bendahara.');
}

$defaults = [
    ['nama' => 'Bendahara', 'username' => 'bendahara', 'password' => 'bendahara001', 'role' => 'bendahara'],
    ['nama' => 'Ketua', 'username' => 'ketua', 'password' => 'ketua001', 'role' => 'ketua'],
    ['nama' => 'Anggota', 'username' => 'anggota', 'password' => 'anggota001', 'role' => 'anggota'],
];

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Token keamanan tidak valid.';
    } else {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('
                INSERT INTO users (nama, username, password, role, status)
                VALUES (:nama, :username, :password, :role, :status)
                ON DUPLICATE KEY UPDATE
                    nama = VALUES(nama),
                    password = VALUES(password),
                    role = VALUES(role),
                    status = VALUES(status)
            ');

            foreach ($defaults as $user) {
                $stmt->execute([
                    'nama' => $user['nama'],
                    'username' => $user['username'],
                    'password' => password_hash($user['password'], PASSWORD_BCRYPT),
                    'role' => $user['role'],
                    'status' => 'aktif',
                ]);
            }

            $pdo->commit();
            $success = 'User default berhasil dibuat.';
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seed Users</title>
    <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
</head>
<body class="auth-page">
<div class="auth-card">
    <h1>Seed Users Default</h1>
    <p>Ini akan membuat akun: bendahara, ketua, dan anggota.</p>
    <?php if ($success) : ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <?php if ($error) : ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
        <button type="submit" class="btn btn-primary">Buat User Default</button>
    </form>
</div>
</body>
</html>
