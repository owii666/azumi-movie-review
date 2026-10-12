<?php
/**
 * Admin Submission Edit Page
 * Azumi Movie Reviews
 */

$pageTitle = "Sunting Ulasan Film - Azumi Movie Reviews";
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: index.php');
    exit;
}

$pdo = getDbConnection();
$stmt = $pdo->prepare("SELECT * FROM film_submissions WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$submission = $stmt->fetch();

if (!$submission) {
    set_flash_message('danger', 'Ulasan tidak ditemukan.');
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Sesi keamanan tidak valid. Silakan muat ulang halaman.";
    } else {
        $title = trim($_POST['title'] ?? '');
        $author = trim($_POST['author_name'] ?? '');
        $category = trim($_POST['category'] ?? 'REVIEWS');
        $content = trim($_POST['content'] ?? '');
        $status = $_POST['status'] ?? 'pending';
        $adminNotes = trim($_POST['admin_notes'] ?? '');

        if (empty($title)) {
            $errors[] = "Judul tulisan wajib diisi.";
        }

        $pCount = count_paragraphs($content);
        if ($pCount < 2) {
            $errors[] = "Isi tulisan minimal 2 paragraf (terdeteksi: $pCount paragraf).";
        } elseif ($pCount > 5) {
            $errors[] = "Isi tulisan maksimal 5 paragraf (terdeteksi: $pCount paragraf).";
        }

        if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $status = 'pending';
        }

        if (empty($errors)) {
            try {
                $reviewedAt = ($status !== 'pending' && empty($submission['reviewed_at'])) ? date('Y-m-d H:i:s') : $submission['reviewed_at'];

                $updateStmt = $pdo->prepare("
                    UPDATE film_submissions 
                    SET title = :title,
                        author_name = :author,
                        category = :category,
                        content = :content,
                        status = :status,
                        admin_notes = :notes,
                        reviewed_at = :reviewed_at
                    WHERE id = :id
                ");

                $updateStmt->execute([
                    ':title'       => $title,
                    ':author'      => !empty($author) ? $author : 'Anonim',
                    ':category'    => !empty($category) ? $category : 'REVIEWS',
                    ':content'     => $content,
                    ':status'      => $status,
                    ':notes'       => !empty($adminNotes) ? $adminNotes : null,
                    ':reviewed_at' => $reviewedAt,
                    ':id'          => $id
                ]);

                set_flash_message('success', 'Ulasan "' . e($title) . '" berhasil diperbarui.');
                header('Location: submission-view.php?id=' . $id);
                exit;

            } catch (Exception $e) {
                $errors[] = "Gagal menyimpan perubahan: " . $e->getMessage();
            }
        }
    }
} else {
    $title = $submission['title'];
    $author = $submission['author_name'];
    $category = $submission['category'];
    $content = $submission['content'];
    $status = $submission['status'];
    $adminNotes = $submission['admin_notes'] ?? '';
}
?>

<main class="admin-container">
    <div style="margin-bottom: 20px;">
        <a href="submission-view.php?id=<?= $id ?>" style="color: var(--secondary-text); font-size: 0.85rem; text-decoration: none;">
            &larr; Kembali ke Detail Ulasan
        </a>
    </div>

    <div style="background-color: var(--white); border-radius: 8px; padding: 36px; box-shadow: 0 2px 12px var(--shadow); border: 1px solid var(--light-grey); max-width: 900px; margin: 0 auto;">
        <h1 style="font-family: var(--font-heading); font-size: 1.4rem; color: var(--dark); margin-bottom: 6px;">
            Sunting Ulasan Film
        </h1>
        <p style="color: var(--lighter-grey); font-size: 0.85rem; margin-bottom: 24px;">
            Perbaiki tata bahasa, judul, atau ringkas isi tulisan sebelum menerbitkan ulasan ke publik.
        </p>

        <?php if (!empty($errors)): ?>
            <div class="alert-box alert-danger">
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="submission-edit.php?id=<?= $id ?>" method="POST">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="title">Judul Ulasan / Film <span class="required">*</span></label>
                <input type="text" name="title" id="title" class="form-control" 
                       value="<?= e($title) ?>" required maxlength="255">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="author_name">Nama Pengirim / Penulis</label>
                    <input type="text" name="author_name" id="author_name" class="form-control" 
                           value="<?= e($author) ?>" maxlength="100">
                </div>

                <div class="form-group">
                    <label for="category">Kategori Ulasan</label>
                    <input type="text" name="category" id="category" class="form-control" 
                           value="<?= e($category) ?>" placeholder="Contoh: JAPANESE REVIEWS, REVIEWS, FESTIVAL WINNERS">
                </div>
            </div>

            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 8px;">
                    <label for="content" style="margin-bottom: 0;">
                        Isi Tulisan (Paragraf) <span class="required">*</span>
                    </label>
                    <span id="editPCountDisplay" class="paragraph-counter">0 paragraf</span>
                </div>
                <textarea name="content" id="editContent" class="form-control" rows="10" required><?= e($content) ?></textarea>
                <div class="form-help">
                    Pisahkan setiap paragraf dengan menekan <strong>Enter dua kali</strong>. Wajib 2 hingga 5 paragraf.
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="status">Status Publikasi</label>
                    <select name="status" id="status" class="form-control" style="font-weight: 600;">
                        <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending (Menunggu)</option>
                        <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved (Diterbitkan)</option>
                        <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected (Ditolak)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="admin_notes">Catatan Editorial Admin</label>
                    <textarea name="admin_notes" id="admin_notes" class="form-control" rows="2" 
                              placeholder="Catatan internal tim redaksi..."><?= e($adminNotes) ?></textarea>
                </div>
            </div>

            <div style="margin-top: 30px; display: flex; justify-content: flex-end; gap: 12px;">
                <a href="submission-view.php?id=<?= $id ?>" class="btn-action btn-view" style="padding: 12px 20px; font-size: 0.85rem;">
                    Batal
                </a>
                <button type="submit" class="btn-submit">
                    SIMPAN PERUBAHAN
                </button>
            </div>
        </form>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const area = document.getElementById("editContent");
    const countDisplay = document.getElementById("editPCountDisplay");

    function calc() {
        if (!area || !countDisplay) return;
        const text = area.value.trim();
        if (!text) {
            countDisplay.textContent = "0 paragraf";
            countDisplay.className = "paragraph-counter";
            return;
        }
        const parts = text.split(/\n\s*\n+/);
        let count = 0;
        for (let i = 0; i < parts.length; i++) {
            if (parts[i].trim().length > 5) count++;
        }
        countDisplay.textContent = count + " paragraf";
        if (count >= 2 && count <= 5) {
            countDisplay.className = "paragraph-counter valid";
        } else {
            countDisplay.className = "paragraph-counter invalid";
        }
    }

    if (area) {
        area.addEventListener("input", calc);
        calc();
    }
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
