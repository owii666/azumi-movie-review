<?php
/**
 * Admin Upcoming Films Management
 * Azumi Movie Reviews
 */

$pageTitle = "Kelola Film Upcoming - Azumi Movie Reviews";
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getDbConnection();

// Fetch all upcoming films
$stmt = $pdo->prepare("SELECT * FROM upcoming_films ORDER BY created_at DESC, id DESC");
$stmt->execute();
$upcomingFilms = $stmt->fetchAll();
?>

<main class="admin-container">
    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-title">
                Kelola Film Upcoming
            </h1>
            <p class="admin-page-subtitle">
                Daftar film yang akan atau sedang ditonton oleh admin. Menampilkan poster dan judul film di website publik.
            </p>
        </div>
        <button type="button" class="btn-submit" onclick="toggleAddForm()" style="padding: 8px 16px; font-size: 0.8rem;">
            + Tambah Film Upcoming
        </button>
    </div>

    <!-- Collapsible Add Film Form -->
    <div id="addFilmSection" style="display: none; background-color: var(--white); border-radius: 8px; padding: 28px; box-shadow: 0 4px 16px var(--shadow); border: 1px solid var(--light-grey); margin-bottom: 28px; border-top: 4px solid var(--accent);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
            <h3 style="font-family: var(--font-heading); font-size: 1.1rem; color: var(--dark); margin: 0;">
                Tambah Film ke Watchlist Upcoming
            </h3>
            <button type="button" onclick="toggleAddForm()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--lighter-grey);">&times;</button>
        </div>

        <form action="upcoming-action.php" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add">

            <div class="admin-form-grid-2">
                <div class="form-group">
                    <label for="title">Judul Film <span class="required">*</span></label>
                    <input type="text" name="title" id="title" class="form-control" required 
                           placeholder="Contoh: Still Walking (2008)" maxlength="255">
                    <div class="form-help">Sertakan judul film dan tahun rilis jika ada.</div>
                </div>

                <div class="form-group">
                    <label for="poster">File Poster Film <span class="required">*</span></label>
                    <input type="file" name="poster" id="poster" class="form-control" accept=".jpg,.jpeg,.png,.webp" required>
                    <div class="form-help">Format: JPG, JPEG, PNG, WebP &bull; Maksimal 2 MB.</div>
                    
                    <!-- Poster Image Preview -->
                    <div id="upcomingPreviewBox" class="image-preview-container" style="display: none; margin-top: 10px;">
                        <div class="image-preview-card" style="padding: 10px 14px;">
                            <div class="image-preview-thumb-wrap" style="width: 50px; height: 72px;">
                                <img id="upcomingPreviewImg" src="" alt="Pratinjau Poster" class="image-preview-thumb">
                            </div>
                            <div class="image-preview-meta">
                                <span class="preview-badge">PRATINJAU POSTER</span>
                                <span id="upcomingPreviewName" class="preview-filename" style="font-size: 0.82rem;"></span>
                                <span id="upcomingPreviewSize" class="preview-filesize"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-top: 15px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-action btn-view" onclick="toggleAddForm()" style="padding: 10px 16px;">
                    Batal
                </button>
                <button type="submit" class="btn-submit" style="padding: 10px 22px;">
                    SIMPAN FILM UPCOMING
                </button>
            </div>
        </form>
    </div>

    <!-- Table of Upcoming Films -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width: 80px;">Poster</th>
                        <th>Judul Film</th>
                        <th>Tanggal Ditambahkan</th>
                        <th style="text-align: right; min-width: 160px;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($upcomingFilms)): ?>
                        <?php foreach ($upcomingFilms as $film): ?>
                            <tr>
                                <td>
                                    <img src="../<?= e(get_poster_url($film['poster_path'])) ?>" 
                                         alt="<?= e($film['title']) ?>" 
                                         class="table-poster-thumb">
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--dark); font-size: 0.95rem;">
                                        <?= e($film['title']) ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--lighter-grey);">
                                        File: <?= e($film['poster_path']) ?>
                                    </div>
                                </td>
                                <td style="white-space: nowrap; font-size: 0.85rem;">
                                    <?= format_date($film['created_at']) ?>
                                </td>
                                <td style="text-align: right;">
                                    <div class="action-buttons" style="justify-content: flex-end;">
                                        <a href="upcoming-edit.php?id=<?= $film['id'] ?>" class="btn-action btn-edit">
                                            Edit
                                        </a>
                                        <button type="button" class="btn-action btn-delete" 
                                                onclick="confirmDeleteUpcoming(<?= $film['id'] ?>, '<?= e(addslashes($film['title'])) ?>')">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 45px; color: var(--lighter-grey);">
                                Belum ada film upcoming. Klik tombol "+ Tambah Film Upcoming" di atas untuk menambahkan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Delete Upcoming Modal -->
<div class="modal-overlay" id="deleteUpcomingModal">
    <div class="modal-box">
        <h3 class="modal-title">Konfirmasi Hapus Film Upcoming</h3>
        <p style="font-size: 0.9rem; color: var(--secondary-text); margin-bottom: 12px;">
            Apakah Anda yakin ingin menghapus film: <strong id="deleteFilmTitle"></strong>?
        </p>
        <p style="font-size: 0.8rem; color: #C62828;">
            Tindakan ini permanen dan akan menghapus film dari daftar Upcoming publik.
        </p>

        <form action="upcoming-action.php" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="deleteFilmId" value="">

            <div class="modal-actions">
                <button type="button" class="btn-action btn-view" onclick="closeDeleteUpcomingModal()">Batal</button>
                <button type="submit" class="btn-action btn-delete" style="padding: 8px 16px;">Ya, Hapus</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAddForm() {
    const sec = document.getElementById("addFilmSection");
    if (sec.style.display === "none" || sec.style.display === "") {
        sec.style.display = "block";
        document.getElementById("title").focus();
    } else {
        sec.style.display = "none";
    }
}

function confirmDeleteUpcoming(id, title) {
    document.getElementById("deleteFilmId").value = id;
    document.getElementById("deleteFilmTitle").textContent = title;
    document.getElementById("deleteUpcomingModal").classList.add("active");
}

function closeDeleteUpcomingModal() {
    document.getElementById("deleteUpcomingModal").classList.remove("active");
}

document.getElementById("deleteUpcomingModal").addEventListener("click", function(e) {
    if (e.target === this) closeDeleteUpcomingModal();
});

// Live image preview for upcoming poster
const upcomingPosterInput = document.getElementById("poster");
if (upcomingPosterInput) {
    upcomingPosterInput.addEventListener("change", function () {
        const previewBox = document.getElementById("upcomingPreviewBox");
        const previewImg = document.getElementById("upcomingPreviewImg");
        const previewName = document.getElementById("upcomingPreviewName");
        const previewSize = document.getElementById("upcomingPreviewSize");

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
