<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';
require_once __DIR__ . '/../../helpers/format-helper.php';
require_once __DIR__ . '/../../helpers/query-helper.php';

require_role(['ketua']);

$pdo = db();
$currentUser = current_user() ?: [];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Token keamanan tidak valid.';
    } else {
        $action = $_POST['action'] ?? '';
        $id = (int) ($_POST['id'] ?? 0);
        $note = trim($_POST['catatan_penolakan'] ?? '');

        $stmt = $pdo->prepare('SELECT * FROM transaksi WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $transaksi = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$transaksi) {
            $error = 'Transaksi tidak ditemukan.';
        } elseif (($transaksi['status'] ?? 'pending') !== 'pending') {
            $error = 'Hanya transaksi pending yang dapat diproses.';
        } elseif ((int) ($transaksi['created_by'] ?? 0) === (int) ($currentUser['id'] ?? 0)) {
            $error = 'Anda tidak dapat memproses transaksi yang Anda buat sendiri.';
        } else {
            $pdo->beginTransaction();
            try {
                if ($action === 'approve') {
                    $update = $pdo->prepare('
                        UPDATE transaksi
                        SET status = "approved",
                            approved_by = :approved_by,
                            approved_at = NOW(),
                            catatan_penolakan = NULL
                        WHERE id = :id
                    ');
                    $update->execute([
                        'approved_by' => $currentUser['id'],
                        'id' => $id,
                    ]);

                    $delta = $transaksi['jenis'] === 'pemasukan' ? (float) $transaksi['jumlah'] : -(float) $transaksi['jumlah'];
                    update_saldo($pdo, $delta);
                    set_flash('success', 'Transaksi berhasil di-approve.');
                } elseif ($action === 'reject') {
                    $update = $pdo->prepare('
                        UPDATE transaksi
                        SET status = "rejected",
                            approved_by = :approved_by,
                            approved_at = NOW(),
                            catatan_penolakan = :catatan_penolakan
                        WHERE id = :id
                    ');
                    $update->execute([
                        'approved_by' => $currentUser['id'],
                        'catatan_penolakan' => $note !== '' ? $note : null,
                        'id' => $id,
                    ]);
                    set_flash('success', 'Transaksi berhasil ditolak.');
                } else {
                    throw new RuntimeException('Aksi tidak valid.');
                }

                $pdo->commit();
                header('Location: ' . base_url('pages/transaksi/persetujuan.php'));
                exit;
            } catch (Throwable $e) {
                $pdo->rollBack();
                $error = $e->getMessage();
            }
        }
    }
}

$statusFilter = $_GET['status'] ?? 'pending';
$where = [];
$params = [];
if (in_array($statusFilter, ['pending', 'approved', 'rejected'], true)) {
    $where[] = 'COALESCE(t.status, "approved") = :status';
    $params['status'] = $statusFilter;
}

$sql = 'SELECT t.*, k.nama AS kategori_nama, keg.nama AS kegiatan_nama, u.nama AS approved_by_name
        FROM transaksi t
        JOIN kategori_transaksi k ON k.id = t.kategori_id
        LEFT JOIN kegiatan keg ON keg.id = t.kegiatan_id
        LEFT JOIN users u ON u.id = t.approved_by';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY t.tanggal DESC, t.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transaksiList = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = 'Persetujuan Transaksi';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <?php include __DIR__ . '/../../includes/alert.php'; ?>
    <div class="card">
        <div class="card-title">Persetujuan Transaksi</div>
        <?php if ($error) : ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <form method="get" class="filters transaksi-filters" style="margin-bottom: 16px;">
            <label>
                Status
                <select name="status">
                    <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="approved" <?php echo $statusFilter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?php echo $statusFilter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>Semua</option>
                </select>
            </label>
            <button type="submit" class="btn btn-primary transaksi-btn">Terapkan</button>
            <a href="<?php echo base_url('pages/transaksi/persetujuan.php'); ?>" class="btn transaksi-btn">Reset</a>
        </form>
        <div class="table-wrap transaksi-table-wrap">
        <table class="table transaksi-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th>Kategori</th>
                    <th>Kegiatan</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$transaksiList) : ?>
                    <tr><td colspan="7">Tidak ada data transaksi.</td></tr>
                <?php else : ?>
                    <?php foreach ($transaksiList as $trx) : ?>
                        <?php
                            $status = $trx['status'] ?? 'approved';
                            $badgeClass = $status === 'approved' ? 'badge-success' : ($status === 'rejected' ? 'badge-danger' : 'badge-warning');
                        ?>
                        <tr>
                            <td><?php echo format_tanggal($trx['tanggal']); ?></td>
                            <td><?php echo htmlspecialchars($trx['jenis'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($trx['kategori_nama'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($trx['kegiatan_nama'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo format_rupiah($trx['jumlah']); ?></td>
                            <td>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php if (!empty($trx['approved_by_name'])) : ?>
                                    <div style="margin-top:6px;font-size:12px;color:var(--muted);">
                                        Oleh: <?php echo htmlspecialchars($trx['approved_by_name'], ENT_QUOTES, 'UTF-8'); ?><br>
                                        <?php echo !empty($trx['approved_at']) ? htmlspecialchars(date('d M Y H:i', strtotime($trx['approved_at'])), ENT_QUOTES, 'UTF-8') : ''; ?>
                                        <?php if ($status === 'rejected' && !empty($trx['catatan_penolakan'])) : ?>
                                            <br><?php echo nl2br(htmlspecialchars($trx['catatan_penolakan'], ENT_QUOTES, 'UTF-8')); ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <a href="<?php echo base_url('pages/transaksi/detail.php?id=' . $trx['id']); ?>" class="btn">Detail</a>
                                <?php if ($status === 'pending') : ?>
                                    <form method="post" style="display:inline-flex;gap:8px;align-items:center;flex-wrap:wrap;">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="id" value="<?php echo (int) $trx['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-success">Approve</button>
                                    </form>
                                    <button type="button" class="btn btn-danger" data-reject-open="reject-<?php echo (int) $trx['id']; ?>">Tolak</button>
                                    <dialog id="reject-<?php echo (int) $trx['id']; ?>" style="border:none;border-radius:16px;padding:0;max-width:520px;width:90%;box-shadow:var(--shadow-md);">
                                        <form method="post" style="padding:20px;min-width:320px;">
                                            <h3 style="margin-top:0;">Tolak Transaksi</h3>
                                            <p style="margin-top:0;color:var(--muted);">Tambahkan catatan penolakan jika diperlukan.</p>
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="id" value="<?php echo (int) $trx['id']; ?>">
                                            <input type="hidden" name="action" value="reject">
                                            <label style="display:block;margin-bottom:16px;">
                                                Catatan Penolakan
                                                <textarea name="catatan_penolakan" rows="4" style="width:100%;"></textarea>
                                            </label>
                                            <div class="actions" style="justify-content:flex-end;">
                                                <button type="button" class="btn" data-reject-close>Tutup</button>
                                                <button type="submit" class="btn btn-danger">Tolak</button>
                                            </div>
                                        </form>
                                    </dialog>
                                <?php else : ?>
                                    <a href="<?php echo base_url('pages/transaksi/detail.php?id=' . $trx['id']); ?>" class="btn">Detail</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<script>
(function () {
    document.querySelectorAll('[data-reject-open]').forEach(function (button) {
        button.addEventListener('click', function () {
            var dialog = document.getElementById(button.getAttribute('data-reject-open'));
            if (dialog && dialog.showModal) dialog.showModal();
        });
    });
    document.querySelectorAll('[data-reject-close]').forEach(function (button) {
        button.addEventListener('click', function () {
            var dialog = button.closest('dialog');
            if (dialog && dialog.close) dialog.close();
        });
    });
})();
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
