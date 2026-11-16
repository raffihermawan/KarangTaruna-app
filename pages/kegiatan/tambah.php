<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';

require_role(['bendahara', 'ketua']);

$pdo = db();
$error = '';

$divisiOptions = [
    'sosial_pendidikan' => 'Sosial & Pendidikan',
    'ekonomi_kreatif' => 'Ekonomi Kreatif',
    'keagamaan' => 'Keagamaan',
    'pemuda_olahraga' => 'Pemuda & Olahraga',
    'umum' => 'Umum',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Token keamanan tidak valid.';
    } else {
        $nama = trim($_POST['nama'] ?? '');
        $divisi = $_POST['divisi'] ?? '';
        $tanggalMulai = $_POST['tanggal_mulai'] ?? '';
        $tanggalSelesai = $_POST['tanggal_selesai'] ?: null;
        $anggaranRaw = $_POST['anggaran'] ?? '';
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $status = $_POST['status'] ?? 'aktif';

        $anggaran = (float) preg_replace('/[^0-9]/', '', $anggaranRaw);

        if ($nama === '' || $divisi === '' || $tanggalMulai === '') {
            $error = 'Nama, divisi, dan tanggal mulai wajib diisi.';
        } else {
            $stmt = $pdo->prepare('
                INSERT INTO kegiatan (nama, divisi, tanggal_mulai, tanggal_selesai, anggaran, deskripsi, status, created_by)
                VALUES (:nama, :divisi, :tanggal_mulai, :tanggal_selesai, :anggaran, :deskripsi, :status, :created_by)
            ');
            $stmt->execute([
                'nama' => $nama,
                'divisi' => $divisi,
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai,
                'anggaran' => $anggaran,
                'deskripsi' => $deskripsi,
                'status' => $status,
                'created_by' => current_user()['id'],
            ]);
            set_flash('success', 'Kegiatan berhasil ditambahkan.');
            header('Location: ' . base_url('pages/kegiatan/index.php'));
            exit;
        }
    }
}

$page_title = 'Tambah Kegiatan';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <div class="card">
        <div class="card-title">Tambah Kegiatan</div>
        <?php if ($error) : ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <form method="post" data-validate class="kegiatan-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-grid">
                <label>
                    Nama
                    <input type="text" name="nama" required>
                </label>
                <label>
                    Divisi
                    <select name="divisi" required>
                        <option value="">Pilih</option>
                        <?php foreach ($divisiOptions as $value => $label) : ?>
                            <option value="<?php echo $value; ?>"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Tanggal Mulai
                    <input type="date" name="tanggal_mulai" required>
                </label>
                <label>
                    Tanggal Selesai
                    <input type="date" name="tanggal_selesai">
                </label>
                <label>
                    Anggaran
                    <input type="text" name="anggaran">
                </label>
                <label>
                    Status
                    <select name="status">
                        <option value="aktif">Aktif</option>
                        <option value="selesai">Selesai</option>
                        <option value="dibatalkan">Dibatalkan</option>
                    </select>
                </label>
            </div>
            <label>
                Deskripsi
                <textarea name="deskripsi"></textarea>
            </label>
            <div class="actions kegiatan-actions">
                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="<?php echo base_url('pages/kegiatan/index.php'); ?>" class="btn">Batal</a>
            </div>
        </form>
    </div>
</div>
<script src="<?php echo base_url('assets/js/validation.js'); ?>"></script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
