<?php
/**
 * Admin Upcoming Film Edit Page
 * Azumi Movie Reviews
 */

$pageTitle = "Sunting Film Upcoming - Azumi Movie Reviews";
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header('Location: upcoming.php');
    exit;
}

$pdo = getDbConnection();
$stmt = $pdo->prepare("SELECT * FROM upcoming_films WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$film = $stmt->fetch();

if (!$film) {
    set_flash_message('danger', 'Film upcoming tidak ditemukan.');
    header('Location: upcoming.php');
    exit;
}
?>

<main class="admin-container">
    <div style="margin-bottom: 20px;">
        <a href="upcoming.php" style="color: var(--secondary-text); font-size: 0.85rem; text-decoration: none;">
            &larr; Kembali ke Kelola Film Upcoming
        </a>
    </div>

    <div style="background-color: var(--white); border-radius: 8px; padding: 36px; box-shadow: 0 2px 12px var(--shadow); border: 1px solid var(--light-grey); max-width: 650px; margin: 0 auto;">
        <h1 style="font-family: var(--font-heading); font-size: 1.3rem; color: var(--dark); margin-bottom: 6px;">
            Sunting Film Upcoming
        </h1>
        <p style="color: var(--lighter-grey); font-size: 0.85rem; margin-bottom: 24px;">
            Perbarui judul film atau unggah poster pengganti baru.
        </p>

        <form action="upcoming-action.php" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?= $film['id'] ?>">

            <div class="form-group">
                <label for="title">Judul Film <span class="required">*</span></label>
                <input type="text" name="title" id="title" class="form-control" 
                       value="<?= e($film['title']) ?>" required maxlength="255">
            </div>

            <div class="form-group">
                <label>Poster Saat Ini</label>
                <div style="margin-bottom: 12px; display: flex; align-items: center; gap: 15px;">
                    <img src="../<?= e(get_poster_url($film['poster_path'])) ?>" 
                         alt="<?= e($film['title']) ?>" 
                         style="width: 80px; height: 110px; object-fit: cover; border-radius: 4px; box-shadow: 0 1px 4px rgba(0,0,0,0.15);">
                    <div style="font-size: 0.8rem; color: var(--lighter-grey);">
                        File: <?= e($film['poster_path']) ?>
                    </div>
                </div>

                <label for="poster">Ganti Poster <span class="hint">(Opsional, kosongkan jika tidak ingin mengubah poster)</span></label>
                <input type="file" name="poster" id="poster" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                <div class="form-help">Format: JPG, JPEG, PNG, WebP &bull; Maksimal 2 MB.</div>

                <!-- Poster Image Preview -->
                <div id="upcomingEditPreviewBox" class="image-preview-container" style="display: none; margin-top: 12px;">
                    <div class="image-preview-card" style="padding: 10px 14px;">
                        <div class="image-preview-thumb-wrap" style="width: 50px; height: 72px;">
                            <img id="upcomingEditPreviewImg" src="" alt="Pratinjau Poster Baru" class="image-preview-thumb">
                        </div>
                        <div class="image-preview-meta">
                            <span class="preview-badge">PRATINJAU POSTER BARU</span>
                            <span id="upcomingEditPreviewName" class="preview-filename" style="font-size: 0.82rem;"></span>
                            <span id="upcomingEditPreviewSize" class="preview-filesize"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-top: 30px; display: flex; justify-content: flex-end; gap: 10px;">
                <a href="upcoming.php" class="btn-action btn-view" style="padding: 10px 18px;">
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
const editPosterInput = document.getElementById("poster");
if (editPosterInput) {
    editPosterInput.addEventListener("change", function () {
        const previewBox = document.getElementById("upcomingEditPreviewBox");
        const previewImg = document.getElementById("upcomingEditPreviewImg");
        const previewName = document.getElementById("upcomingEditPreviewName");
        const previewSize = document.getElementById("upcomingEditPreviewSize");

        if (this.files && this.files[0]) {
            const file = this.files[0];
            const sizeMb = (file.size / (1024 * 1024)).toFixed(2);
            if (file.type && file.type.startsWith("image/")) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    if (previewImg) previewImg.src = e.target.result;
                    if (previewName) previewName.textContent = file.name;
                    if (previewSize) previewSize.textContent = sizeMb + " MB";
                    if (previewBox) previewBox.style.display = "block";
                };
                reader.readAsDataURL(file);
            } else {
                if (previewBox) previewBox.style.display = "none";
            }
        } else {
            if (previewBox) previewBox.style.display = "none";
        }
    });
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
