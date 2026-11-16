<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';

require_login();

$role = current_user()['role'] ?? '';
if ($role !== 'anggota') {
    header('Location: ' . base_url('pages/saran/index.php'));
    exit;
}

$pdo = db();
$anggotaList = $pdo->query("SELECT id, nama FROM anggota WHERE status = 'aktif' ORDER BY nama ASC")->fetchAll();

$page_title = 'Buat Masukan Baru';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <div class="card">
        <div class="card-title">Kirim Masukan & Saran</div>
        <p>Gunakan formulir ini untuk menyampaikan masukan, kritik, atau saran secara konstruktif kepada Ketua.</p>
        
        <div id="alert-container"></div>

        <form id="form-saran" method="post" data-validate class="saran-form">
            <input type="hidden" name="action" value="create">
            <div class="form-grid">
                <label>
                    Pengirim Masukan (Pilih Nama Anda) <span style="color:red;">*</span>
                    <select name="id_anggota" class="form-control" required>
                        <option value="" selected disabled>-- Pilih Anggota --</option>
                        <?php foreach ($anggotaList as $angg): ?>
                            <option value="<?php echo $angg['id']; ?>"><?php echo htmlspecialchars($angg['nama'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Perihal / Topik Keuangan <span style="color:red;">*</span>
                    <input type="text" name="perihal" class="form-control" placeholder="Contoh: Transparansi Dana Kegiatan" required>
                </label>
            </div>
            <label>
                Detail Masukan / Kritik <span style="color:red;">*</span>
                <textarea name="detail_saran" class="form-control" rows="4" placeholder="Jelaskan secara rinci masukan atau kritik Anda terkait pengelolaan keuangan..." required></textarea>
            </label>
            <label>
                Saran Solutif
                <textarea name="solusi" class="form-control" rows="3" placeholder="Berikan saran atau solusi menurut Anda agar menjadi lebih baik ke depannya..."></textarea>
            </label>
            <div class="actions saran-actions">
                <button type="submit" id="btn-submit" class="btn" style="background-color: #112A46; color: white;">Kirim Masukan</button>
                <a href="<?php echo base_url('pages/saran/index.php'); ?>" class="btn">Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
// Validasi HTML5 bawaan form
document.getElementById('form-saran').addEventListener('submit', function(e) {
    if (!this.checkValidity()) {
        return; // Biarkan HTML5 menangani validasi field kosong
    }
    
    e.preventDefault();
    const btn = document.getElementById('btn-submit');
    const alertContainer = document.getElementById('alert-container');
    
    btn.disabled = true;
    btn.textContent = 'Mengirim...';
    alertContainer.innerHTML = '';

    const formData = new window.FormData(this);

    fetch('<?php echo base_url("api/saran-proses.php"); ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.textContent = 'Kirim Masukan';

        if (data.status === 'success') {
            alertContainer.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
            setTimeout(() => {
                window.location.href = '<?php echo base_url("pages/saran/index.php"); ?>';
            }, 1500);
        } else {
            alertContainer.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.textContent = 'Kirim Masukan';
        alertContainer.innerHTML = '<div class="alert alert-danger">Terjadi kesalahan sistem.</div>';
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
