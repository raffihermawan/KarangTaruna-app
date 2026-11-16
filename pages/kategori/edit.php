<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';

require_role(['bendahara', 'ketua']);

$pdo = db();
$id = (int) ($_GET['id'] ?? 0);
$error = '';

$stmt = $pdo->prepare('SELECT * FROM kategori_transaksi WHERE id = :id');
$stmt->execute(['id' => $id]);
$kategori = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kategori) {
    http_response_code(404);
    exit('Kategori tidak ditemukan.');
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
        $jenis = $_POST['jenis'] ?? '';
        $divisi = $_POST['divisi'] ?? 'umum';
        $deskripsi = trim($_POST['deskripsi'] ?? '');

        if ($nama === '' || $jenis === '') {
            $error = 'Nama dan jenis wajib diisi.';
        } else {
            $stmt = $pdo->prepare('
                UPDATE kategori_transaksi
                SET nama = :nama,
                    jenis = :jenis,
                    divisi = :divisi,
                    deskripsi = :deskripsi
                WHERE id = :id
            ');
            $stmt->execute([
                'nama' => $nama,
                'jenis' => $jenis,
                'divisi' => $divisi,
                'deskripsi' => $deskripsi,
                'id' => $id,
            ]);
            set_flash('success', 'Kategori berhasil diperbarui.');
            header('Location: ' . base_url('pages/kategori/index.php'));
            exit;
        }
    }
}

$page_title = 'Edit Kategori';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <div class="card">
        <div class="card-title">Edit Kategori</div>
        <?php if ($error) : ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <form method="post" data-validate class="kategori-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-grid">
                <label>
                    Nama
                    <input type="text" name="nama" value="<?php echo htmlspecialchars($kategori['nama'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </label>
                <label>
                    Jenis
                    <select name="jenis" required>
                        <option value="pemasukan" <?php echo $kategori['jenis'] === 'pemasukan' ? 'selected' : ''; ?>>Pemasukan</option>
                        <option value="pengeluaran" <?php echo $kategori['jenis'] === 'pengeluaran' ? 'selected' : ''; ?>>Pengeluaran</option>
                    </select>
                </label>
                <label>
                    Divisi
                    <select name="divisi">
                        <?php foreach ($divisiOptions as $value => $label) : ?>
                            <option value="<?php echo $value; ?>" <?php echo $kategori['divisi'] === $value ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <label>
                Deskripsi
                <textarea name="deskripsi"><?php echo htmlspecialchars($kategori['deskripsi'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            </label>
            <div class="actions kategori-actions">
                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="<?php echo base_url('pages/kategori/index.php'); ?>" class="btn">Batal</a>
            </div>
        </form>
    </div>
</div>
<script src="<?php echo base_url('assets/js/validation.js'); ?>"></script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
