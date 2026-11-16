<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';
require_once __DIR__ . '/../../helpers/format-helper.php';

require_login();

$pdo = db();
$user = current_user();
$role = $user['role'] ?? '';

if (!in_array($role, ['anggota', 'ketua'])) {
    header('Location: ' . base_url('pages/dashboard/index.php'));
    exit;
}

$id_saran = (int)($_GET['id'] ?? 0);

if ($role === 'anggota') {
    $stmt = $pdo->prepare('SELECT * FROM saran WHERE id_saran = :id');
    $stmt->execute(['id' => $id_saran]);
} else {
    // Ketua
    $stmt = $pdo->prepare('SELECT * FROM saran WHERE id_saran = :id');
    $stmt->execute(['id' => $id_saran]);
    
    // Mark as read if not yet
    $pdo->prepare('UPDATE saran SET status_baca = 1 WHERE id_saran = :id AND status_baca = 0')->execute(['id' => $id_saran]);
}

$saran = $stmt->fetch();

if (!$saran) {
    header('Location: ' . base_url('pages/saran/index.php'));
    exit;
}

$page_title = 'Detail Masukan & Saran';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <div class="card">
        <div class="card-title">Perihal: <?php echo htmlspecialchars($saran['perihal'], ENT_QUOTES, 'UTF-8'); ?></div>
        
        <div class="meta-info" style="margin-bottom: 20px; color: #666; font-size: 0.9em;">
            <strong>Tanggal Kirim:</strong> <?php echo format_tanggal($saran['tgl_kirim']); ?><br>
            <?php if ($role === 'ketua'): ?>
                <strong>Pengirim:</strong> Anggota #<?php echo $saran['id_anggota']; ?><br>
            <?php endif; ?>
            <strong>Status:</strong> 
            <?php if ($saran['status_baca'] == 1 && !empty($saran['draf_balasan_ketua'])): ?>
                <span class="badge badge-success">Dibalas</span>
            <?php elseif ($saran['status_baca'] == 1): ?>
                <span class="badge badge-primary">Dibaca Ketua</span>
            <?php else: ?>
                <span class="badge badge-danger">Belum Dibaca</span>
            <?php endif; ?>
        </div>

        <div style="background: #f9fafb; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #e5e7eb;">
            <p style="white-space: pre-line; margin:0;"><?php echo htmlspecialchars($saran['isi_saran'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>

        <?php if (!empty($saran['draf_balasan_ketua'])): ?>
            <div style="background: #eff6ff; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #bfdbfe;">
                <h4 style="margin-top: 0; color: #1e3a8a;">Balasan Ketua:</h4>
                <p style="white-space: pre-line; margin:0;"><?php echo htmlspecialchars($saran['draf_balasan_ketua'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        <?php endif; ?>

        <?php if ($role === 'ketua'): ?>
            <hr style="margin: 30px 0; border: 0; border-top: 1px solid #e5e7eb;">
            <h4>Tanggapi Masukan</h4>
            <div id="alert-container"></div>
            <form id="form-balasan" method="POST" class="form saran-form">
                <input type="hidden" name="action" value="reply">
                <input type="hidden" name="id_saran" value="<?php echo $saran['id_saran']; ?>">
                <div class="form-group">
                    <label class="form-label" for="draf_balasan_ketua">Draf Balasan</label>
                    <textarea class="form-textarea" id="draf_balasan_ketua" name="draf_balasan_ketua" rows="5" required placeholder="Tuliskan tanggapan Anda di sini..."><?php echo htmlspecialchars($saran['draf_balasan_ketua'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
                <div class="form-actions saran-actions">
                    <button type="submit" class="btn btn-primary" id="btn-submit">Simpan Balasan</button>
                    <a href="<?php echo base_url('pages/saran/index.php'); ?>" class="btn">Kembali</a>
                </div>
            </form>

            <script>
            document.getElementById('form-balasan').addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = document.getElementById('btn-submit');
                const alertContainer = document.getElementById('alert-container');
                
                btn.disabled = true;
                btn.textContent = 'Menyimpan...';
                alertContainer.innerHTML = '';

                const formData = new window.FormData(this);

                fetch('<?php echo base_url("api/saran-proses.php"); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    btn.disabled = false;
                    btn.textContent = 'Simpan Balasan';

                    if (data.status === 'success') {
                        alertContainer.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        alertContainer.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                    }
                })
                .catch(error => {
                    btn.disabled = false;
                    btn.textContent = 'Simpan Balasan';
                    alertContainer.innerHTML = '<div class="alert alert-danger">Terjadi kesalahan sistem.</div>';
                });
            });
            </script>
        <?php else: ?>
            <div class="form-actions saran-actions">
                <a href="<?php echo base_url('pages/saran/index.php'); ?>" class="btn">Kembali</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
