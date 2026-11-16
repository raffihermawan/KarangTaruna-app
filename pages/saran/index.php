<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';
require_once __DIR__ . '/../../helpers/format-helper.php';

require_login();

$pdo = db();
$user = current_user();
$role = $user['role'] ?? '';

// Restrict access
if (!in_array($role, ['anggota', 'ketua'])) {
    header('Location: ' . base_url('pages/dashboard/index.php'));
    exit;
}

// Get Anggota list for Dropdown (Ketua only)
$anggotaList = [];
if ($role === 'ketua') {
    $stmt = $pdo->query("SELECT id, nama FROM anggota WHERE status = 'aktif' ORDER BY nama ASC");
    $anggotaList = $stmt->fetchAll();
}

$page_title = 'Masukan & Saran';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <?php include __DIR__ . '/../../includes/alert.php'; ?>
    <div class="card">
        <div class="card-title saran-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span>Daftar Masukan & Saran</span>
            <?php if ($role === 'ketua'): ?>
                <div>
                    <select id="filterAnggota" class="form-input saran-filter" style="width: auto; display: inline-block;">
                        <option value="all">Semua Anggota</option>
                        <?php foreach ($anggotaList as $angg): ?>
                            <option value="<?php echo $angg['id']; ?>"><?php echo htmlspecialchars($angg['nama'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($role === 'anggota') : ?>
            <div class="actions">
                <a href="<?php echo base_url('pages/saran/create.php'); ?>" class="btn btn-success">Buat Masukan Baru</a>
            </div>
        <?php endif; ?>
        
        <table class="table saran-table" id="saranTable">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <?php if ($role === 'ketua'): ?>
                        <th>Pengirim</th>
                    <?php endif; ?>
                    <th>Perihal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="saranTbody">
                <!-- Data will be loaded via API -->
                <tr><td colspan="<?php echo $role === 'ketua' ? '5' : '4'; ?>">Memuat data...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadSaran();
    
    <?php if ($role === 'ketua'): ?>
    document.getElementById('filterAnggota').addEventListener('change', function() {
        loadSaran(this.value);
    });
    <?php endif; ?>
});

function loadSaran(id_anggota = 'all') {
    let url = '<?php echo base_url("api/saran-proses.php"); ?>';
    <?php if ($role === 'ketua'): ?>
        url += '?action=get_saran&id_anggota=' + id_anggota;
    <?php endif; ?>
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('saranTbody');
            tbody.innerHTML = '';
            
            if (data.status === 'success' && data.data && data.data.length > 0) {
                data.data.forEach(saran => {
                    let statusBadge = '';
                    if (saran.status_baca == 1 && saran.draf_balasan_ketua) {
                        statusBadge = '<span class="badge badge-success">Dibalas</span>';
                    } else if (saran.status_baca == 1) {
                        statusBadge = '<span class="badge badge-primary">Dibaca Ketua</span>';
                    } else {
                        statusBadge = '<span class="badge badge-danger">Belum Dibaca</span>';
                    }
                    
                    let tr = document.createElement('tr');
                    
                    let html = '<td>' + saran.tgl_kirim + '</td>'; // You might want to format this with JS, but server returns Y-m-d H:i:s
                    
                    <?php if ($role === 'ketua'): ?>
                    html += '<td>' + escapeHtml(saran.pengirim || '') + '</td>';
                    <?php endif; ?>
                    
                    html += '<td>' + escapeHtml(saran.perihal || '') + '</td>';
                    html += '<td>' + statusBadge + '</td>';
                    html += '<td class="actions"><a href="<?php echo base_url('pages/saran/view.php?id='); ?>' + saran.id_saran + '" class="btn">Detail</a></td>';
                    
                    tr.innerHTML = html;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = '<tr><td colspan="<?php echo $role === 'ketua' ? '5' : '4'; ?>">Belum ada data masukan & saran.</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error fetching saran:', error);
            document.getElementById('saranTbody').innerHTML = '<tr><td colspan="<?php echo $role === 'ketua' ? '5' : '4'; ?>">Gagal memuat data.</td></tr>';
        });
}

function escapeHtml(unsafe) {
    return unsafe
         .replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
