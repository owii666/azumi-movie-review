<?php
/**
 * Admin Submission Detail View
 * Azumi Movie Reviews
 */

$pageTitle = "Detail Pengajuan Ulasan - Azumi Movie Reviews";
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
    set_flash_message('danger', 'Pengajuan ulasan tidak ditemukan.');
    header('Location: index.php');
    exit;
}

$paragraphs = get_paragraphs($submission['content']);
?>

<main class="admin-container">
    <div style="margin-bottom: 20px;">
        <a href="index.php" style="color: var(--secondary-text); font-size: 0.85rem; text-decoration: none;">
            &larr; Kembali ke Daftar Pengajuan
        </a>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 340px; gap: 28px; align-items: start;">
        <!-- Left: Full Review Content -->
        <div style="background-color: var(--white); border-radius: 8px; padding: 32px; box-shadow: 0 2px 12px var(--shadow); border: 1px solid var(--light-grey);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                <span class="category"><?= e($submission['category'] ?: 'REVIEWS') ?></span>
                <span class="badge badge-<?= e($submission['status']) ?>">
                    STATUS: <?= strtoupper(e($submission['status'])) ?>
                </span>
            </div>

            <h1 style="font-family: var(--font-title); font-size: 1.8rem; color: var(--dark); margin-bottom: 14px;">
                <?= e($submission['title']) ?>
            </h1>

            <div style="font-size: 0.85rem; color: var(--lighter-grey); margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid var(--light-grey);">
                <span>Dikirim oleh: <strong><?= e($submission['author_name'] ?: 'Anonim') ?></strong></span> &bull; 
                <span>Tanggal: <?= format_date($submission['submitted_at']) ?></span>
                <?php if ($submission['reviewed_at']): ?>
                    &bull; <span>Ditinjau: <?= format_date($submission['reviewed_at']) ?></span>
                <?php endif; ?>
            </div>

            <h3 style="font-family: var(--font-heading); font-size: 0.85rem; letter-spacing: 1px; color: var(--dark); margin-bottom: 18px;">
                ISI ULASAN (<?= count($paragraphs) ?> PARAGRAF)
            </h3>

            <div class="review-paragraphs-box">
                <?php foreach ($paragraphs as $index => $p): ?>
                    <div style="margin-bottom: 20px; padding: 14px 18px; background-color: #FAF9F5; border-left: 3px solid var(--light-grey); border-radius: 4px;">
                        <span style="display: block; font-size: 0.72rem; font-family: var(--font-heading); font-weight: 700; color: var(--lighter-grey); margin-bottom: 6px;">
                            PARAGRAF <?= $index + 1 ?>
                        </span>
                        <p style="font-family: var(--font-body); font-size: 0.95rem; line-height: 1.8; color: var(--secondary-text); margin: 0;">
                            <?= nl2br(e($p)) ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($submission['admin_notes'])): ?>
                <div style="margin-top: 30px; padding: 16px 20px; background-color: #FFF9C4; border-radius: 6px; border-left: 4px solid #FBC02D;">
                    <strong style="display: block; font-size: 0.8rem; color: #7F0000; text-transform: uppercase; margin-bottom: 4px;">Catatan Admin Sebelumnya:</strong>
                    <p style="font-size: 0.9rem; color: #3E2723; margin: 0;"><?= nl2br(e($submission['admin_notes'])) ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right: Poster & Quick Moderation Actions -->
        <div>
            <!-- Poster Card -->
            <div style="background-color: var(--white); border-radius: 8px; padding: 20px; box-shadow: 0 2px 12px var(--shadow); border: 1px solid var(--light-grey); margin-bottom: 24px; text-align: center;">
                <h4 style="font-family: var(--font-heading); font-size: 0.8rem; letter-spacing: 0.5px; margin-bottom: 12px; color: var(--dark);">
                    POSTER FILM
                </h4>
                <img src="../<?= e(get_poster_url($submission['image_path'])) ?>" 
                     alt="<?= e($submission['title']) ?>" 
                     style="width: 100%; max-height: 400px; object-fit: contain; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <div style="font-size: 0.75rem; color: var(--lighter-grey); margin-top: 8px; word-break: break-all;">
                    File: <?= e($submission['image_path']) ?>
                </div>
            </div>

            <!-- Moderation Form Card -->
            <div style="background-color: var(--white); border-radius: 8px; padding: 24px; box-shadow: 0 2px 12px var(--shadow); border: 1px solid var(--light-grey);">
                <h4 style="font-family: var(--font-heading); font-size: 0.85rem; letter-spacing: 0.5px; margin-bottom: 16px; color: var(--dark);">
                    TINDAKAN MODERASI
                </h4>

                <form action="submission-action.php" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="id" value="<?= $submission['id'] ?>">

                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="status_select">Status Publikasi</label>
                        <select name="status" id="status_select" class="form-control" style="font-weight: 600;">
                            <option value="pending" <?= $submission['status'] === 'pending' ? 'selected' : '' ?>>Pending (Menunggu)</option>
                            <option value="approved" <?= $submission['status'] === 'approved' ? 'selected' : '' ?>>Approved (Terbitkan)</option>
                            <option value="rejected" <?= $submission['status'] === 'rejected' ? 'selected' : '' ?>>Rejected (Tolak)</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 18px;">
                        <label for="admin_notes">Catatan Editorial / Alasan</label>
                        <textarea name="admin_notes" id="admin_notes" class="form-control" rows="3" 
                                  placeholder="Tambahkan catatan editorial atau alasan persetujuan/penolakan..."><?= e($submission['admin_notes']) ?></textarea>
                    </div>

                    <button type="submit" class="btn-submit" style="width: 100%; padding: 10px; font-size: 0.85rem;">
                        SIMPAN PERUBAHAN
                    </button>
                </form>

                <div style="margin-top: 18px; padding-top: 16px; border-top: 1px solid var(--light-grey); display: flex; flex-direction: column; gap: 8px;">
                    <a href="submission-edit.php?id=<?= $submission['id'] ?>" class="btn-action btn-edit" style="justify-content: center; padding: 10px;">
                        Edit Teks Tulisan & Judul
                    </a>

                    <?php if ($submission['status'] === 'approved'): ?>
                        <a href="../review-detail.php?id=<?= $submission['id'] ?>" target="_blank" class="btn-action btn-view" style="justify-content: center; padding: 10px;">
                            Lihat di Website Publik &rarr;
                        </a>
                    <?php endif; ?>

                    <button type="button" class="btn-action btn-delete" style="justify-content: center; padding: 10px;"
                            onclick="confirmDelete(<?= $submission['id'] ?>, '<?= e(addslashes($submission['title'])) ?>')">
                        Hapus Pengajuan
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Delete Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <h3 class="modal-title">Konfirmasi Hapus Ulasan</h3>
        <p style="font-size: 0.9rem; color: var(--secondary-text); margin-bottom: 12px;">
            Apakah Anda yakin ingin menghapus ulasan: <strong id="deleteItemTitle"></strong>?
        </p>
        <p style="font-size: 0.8rem; color: #C62828;">
            Tindakan ini tidak dapat dibatalkan.
        </p>

        <form action="submission-action.php" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="deleteItemId" value="">

            <div class="modal-actions">
                <button type="button" class="btn-action btn-view" onclick="closeDeleteModal()">Batal</button>
                <button type="submit" class="btn-action btn-delete" style="padding: 8px 16px;">Ya, Hapus</button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmDelete(id, title) {
    document.getElementById("deleteItemId").value = id;
    document.getElementById("deleteItemTitle").textContent = title;
    document.getElementById("deleteModal").classList.add("active");
}

function closeDeleteModal() {
    document.getElementById("deleteModal").classList.remove("active");
}

document.getElementById("deleteModal").addEventListener("click", function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
