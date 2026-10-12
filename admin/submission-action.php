<?php
/**
 * Admin Action Handler (Approve, Reject, Delete, Quick Status Update)
 * Azumi Movie Reviews
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

// Enforce admin login
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash_message('danger', 'Sesi keamanan tidak valid atau telah kedaluwarsa.');
    header('Location: index.php');
    exit;
}

$action = $_POST['action'] ?? '';
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    set_flash_message('danger', 'ID ulasan tidak valid.');
    header('Location: index.php');
    exit;
}

$pdo = getDbConnection();

// Fetch submission
$stmt = $pdo->prepare("SELECT id, title, image_path, status FROM film_submissions WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$sub = $stmt->fetch();

if (!$sub) {
    set_flash_message('danger', 'Ulasan tidak ditemukan.');
    header('Location: index.php');
    exit;
}

switch ($action) {
    case 'approve':
        $up = $pdo->prepare("UPDATE film_submissions SET status = 'approved', reviewed_at = NOW() WHERE id = :id");
        $up->execute([':id' => $id]);
        set_flash_message('success', 'Ulasan "' . e($sub['title']) . '" telah DISETUJUI dan sekarang diterbitkan di website publik.');
        break;

    case 'reject':
        $up = $pdo->prepare("UPDATE film_submissions SET status = 'rejected', reviewed_at = NOW() WHERE id = :id");
        $up->execute([':id' => $id]);
        set_flash_message('info', 'Ulasan "' . e($sub['title']) . '" telah DITOLAK.');
        break;

    case 'update_status':
        $newStatus = $_POST['status'] ?? 'pending';
        $notes = trim($_POST['admin_notes'] ?? '');
        
        if (!in_array($newStatus, ['pending', 'approved', 'rejected'], true)) {
            $newStatus = 'pending';
        }

        $up = $pdo->prepare("
            UPDATE film_submissions 
            SET status = :status, 
                admin_notes = :notes, 
                reviewed_at = NOW() 
            WHERE id = :id
        ");
        $up->execute([
            ':status' => $newStatus,
            ':notes'  => !empty($notes) ? $notes : null,
            ':id'     => $id
        ]);
        set_flash_message('success', 'Status ulasan "' . e($sub['title']) . '" berhasil diperbarui menjadi ' . strtoupper($newStatus) . '.');
        header('Location: submission-view.php?id=' . $id);
        exit;

    case 'delete':
        // Delete record from DB
        $del = $pdo->prepare("DELETE FROM film_submissions WHERE id = :id");
        $del->execute([':id' => $id]);

        // If poster is in uploads/posters/ and not a seed asset, delete the uploaded file
        if (!empty($sub['image_path'])) {
            $uploadedFilePath = __DIR__ . '/../uploads/posters/' . $sub['image_path'];
            if (file_exists($uploadedFilePath) && is_file($uploadedFilePath)) {
                // Check if this image isn't one of the static assets
                $assetPath = __DIR__ . '/../assets/images/' . $sub['image_path'];
                if (!file_exists($assetPath)) {
                    @unlink($uploadedFilePath);
                }
            }
        }

        set_flash_message('success', 'Ulasan "' . e($sub['title']) . '" berhasil dihapus dari sistem.');
        break;

    default:
        set_flash_message('warning', 'Tindakan tidak dikenal.');
        break;
}

header('Location: index.php');
exit;
