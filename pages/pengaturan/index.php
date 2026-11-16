<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';
require_once __DIR__ . '/../../helpers/query-helper.php';
require_once __DIR__ . '/../../helpers/upload-helper.php';

require_role(['bendahara', 'ketua']);

$pdo = db();
$pdo->exec('ALTER TABLE pengaturan ADD COLUMN IF NOT EXISTS email VARCHAR(150) DEFAULT NULL AFTER alamat');
$pdo->exec('ALTER TABLE pengaturan ADD COLUMN IF NOT EXISTS nama_ketua VARCHAR(100) DEFAULT NULL AFTER email');
$pdo->exec('ALTER TABLE pengaturan ADD COLUMN IF NOT EXISTS nama_bendahara VARCHAR(100) DEFAULT NULL AFTER nama_ketua');
$pdo->exec('ALTER TABLE pengaturan ADD COLUMN IF NOT EXISTS tampilkan_tanda_tangan TINYINT(1) NOT NULL DEFAULT 1 AFTER nama_bendahara');
$pdo->exec('ALTER TABLE pengaturan ADD COLUMN IF NOT EXISTS tanda_tangan_ketua VARCHAR(255) DEFAULT NULL AFTER tampilkan_tanda_tangan');
$pdo->exec('ALTER TABLE pengaturan ADD COLUMN IF NOT EXISTS tanda_tangan_bendahara VARCHAR(255) DEFAULT NULL AFTER tanda_tangan_ketua');
$settings = get_pengaturan($pdo);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Token keamanan tidak valid.';
    } else {
        $namaOrganisasi = trim($_POST['nama_organisasi'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $namaKetua = trim($_POST['nama_ketua'] ?? '');
        $namaBendahara = trim($_POST['nama_bendahara'] ?? '');
        $tampilkanTandaTangan = isset($_POST['tampilkan_tanda_tangan']) ? 1 : 0;
        $noRekening = trim($_POST['no_rekening'] ?? '');
        $namaBank = trim($_POST['nama_bank'] ?? '');
        $tahunAktif = trim($_POST['tahun_aktif'] ?? '');

        $logoPath = $settings['logo'] ?? null;
        $signatureKetuaPath = $settings['tanda_tangan_ketua'] ?? null;
        $signatureBendaharaPath = $settings['tanda_tangan_bendahara'] ?? null;
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

        foreach ([
            'tanda_tangan_ketua' => ['label' => 'Ketua', 'file' => $_FILES['tanda_tangan_ketua'] ?? null, 'path' => &$signatureKetuaPath],
            'tanda_tangan_bendahara' => ['label' => 'Bendahara', 'file' => $_FILES['tanda_tangan_bendahara'] ?? null, 'path' => &$signatureBendaharaPath],
        ] as $signature) {
            if (empty($signature['file']['name'])) {
                continue;
            }

            $upload = handle_upload(
                $signature['file'],
                __DIR__ . '/../../uploads/signatures',
                ['image/jpeg', 'image/png'],
                2 * 1024 * 1024,
                'uploads/signatures'
            );
            if (!$upload['success']) {
                $error = 'Upload tanda tangan ' . $signature['label'] . ': ' . $upload['error'];
                break;
            }

            if (!empty($signature['path'])) {
                $oldPath = __DIR__ . '/../../' . $signature['path'];
                if (is_file($oldPath)) {
                    unlink($oldPath);
                }
            }
            $signature['path'] = $upload['data']['path_file'] ?? null;
        }

        if ($error === '') {
            $stmt = $pdo->prepare('
                UPDATE pengaturan
                SET nama_organisasi = :nama_organisasi,
                    alamat = :alamat,
                    email = :email,
                    nama_ketua = :nama_ketua,
                    nama_bendahara = :nama_bendahara,
                    tampilkan_tanda_tangan = :tampilkan_tanda_tangan,
                    tanda_tangan_ketua = :tanda_tangan_ketua,
                    tanda_tangan_bendahara = :tanda_tangan_bendahara,
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
                'nama_ketua' => $namaKetua,
                'nama_bendahara' => $namaBendahara,
                'tampilkan_tanda_tangan' => $tampilkanTandaTangan,
                'tanda_tangan_ketua' => $signatureKetuaPath,
                'tanda_tangan_bendahara' => $signatureBendaharaPath,
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
                    Nama Ketua
                    <input type="text" name="nama_ketua" value="<?php echo htmlspecialchars($settings['nama_ketua'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </label>
                <label>
                    Nama Bendahara
                    <input type="text" name="nama_bendahara" value="<?php echo htmlspecialchars($settings['nama_bendahara'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </label>
            <label>
                Logo (Opsional)
                <input type="file" id="logo-input" name="logo" accept=".jpg,.jpeg,.png">
            </label>
            <label class="setting-toggle">
                <span class="setting-toggle__text">Tampilkan tanda tangan pada laporan</span>
                <input type="checkbox" name="tampilkan_tanda_tangan" value="1" <?php echo (int) ($settings['tampilkan_tanda_tangan'] ?? 1) === 1 ? 'checked' : ''; ?>>
                <span class="toggle-switch" aria-hidden="true">
                    <span class="toggle-thumb"></span>
                </span>
            </label>
            <div class="form-grid">
                <label>
                    Tanda Tangan Ketua (PNG/JPG)
                    <input type="file" name="tanda_tangan_ketua" accept=".jpg,.jpeg,.png">
                </label>
                <label>
                    Tanda Tangan Bendahara (PNG/JPG)
                    <input type="file" name="tanda_tangan_bendahara" accept=".jpg,.jpeg,.png">
                </label>
            </div>
            <div class="actions">
                <button type="submit" id="btn-simpan" class="btn btn-success">Simpan</button>
            </div>
        </form>
    </div>
</div>
<script src="<?php echo base_url('assets/js/validation.js'); ?>"></script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
