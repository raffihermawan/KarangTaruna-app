<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';

require_role(['ketua', 'bendahara']);

$pdo = db();
$error = '';

$jenisOptions = [
    'SPTJM'        => 'SPTJM',
    'SK'            => 'Surat Keputusan (SK)',
    'Surat Tugas'   => 'Surat Tugas',
    'Proposal'      => 'Proposal',
    'LPJ'           => 'Laporan Pertanggungjawaban (LPJ)',
    'Notulensi'     => 'Notulensi Rapat',
    'Lainnya'       => 'Lainnya',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Token keamanan tidak valid.';
    } else {
        $namaDokumen = trim($_POST['nama_dokumen'] ?? '');
        $jenisDokumen = $_POST['jenis_dokumen'] ?? '';

        if ($namaDokumen === '' || $jenisDokumen === '') {
            $error = 'Nama dokumen dan jenis dokumen wajib diisi.';
        } elseif (!isset($_FILES['file_dokumen']) || $_FILES['file_dokumen']['error'] === UPLOAD_ERR_NO_FILE) {
            $error = 'File dokumen wajib diunggah.';
        } else {
            $file = $_FILES['file_dokumen'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'Gagal mengunggah file.';
            } else {
                $allowedExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

                if (!in_array($ext, $allowedExt, true)) {
                    $error = 'Ekstensi file tidak diizinkan. Hanya: ' . implode(', ', $allowedExt);
                } elseif ($file['size'] > 10 * 1024 * 1024) {
                    $error = 'Ukuran file maksimal 10 MB.';
                } else {
                    $destDir = __DIR__ . '/../../uploads/dokumen';
                    if (!is_dir($destDir)) {
                        mkdir($destDir, 0755, true);
                    }

                    $safeName = bin2hex(random_bytes(16)) . '.' . $ext;
                    $targetPath = $destDir . DIRECTORY_SEPARATOR . $safeName;

                    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $error = 'Gagal menyimpan file ke server.';
                    } else {
                        $stmt = $pdo->prepare('
                            INSERT INTO dokumen (nama_dokumen, jenis_dokumen, nama_file, uploaded_by, tgl_upload)
                            VALUES (:nama_dokumen, :jenis_dokumen, :nama_file, :uploaded_by, NOW())
                        ');
                        $stmt->execute([
                            'nama_dokumen'  => $namaDokumen,
                            'jenis_dokumen' => $jenisDokumen,
                            'nama_file'     => $safeName,
                            'uploaded_by'   => current_user()['id'],
                        ]);

                        set_flash('success', 'Dokumen berhasil diunggah.');
                        header('Location: ' . base_url('pages/dokumen/index.php'));
                        exit;
                    }
                }
            }
        }
    }
}

$page_title = 'Tambah Dokumen';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <div class="card">
        <div class="card-title">Tambah Dokumen / Berkas</div>
        <?php if ($error) : ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" data-validate class="dokumen-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-grid">
                <label>
                    Nama Dokumen
                    <input type="text" name="nama_dokumen" class="form-control" required placeholder="Contoh: SK Kepengurusan 2026">
                </label>
                <label>
                    Jenis Dokumen
                    <select name="jenis_dokumen" class="form-control" required>
                        <option value="">Pilih Jenis</option>
                        <?php foreach ($jenisOptions as $value => $label) : ?>
                            <option value="<?php echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    File Dokumen <small style="color:var(--muted);">(PDF, DOC, DOCX, XLS, XLSX — maks. 10MB)</small>
                    <input type="file" name="file_dokumen" class="form-control" required accept=".pdf,.doc,.docx,.xls,.xlsx">
                </label>
            </div>
            <div class="actions dokumen-actions">
                <button type="submit" class="btn" style="background-color: #112A46; color: #fff;">Simpan</button>
                <a href="<?php echo base_url('pages/dokumen/index.php'); ?>" class="btn">Batal</a>
            </div>
        </form>
    </div>
</div>
<script src="<?php echo base_url('assets/js/validation.js'); ?>"></script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
