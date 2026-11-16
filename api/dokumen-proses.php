<?php
/**
 * API Endpoint: Dokumen Proses
 *
 * Endpoint ini menyediakan logika upload dokumen via AJAX/API call.
 * Batasan ekstensi: .pdf, .doc, .docx, .xls, .xlsx
 * Menggunakan move_uploaded_file() untuk keamanan upload.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth-helper.php';

header('Content-Type: application/json; charset=utf-8');

// Hanya POST yang diizinkan
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

// Cek login
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

// Cek role
$user = current_user();
$role = $user['role'] ?? '';
if (!in_array($role, ['ketua', 'bendahara'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak.']);
    exit;
}

// Validasi CSRF
if (!validate_csrf($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token keamanan tidak valid.']);
    exit;
}

// Validasi input
$namaDokumen = trim($_POST['nama_dokumen'] ?? '');
$jenisDokumen = trim($_POST['jenis_dokumen'] ?? '');

if ($namaDokumen === '' || $jenisDokumen === '') {
    echo json_encode(['success' => false, 'message' => 'Nama dokumen dan jenis dokumen wajib diisi.']);
    exit;
}

// Validasi file
if (!isset($_FILES['file_dokumen']) || $_FILES['file_dokumen']['error'] === UPLOAD_ERR_NO_FILE) {
    echo json_encode(['success' => false, 'message' => 'File dokumen wajib diunggah.']);
    exit;
}

$file = $_FILES['file_dokumen'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Gagal mengunggah file. Error code: ' . $file['error']]);
    exit;
}

// Validasi ekstensi
$allowedExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExt, true)) {
    echo json_encode(['success' => false, 'message' => 'Ekstensi file tidak diizinkan. Hanya: ' . implode(', ', $allowedExt)]);
    exit;
}

// Validasi ukuran (maks. 10MB)
if ($file['size'] > 10 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'Ukuran file maksimal 10 MB.']);
    exit;
}

// Simpan file
$destDir = __DIR__ . '/../uploads/dokumen';
if (!is_dir($destDir)) {
    mkdir($destDir, 0755, true);
}

$safeName = bin2hex(random_bytes(16)) . '.' . $ext;
$targetPath = $destDir . DIRECTORY_SEPARATOR . $safeName;

if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode(['success' => false, 'message' => 'Gagal menyimpan file ke server.']);
    exit;
}

// Simpan ke database
$pdo = db();
$stmt = $pdo->prepare('
    INSERT INTO dokumen (nama_dokumen, jenis_dokumen, nama_file, uploaded_by, tgl_upload)
    VALUES (:nama_dokumen, :jenis_dokumen, :nama_file, :uploaded_by, NOW())
');
$stmt->execute([
    'nama_dokumen'  => $namaDokumen,
    'jenis_dokumen' => $jenisDokumen,
    'nama_file'     => $safeName,
    'uploaded_by'   => $user['id'],
]);

echo json_encode([
    'success' => true,
    'message' => 'Dokumen berhasil diunggah.',
    'data'    => [
        'id'             => (int) $pdo->lastInsertId(),
        'nama_dokumen'   => $namaDokumen,
        'jenis_dokumen'  => $jenisDokumen,
        'nama_file'      => $safeName,
    ],
]);
