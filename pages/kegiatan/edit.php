<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';

require_role('bendahara');

$pdo = db();
$id = (int) ($_GET['id'] ?? 0);
$error = '';

$stmt = $pdo->prepare('SELECT * FROM kegiatan WHERE id = :id');
$stmt->execute(['id' => $id]);
$kegiatan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kegiatan) {
    http_response_code(404);
    exit('Kegiatan tidak ditemukan.');
}

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
                UPDATE kegiatan
                SET nama = :nama,
                    divisi = :divisi,
                    tanggal_mulai = :tanggal_mulai,
                    tanggal_selesai = :tanggal_selesai,
                    anggaran = :anggaran,
                    deskripsi = :deskripsi,
                    status = :status
                WHERE id = :id
            ');
            $stmt->execute([
                'nama' => $nama,
                'divisi' => $divisi,
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai,
                'anggaran' => $anggaran,
                'deskripsi' => $deskripsi,
                'status' => $status,
                'id' => $id,
            ]);
            set_flash('success', 'Kegiatan berhasil diperbarui.');
            header('Location: ' . base_url('pages/kegiatan/detail.php?id=' . $id));
            exit;
        }
    }
}

$page_title = 'Edit Kegiatan';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <div class="card">
        <div class="card-title">Edit Kegiatan</div>
        <?php if ($error) : ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <form method="post" data-validate class="kegiatan-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-grid">
                <label>
                    Nama
                    <input type="text" name="nama" value="<?php echo htmlspecialchars($kegiatan['nama'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </label>
                <label>
                    Divisi
                    <select name="divisi" required>
                        <?php foreach ($divisiOptions as $value => $label) : ?>
                            <option value="<?php echo $value; ?>" <?php echo $kegiatan['divisi'] === $value ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Tanggal Mulai
                    <input type="date" name="tanggal_mulai" value="<?php echo htmlspecialchars($kegiatan['tanggal_mulai'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </label>
                <label>
                    Tanggal Selesai
                    <input type="date" name="tanggal_selesai" value="<?php echo htmlspecialchars($kegiatan['tanggal_selesai'], ENT_QUOTES, 'UTF-8'); ?>">
                </label>
                <label>
                    Anggaran
                    <input type="text" name="anggaran" value="<?php echo htmlspecialchars($kegiatan['anggaran'], ENT_QUOTES, 'UTF-8'); ?>">
                </label>
                <label>
                    Status
                    <select name="status">
                        <option value="aktif" <?php echo $kegiatan['status'] === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="selesai" <?php echo $kegiatan['status'] === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                        <option value="dibatalkan" <?php echo $kegiatan['status'] === 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                    </select>
                </label>
            </div>
            <label>
                Deskripsi
                <textarea name="deskripsi"><?php echo htmlspecialchars($kegiatan['deskripsi'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </label>
            <div class="actions kegiatan-actions">
                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="<?php echo base_url('pages/kegiatan/detail.php?id=' . $id); ?>" class="btn">Batal</a>
            </div>
        </form>
    </div>
</div>
<script src="<?php echo base_url('assets/js/validation.js'); ?>"></script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
