<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';
require_once __DIR__ . '/../../helpers/upload-helper.php';

require_role(['bendahara', 'ketua']);

$pdo = db();
$id = (int) ($_GET['id'] ?? 0);
$error = '';

$stmt = $pdo->prepare('SELECT * FROM anggota WHERE id = :id');
$stmt->execute(['id' => $id]);
$anggota = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$anggota) {
    http_response_code(404);
    exit('Anggota tidak ditemukan.');
}

$fotoValue = trim((string) ($anggota['foto'] ?? ''));
$fotoFileExists = false;
if ($fotoValue !== '') {
    $fotoAbsolutePath = __DIR__ . '/../../' . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fotoValue);
    $fotoFileExists = is_file($fotoAbsolutePath);
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
            $fotoPath = $anggota['foto'];
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
                    if ($fotoPath) {
                        $oldPath = __DIR__ . '/../../' . $fotoPath;
                        if (is_file($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                    $fotoPath = $upload['data']['path_file'] ?? null;
                }
            }
        }

        if ($error === '') {
            $stmt = $pdo->prepare('
                UPDATE anggota
                SET nik = :nik,
                    nama = :nama,
                    jabatan = :jabatan,
                    divisi = :divisi,
                    no_hp = :no_hp,
                    email = :email,
                    alamat = :alamat,
                    foto = :foto,
                    status = :status
                WHERE id = :id
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
                'id' => $id,
            ]);
            set_flash('success', 'Anggota berhasil diperbarui.');
            header('Location: ' . base_url('pages/anggota/index.php'));
            exit;
        }
    }
}

$page_title = 'Edit Anggota';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <div class="card">
        <div class="card-title">Edit Anggota</div>
        <?php include __DIR__ . '/../../includes/alert.php'; ?>
        <?php if ($error) : ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" data-validate class="anggota-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-grid">
                <label>
                    NIK
                    <input type="text" name="nik" value="<?php echo htmlspecialchars($anggota['nik'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </label>
                <label>
                    Nama
                    <input type="text" name="nama" value="<?php echo htmlspecialchars($anggota['nama'], ENT_QUOTES, 'UTF-8'); ?>" required>
                </label>
                <label>
                    Jabatan
                    <input type="text" name="jabatan" value="<?php echo htmlspecialchars($anggota['jabatan'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </label>
                <label>
                    Divisi
                    <select name="divisi">
                        <?php foreach ($divisiOptions as $value => $label) : ?>
                            <option value="<?php echo $value; ?>" <?php echo $anggota['divisi'] === $value ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    No. HP
                    <input type="text" name="no_hp" value="<?php echo htmlspecialchars($anggota['no_hp'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </label>
                <label>
                    Email
                    <input type="email" name="email" value="<?php echo htmlspecialchars($anggota['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </label>
            </div>
            <label>
                Alamat
                <textarea name="alamat"><?php echo htmlspecialchars($anggota['alamat'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            </label>
            <label>
                Foto Baru (Opsional)
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:10px;">
                    <input type="file" name="foto" accept=".jpg,.jpeg,.png" style="max-width:320px;">
                    <?php if ($fotoFileExists) : ?>
                        <a
                            href="<?php echo base_url('pages/anggota/process-delete-foto.php?id=' . (int) $anggota['id'] . '&csrf_token=' . urlencode(csrf_token())); ?>"
                            class="btn btn-danger"
                            data-confirm="Apakah Anda yakin ingin menghapus foto profil dan kembali menggunakan avatar bawaan?"
                            data-confirm-title="Hapus Foto Profil"
                            data-confirm-ok="Ya, hapus"
                            data-confirm-cancel="Batal"
                            data-confirm-icon="warning"
                        >Hapus Foto</a>
                    <?php endif; ?>
                </div>
            </label>
            <label>
                Status
                <select name="status">
                    <option value="aktif" <?php echo $anggota['status'] === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="nonaktif" <?php echo $anggota['status'] === 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                </select>
            </label>
            <div class="actions anggota-actions">
                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="<?php echo base_url('pages/anggota/index.php'); ?>" class="btn">Batal</a>
            </div>
        </form>
    </div>
</div>
<script src="<?php echo base_url('assets/js/validation.js'); ?>"></script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
