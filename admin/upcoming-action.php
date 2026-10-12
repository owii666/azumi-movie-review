<?php
/**
 * Admin Upcoming Actions Handler (Add, Edit, Delete)
 * Azumi Movie Reviews
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// Enforce admin login
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: upcoming.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash_message('danger', 'Sesi keamanan tidak valid atau telah kedaluwarsa.');
    header('Location: upcoming.php');
    exit;
}

$action = $_POST['action'] ?? '';
$pdo = getDbConnection();

switch ($action) {
    case 'add':
        $title = trim($_POST['title'] ?? '');
        if (empty($title)) {
            set_flash_message('danger', 'Judul film wajib diisi.');
            header('Location: upcoming.php');
            exit;
        }

        if (!isset($_FILES['poster']) || $_FILES['poster']['error'] === UPLOAD_ERR_NO_FILE) {
            set_flash_message('danger', 'File poster film wajib diunggah.');
            header('Location: upcoming.php');
            exit;
        }

        $file = $_FILES['poster'];
        $uploadResult = handle_poster_upload($file);
        if (!$uploadResult['success']) {
            set_flash_message('danger', $uploadResult['error']);
            header('Location: upcoming.php');
            exit;
        }

        try {
            $stmt = $pdo->prepare("
                INSERT INTO upcoming_films (title, poster_path, created_at) 
                VALUES (:title, :poster, NOW())
            ");
            $stmt->execute([
                ':title'  => $title,
                ':poster' => $uploadResult['filename']
            ]);

            set_flash_message('success', 'Film "' . e($title) . '" berhasil ditambahkan ke daftar Upcoming.');
        } catch (Exception $e) {
            set_flash_message('danger', 'Gagal menyimpan film: ' . $e->getMessage());
        }

        header('Location: upcoming.php');
        exit;

    case 'edit':
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $title = trim($_POST['title'] ?? '');

        if (!$id || empty($title)) {
            set_flash_message('danger', 'ID atau judul film tidak valid.');
            header('Location: upcoming.php');
            exit;
        }

        // Fetch existing film
        $stmt = $pdo->prepare("SELECT * FROM upcoming_films WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $existing = $stmt->fetch();

        if (!$existing) {
            set_flash_message('danger', 'Film tidak ditemukan.');
            header('Location: upcoming.php');
            exit;
        }

        $posterFilename = $existing['poster_path'];

        // If a new poster is uploaded
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = handle_poster_upload($_FILES['poster']);
            if (!$uploadResult['success']) {
                set_flash_message('danger', $uploadResult['error']);
                header('Location: upcoming-edit.php?id=' . $id);
                exit;
            }

            // Delete old poster file if in uploads/posters/ and not a static asset
            delete_poster_file_if_safe($existing['poster_path']);
            $posterFilename = $uploadResult['filename'];
        }

        try {
            $up = $pdo->prepare("
                UPDATE upcoming_films 
                SET title = :title, poster_path = :poster 
                WHERE id = :id
            ");
            $up->execute([
                ':title'  => $title,
                ':poster' => $posterFilename,
                ':id'     => $id
            ]);

            set_flash_message('success', 'Film "' . e($title) . '" berhasil diperbarui.');
        } catch (Exception $e) {
            set_flash_message('danger', 'Gagal memperbarui film: ' . $e->getMessage());
        }

        header('Location: upcoming.php');
        exit;

    case 'delete':
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$id) {
            set_flash_message('danger', 'ID film tidak valid.');
            header('Location: upcoming.php');
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM upcoming_films WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $film = $stmt->fetch();

        if ($film) {
            $del = $pdo->prepare("DELETE FROM upcoming_films WHERE id = :id");
            $del->execute([':id' => $id]);

            delete_poster_file_if_safe($film['poster_path']);
            set_flash_message('success', 'Film "' . e($film['title']) . '" berhasil dihapus dari Upcoming.');
        } else {
            set_flash_message('danger', 'Film tidak ditemukan.');
        }

        header('Location: upcoming.php');
        exit;

    default:
        set_flash_message('warning', 'Tindakan tidak dikenal.');
        header('Location: upcoming.php');
        exit;
}

/**
 * Validates and stores uploaded poster file safely
 */
function handle_poster_upload(array $file): array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Gagal mengunggah file (Kode error: ' . $file['error'] . ')'];
    }

    $maxSize = 2 * 1024 * 1024; // 2 MB
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'Ukuran file gambar melebihi batas maksimal 2 MB.'];
    }

    $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
    $info = pathinfo($file['name']);
    $ext = strtolower($info['extension'] ?? '');

    if (!in_array($ext, $allowedExts, true)) {
        return ['success' => false, 'error' => 'Format file tidak diizinkan. Gunakan JPG, PNG, atau WebP.'];
    }

    // MIME verification
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowedMimes = ['image/jpeg', 'image/pjpeg', 'image/png', 'image/webp'];
    if (!in_array($mimeType, $allowedMimes, true)) {
        return ['success' => false, 'error' => 'File bukan gambar valid.'];
    }

    if (@getimagesize($file['tmp_name']) === false) {
        return ['success' => false, 'error' => 'File gambar tidak dapat dibaca atau rusak.'];
    }

    $uploadDir = __DIR__ . '/../uploads/posters/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $uniqueName = 'up_' . bin2hex(random_bytes(6)) . '_' . time() . '.' . $ext;
    $target = $uploadDir . $uniqueName;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return ['success' => false, 'error' => 'Gagal menyimpan file ke direktori server.'];
    }

    return ['success' => true, 'filename' => $uniqueName];
}

/**
 * Deletes uploaded file only if it is in uploads/posters and not in static assets
 */
function delete_poster_file_if_safe(?string $filename): void {
    if (empty($filename)) return;

    $uploadedPath = __DIR__ . '/../uploads/posters/' . $filename;
    $staticAssetPath = __DIR__ . '/../assets/images/' . $filename;

    if (file_exists($uploadedPath) && is_file($uploadedPath) && !file_exists($staticAssetPath)) {
        @unlink($uploadedPath);
    }
}
