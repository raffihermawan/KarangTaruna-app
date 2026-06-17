<?php
function handle_upload(array $file, string $destDir, array $allowedTypes, int $maxSize, string $relativeDir): array
{
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'error' => 'File upload tidak valid.'];
    }

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => true, 'data' => null];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Gagal mengunggah file.'];
    }

    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'Ukuran file melebihi batas 5MB.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowedTypes, true)) {
        return ['success' => false, 'error' => 'Tipe file tidak didukung.'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $safeName = bin2hex(random_bytes(16)) . '.' . $ext;

    if (!is_dir($destDir) && !mkdir($destDir, 0755, true)) {
        return ['success' => false, 'error' => 'Direktori upload tidak tersedia.'];
    }

    $targetPath = rtrim($destDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $safeName;
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => false, 'error' => 'Gagal menyimpan file.'];
    }

    $relativeDir = trim(str_replace('\\', '/', $relativeDir), '/');
    $relativePath = $relativeDir === '' ? $safeName : $relativeDir . '/' . $safeName;

    return [
        'success' => true,
        'data' => [
            'nama_file' => $file['name'],
            'path_file' => $relativePath,
            'tipe_file' => $mime,
            'ukuran_file' => $file['size'],
        ],
    ];
}
