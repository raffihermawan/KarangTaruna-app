<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';
require_once __DIR__ . '/../../helpers/query-helper.php';
require_once __DIR__ . '/../../helpers/upload-helper.php';

require_role('bendahara');

$pdo = db();
$pdo->exec('ALTER TABLE pengaturan ADD COLUMN IF NOT EXISTS email VARCHAR(150) DEFAULT NULL AFTER alamat');
$settings = get_pengaturan($pdo);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Token keamanan tidak valid.';
    } else {
        $namaOrganisasi = trim($_POST['nama_organisasi'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $noRekening = trim($_POST['no_rekening'] ?? '');
        $namaBank = trim($_POST['nama_bank'] ?? '');
        $tahunAktif = trim($_POST['tahun_aktif'] ?? '');

        $logoPath = $settings['logo'] ?? null;
        if (!empty($_FILES['logo']['name'])) {
            $upload = handle_upload(
                $_FILES['logo'],
                __DIR__ . '/../../uploads/logo',
                ['image/jpeg', 'image/png'],
                2 * 1024 * 1024,
                'uploads/logo'
            );
            if (!$upload['success']) {
                $error = $upload['error'];
            } else {
                if ($logoPath) {
                    $oldPath = __DIR__ . '/../../' . $logoPath;
                    if (is_file($oldPath)) {
                        unlink($oldPath);
                    }
                }
                $logoPath = $upload['data']['path_file'] ?? null;
            }
        }

        if ($error === '') {
            $stmt = $pdo->prepare('
                UPDATE pengaturan
                SET nama_organisasi = :nama_organisasi,
                    alamat = :alamat,
                    email = :email,
                    logo = :logo,
                    no_rekening = :no_rekening,
                    nama_bank = :nama_bank,
                    tahun_aktif = :tahun_aktif
                WHERE id = :id
            ');
            $stmt->execute([
                'nama_organisasi' => $namaOrganisasi ?: $settings['nama_organisasi'],
                'alamat' => $alamat,
                'email' => $email,
                'logo' => $logoPath,
                'no_rekening' => $noRekening,
                'nama_bank' => $namaBank,
                'tahun_aktif' => $tahunAktif ?: null,
                'id' => $settings['id'],
            ]);
            set_flash('success', 'Pengaturan berhasil diperbarui.');
            header('Location: ' . base_url('pages/pengaturan/index.php'));
            exit;
        }
    }
}

$page_title = 'Pengaturan Sistem';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <?php include __DIR__ . '/../../includes/alert.php'; ?>
    <div class="card">
        <div class="card-title">Profil Organisasi</div>
        <?php if ($error) : ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" data-validate id="form-pengaturan">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-grid">
                <label>
                    Nama Organisasi
                    <input type="text" name="nama_organisasi" value="<?php echo htmlspecialchars($settings['nama_organisasi'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                </label>
                <label>
                    Tahun Aktif
                    <input type="number" name="tahun_aktif" value="<?php echo htmlspecialchars($settings['tahun_aktif'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </label>
                <label>
                    No. Rekening
                    <input type="text" name="no_rekening" value="<?php echo htmlspecialchars($settings['no_rekening'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </label>
                <label>
                    Nama Bank
                    <input type="text" name="nama_bank" value="<?php echo htmlspecialchars($settings['nama_bank'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </label>
            </div>
            <label>
                Alamat
                <textarea name="alamat"><?php echo htmlspecialchars($settings['alamat'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
            </label>
                <label>
                    Email Organisasi
                    <input type="email" name="email" value="<?php echo htmlspecialchars($settings['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </label>
            <label>
                Logo (Opsional)
                <input type="file" id="logo-input" name="logo" accept=".jpg,.jpeg,.png">
            </label>
            <div class="actions">
                <button type="submit" id="btn-simpan" class="btn btn-success">Simpan</button>
            </div>
        </form>
    </div>
</div>
<script src="<?php echo base_url('assets/js/validation.js'); ?>"></script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
