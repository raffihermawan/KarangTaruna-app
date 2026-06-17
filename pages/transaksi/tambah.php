<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth-helper.php';
require_once __DIR__ . '/../../helpers/upload-helper.php';
require_once __DIR__ . '/../../helpers/query-helper.php';
require_once __DIR__ . '/../../helpers/telegram-helper.php';

require_role('bendahara');

$pdo = db();
$error = '';

$kategoriList = $pdo->query('SELECT id, nama, jenis FROM kategori_transaksi ORDER BY nama')->fetchAll(PDO::FETCH_ASSOC);
$kegiatanList = $pdo->query('SELECT id, nama FROM kegiatan ORDER BY nama')->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Token keamanan tidak valid.';
    } else {
        $jenis = $_POST['jenis'] ?? '';
        $jumlahRaw = $_POST['jumlah'] ?? '';
        $tanggal = $_POST['tanggal'] ?? '';
        $kategoriId = $_POST['kategori_id'] ?? '';
        $kegiatanId = $_POST['kegiatan_id'] ?: null;
        $keterangan = trim($_POST['keterangan'] ?? '');
        $pic = trim($_POST['pic'] ?? '');

        $jumlah = (float) preg_replace('/[^0-9]/', '', $jumlahRaw);

        if ($jenis === '' || $jumlah <= 0 || $tanggal === '' || $kategoriId === '' || $keterangan === '' || $pic === '') {
            $error = 'Semua field wajib diisi.';
        } else {
            $kategoriStmt = $pdo->prepare('SELECT jenis FROM kategori_transaksi WHERE id = :id');
            $kategoriStmt->execute(['id' => $kategoriId]);
            $kategoriData = $kategoriStmt->fetch(PDO::FETCH_ASSOC);
            if (!$kategoriData || $kategoriData['jenis'] !== $jenis) {
                $error = 'Kategori tidak sesuai dengan jenis transaksi.';
            } else {
                $pdo->beginTransaction();
                try {
                $stmt = $pdo->prepare('
                    INSERT INTO transaksi (jenis, jumlah, tanggal, kategori_id, kegiatan_id, keterangan, pic, created_by)
                    VALUES (:jenis, :jumlah, :tanggal, :kategori_id, :kegiatan_id, :keterangan, :pic, :created_by)
                ');
                $stmt->execute([
                    'jenis' => $jenis,
                    'jumlah' => $jumlah,
                    'tanggal' => $tanggal,
                    'kategori_id' => $kategoriId,
                    'kegiatan_id' => $kegiatanId,
                    'keterangan' => $keterangan,
                    'pic' => $pic,
                    'created_by' => current_user()['id'],
                ]);

                $transaksiId = (int) $pdo->lastInsertId();

                $upload = handle_upload(
                    $_FILES['bukti'] ?? [],
                    __DIR__ . '/../../uploads/bukti',
                    ['image/jpeg', 'image/png', 'application/pdf'],
                    5 * 1024 * 1024,
                    'uploads/bukti'
                );

                if (!$upload['success']) {
                    throw new RuntimeException($upload['error']);
                }

                if ($upload['data']) {
                    $bukti = $upload['data'];
                    $stmt = $pdo->prepare('
                        INSERT INTO bukti_transaksi (transaksi_id, nama_file, path_file, tipe_file, ukuran_file)
                        VALUES (:transaksi_id, :nama_file, :path_file, :tipe_file, :ukuran_file)
                    ');
                    $stmt->execute([
                        'transaksi_id' => $transaksiId,
                        'nama_file' => $bukti['nama_file'],
                        'path_file' => $bukti['path_file'],
                        'tipe_file' => $bukti['tipe_file'],
                        'ukuran_file' => $bukti['ukuran_file'],
                    ]);
                }

                 $pdo->commit();

/*
|--------------------------------------------------------------------------
| NOTIF TELEGRAM TRANSAKSI
|--------------------------------------------------------------------------
*/

// Ambil nama kategori
$stmtKategori = $pdo->prepare('SELECT nama FROM kategori_transaksi WHERE id = :id');
$stmtKategori->execute(['id' => $kategoriId]);
$kategoriNama = $stmtKategori->fetchColumn() ?: '-';

// Ambil nama kegiatan
$kegiatanNama = '-';

if (!empty($kegiatanId)) {
    $stmtKegiatan = $pdo->prepare('SELECT nama FROM kegiatan WHERE id = :id');
    $stmtKegiatan->execute(['id' => $kegiatanId]);
    $kegiatanNama = $stmtKegiatan->fetchColumn() ?: '-';
}

// Ambil semua user yang punya chat id telegram
$stmtUsers = $pdo->query("
    SELECT telegram_chat_id
    FROM users
    WHERE telegram_chat_id IS NOT NULL
    AND telegram_chat_id != ''
");

$users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

// Kirim notif ke semua user
foreach ($users as $userNotif) {

    kirim_telegram_transaksi(
        $userNotif['telegram_chat_id'],
        $jenis,
        $jumlah,
        $kategoriNama,
        $kegiatanNama,
        current_user()['nama']
    );
}

set_flash('success', 'Transaksi berhasil ditambahkan.');
header('Location: ' . base_url('pages/transaksi/index.php'));
exit;
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = $e->getMessage();
                }
            }
        }
    }
}

$page_title = 'Tambah Transaksi';
include __DIR__ . '/../../includes/header.php';
include __DIR__ . '/../../includes/sidebar.php';
?>
<div class="page">
    <div class="card">
        <div class="card-title">Tambah Transaksi</div>
        <?php if ($error) : ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data" data-validate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
            <div class="form-grid">
                <label>
                    Jenis
                    <select name="jenis" required>
                        <option value="">Pilih</option>
                        <option value="pemasukan">Pemasukan</option>
                        <option value="pengeluaran">Pengeluaran</option>
                    </select>
                </label>
                <label>
                    Jumlah
                    <input type="text" name="jumlah" required>
                </label>
                <label>
                    Tanggal
                    <input type="date" name="tanggal" required>
                </label>
                <label>
                    Kategori
                    <select name="kategori_id" required>
                        <option value="">Pilih</option>
                        <?php foreach ($kategoriList as $kat) : ?>
                            <option value="<?php echo $kat['id']; ?>">
                                <?php echo htmlspecialchars($kat['nama'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    Kegiatan (Opsional)
                    <select name="kegiatan_id">
                        <option value="">Tanpa Kegiatan</option>
                        <?php foreach ($kegiatanList as $keg) : ?>
                            <option value="<?php echo $keg['id']; ?>">
                                <?php echo htmlspecialchars($keg['nama'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    PIC
                    <input type="text" name="pic" required>
                </label>
            </div>
            <label>
                Keterangan
                <textarea name="keterangan" required></textarea>
            </label>
            <label>
                Upload Bukti (JPG/PNG/PDF, max 5MB)
                <input type="file" name="bukti" accept=".jpg,.jpeg,.png,.pdf">
            </label>
            <div class="actions">
                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="<?php echo base_url('pages/transaksi/index.php'); ?>" class="btn">Batal</a>
            </div>
        </form>
    </div>
</div>
<script src="<?php echo base_url('assets/js/validation.js'); ?>"></script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
