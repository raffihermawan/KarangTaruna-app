<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';

require_role(['bendahara', 'ketua']);

$pdo = db();
$users = $pdo->query('SELECT id, nama, username, role, status FROM users ORDER BY nama ASC')->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'User';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <?php include __DIR__ . '/../../includes/alert.php'; ?>
    <div class="card">
        <div class="card-title">Manajemen User</div>
        <div class="actions">
            <a href="<?php echo base_url('pages/user/tambah.php'); ?>" class="btn btn-success">Tambah User</a>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$users) : ?>
                    <tr><td colspan="5">Belum ada user.</td></tr>
                <?php else : ?>
                    <?php foreach ($users as $user) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['nama'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($user['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="actions">
                                <a href="<?php echo base_url('pages/user/edit.php?id=' . $user['id']); ?>" class="btn btn-primary">Edit</a>
                                <form method="post" action="<?php echo base_url('pages/user/hapus.php'); ?>"
                                      data-confirm="Nonaktifkan user ini?"
                                      data-confirm-title="Nonaktifkan User"
                                      data-confirm-ok="Ya, nonaktifkan"
                                      data-confirm-icon="warning">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn btn-danger">Nonaktifkan</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
