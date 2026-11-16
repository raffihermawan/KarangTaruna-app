<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/auth-helper.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user = current_user();
$pdo = db();
$method = $_SERVER['REQUEST_METHOD'];

// Profanity filter array
$bad_words = ['kasar', 'bodoh', 'goblok', 'tolol', 'anjing', 'babi', 'bangsat'];

function contains_bad_word(string $text, array $bad_words): bool
{
    $text = strtolower($text);
    foreach ($bad_words as $word) {
        if (strpos($text, $word) !== false) {
            return true;
        }
    }
    return false;
}

if ($method === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create' && $user['role'] === 'anggota') {
        $id_anggota = (int)($_POST['id_anggota'] ?? 0);
        $perihal = trim($_POST['perihal'] ?? '');
        $detail = trim($_POST['detail_saran'] ?? '');
        $solusi = trim($_POST['solusi'] ?? '');
        
        $isi_saran = "Detail: " . $detail . "\n\nSaran Solutif: " . $solusi;

        if ($id_anggota <= 0 || empty($perihal) || empty($detail) || empty($solusi)) {
            echo json_encode(['status' => 'error', 'message' => 'Semua kolom wajib diisi.']);
            exit;
        }

        if (contains_bad_word($perihal, $bad_words) || contains_bad_word($isi_saran, $bad_words)) {
            echo json_encode(['status' => 'error', 'message' => 'Mohon gunakan bahasa yang sopan dan konstruktif.']);
            exit;
        }

        $stmt = $pdo->prepare('INSERT INTO saran (id_anggota, perihal, isi_saran) VALUES (:id_anggota, :perihal, :isi_saran)');
        $stmt->execute([
            'id_anggota' => $id_anggota,
            'perihal' => $perihal,
            'isi_saran' => $isi_saran
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Masukan dan saran berhasil dikirim.']);
        exit;
    }

    if ($action === 'reply' && $user['role'] === 'ketua') {
        $id_saran = (int)($_POST['id_saran'] ?? 0);
        $draf_balasan = trim($_POST['draf_balasan_ketua'] ?? '');

        if ($id_saran <= 0 || empty($draf_balasan)) {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak valid.']);
            exit;
        }

        $stmt = $pdo->prepare('UPDATE saran SET draf_balasan_ketua = :balasan, status_baca = 1 WHERE id_saran = :id');
        $stmt->execute([
            'balasan' => $draf_balasan,
            'id' => $id_saran
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Balasan berhasil disimpan.']);
        exit;
    }
    
    if ($action === 'mark_read' && $user['role'] === 'ketua') {
        $id_saran = (int)($_POST['id_saran'] ?? 0);
        if ($id_saran > 0) {
            $stmt = $pdo->prepare('UPDATE saran SET status_baca = 1 WHERE id_saran = :id AND status_baca = 0');
            $stmt->execute(['id' => $id_saran]);
            echo json_encode(['status' => 'success']);
            exit;
        }
    }

    echo json_encode(['status' => 'error', 'message' => 'Aksi tidak diizinkan.']);
    exit;
}

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    
    // For API requests with filter
    if ($action === 'get_saran' && $user['role'] === 'ketua') {
        $filter_id_anggota = $_GET['id_anggota'] ?? 'all';
        
        $sql = '
            SELECT s.*, a.nama AS nama_anggota 
            FROM saran s 
            JOIN anggota a ON s.id_anggota = a.id 
        ';
        
        $params = [];
        if ($filter_id_anggota !== 'all' && (int)$filter_id_anggota > 0) {
            $sql .= ' WHERE s.id_anggota = :id_anggota';
            $params['id_anggota'] = (int)$filter_id_anggota;
        }
        
        $sql .= ' ORDER BY s.tgl_kirim DESC';
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();
        
        foreach($data as &$row) {
            $row['pengirim'] = $row['nama_anggota']; 
        }
        
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }
    
    // Fallback for standard fetch if needed
    if ($user['role'] === 'ketua') {
        $stmt = $pdo->query('
            SELECT s.*, a.nama AS nama_anggota 
            FROM saran s 
            JOIN anggota a ON s.id_anggota = a.id 
            ORDER BY s.tgl_kirim DESC
        ');
        $data = $stmt->fetchAll();
        
        foreach($data as &$row) {
            $row['pengirim'] = $row['nama_anggota']; 
        }
        
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    } else if ($user['role'] === 'anggota') {
        // Wait, for 'anggota' role, if they submitted via dropdown, they can submit for ANY anggota.
        // Should we let them see all, or only the ones they submitted? 
        // The instruction says "pengguna di role Anggota dapat memilih terlebih dahulu Siapa nama anggota".
        // It implies the submitter can pick anyone. But who sees what? 
        // For now let's just show everything in anggota view, or maybe we leave the anggota view simple.
        // Let's just fetch all where id_anggota = session user id? NO. 
        // Since we decoupled login user and anggota, maybe we just fetch all for now, or just show empty.
        // Actually, let's just fetch all or keep it simple. Let's fetch all.
        $stmt = $pdo->query('
            SELECT s.*, a.nama AS nama_anggota 
            FROM saran s 
            JOIN anggota a ON s.id_anggota = a.id 
            ORDER BY s.tgl_kirim DESC
        ');
        $data = $stmt->fetchAll();
        foreach($data as &$row) {
            $row['pengirim'] = $row['nama_anggota']; 
        }
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
