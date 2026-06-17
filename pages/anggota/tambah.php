<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';
require_once __DIR__ . '/../../helpers/upload-helper.php';

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
        $nik = trim($_POST['nik'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $jabatan = trim($_POST['jabatan'] ?? '');
        $divisi = $_POST['divisi'] ?? 'umum';
        $noHp = trim($_POST['no_hp'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $status = $_POST['status'] ?? 'aktif';

        if ($nama === '') {
            $error = 'Nama wajib diisi.';
        } else {
            $fotoPath = null;
            if (!empty($_FILES['foto']['name'])) {
                $upload = handle_upload(
                    $_FILES['foto'],
                    __DIR__ . '/../../uploads/anggota',
                    ['image/jpeg', 'image/png'],
                    2 * 1024 * 1024,
                    'uploads/anggota'
                );
                if (!$upload['success']) {
                    $error = $upload['error'];
                } else {
                    $fotoPath = $upload['data']['path_file'] ?? null;
                }
            }
        }

        if ($error === '') {
            $stmt = $pdo->prepare('
                INSERT INTO anggota (nik, nama, jabatan, divisi, no_hp, email, alamat, foto, status)
                VALUES (:nik, :nama, :jabatan, :divisi, :no_hp, :email, :alamat, :foto, :status)
            ');
            $stmt->execute([
                'nik' => $nik,
                'nama' => $nama,
                'jabatan' => $jabatan,
                'divisi' => $divisi,
                'no_hp' => $noHp,
                'email' => $email,
                'alamat' => $alamat,
                'foto' => $fotoPath,
                'status' => $status,
            ]);
            set_flash('success', 'Anggota berhasil ditambahkan.');
            header('Location: ' . base_url('pages/anggota/index.php'));
            exit;
        }
    }
}

$page_title = 'Tambah Anggota';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <div class="card">
        <div class="card-title">Tambah Anggota</div>
        <?php if ($error) : ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" data-validate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-grid">
                <label>
                    NIK
                    <input type="text" name="nik">
                </label>
                <label>
                    Nama
                    <input type="text" name="nama" required>
                </label>
                <label>
                    Jabatan
                    <input type="text" name="jabatan">
                </label>
                <label>
                    Divisi
                    <select name="divisi">
                        <?php foreach ($divisiOptions as $value => $label) : ?>
                            <option value="<?php echo $value; ?>"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    No. HP
                    <input type="text" name="no_hp">
                </label>
                <label>
                    Email
                    <input type="email" name="email">
                </label>
            </div>
            <label>
                Alamat
                <textarea name="alamat"></textarea>
            </label>
            <label>
                Foto (Opsional, max 2MB)
                <input type="file" name="foto" accept=".jpg,.jpeg,.png">
            </label>
            <label>
                Status
                <select name="status">
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </label>
            <div class="actions">
                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="<?php echo base_url('pages/anggota/index.php'); ?>" class="btn">Batal</a>
            </div>
        </form>
    </div>
</div>
<script src="<?php echo base_url('assets/js/validation.js'); ?>"></script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
