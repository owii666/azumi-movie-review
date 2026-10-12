<?php
/**
 * Submit Your Film Page
 * Azumi Movie Reviews
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$errors = [];
$titleVal = '';
$authorVal = '';
$contentVal = '';

// Generate submission nonce for idempotency
if (empty($_SESSION['submit_nonce'])) {
    $_SESSION['submit_nonce'] = bin2hex(random_bytes(16));
}
$currentNonce = $_SESSION['submit_nonce'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Spam honeypot protection
    if (!empty($_POST['website_hp'])) {
        header('Location: submit.php');
        exit;
    }

    // 2. CSRF Token validation
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Sesi keamanan tidak valid atau telah kedaluwarsa. Silakan muat ulang halaman.";
    }

    // 3. Idempotency nonce validation to prevent duplicate submissions
    $submittedNonce = trim($_POST['submit_nonce'] ?? '');
    if (!empty($_SESSION['last_processed_nonce']) && $_SESSION['last_processed_nonce'] === $submittedNonce) {
        // Form was already successfully submitted and processed! Redirect to GET
        header('Location: submit.php');
        exit;
    }

    // 4. Inputs Sanitization & Validation
    $titleVal = trim($_POST['title'] ?? '');
    $authorVal = trim($_POST['author_name'] ?? '');
    $contentVal = trim($_POST['content'] ?? '');

    if (empty($titleVal)) {
        $errors[] = "Judul tulisan atau film wajib diisi.";
    } elseif (mb_strlen($titleVal) < 3 || mb_strlen($titleVal) > 255) {
        $errors[] = "Judul film harus antara 3 hingga 255 karakter.";
    }

    if (mb_strlen($authorVal) > 100) {
        $errors[] = "Nama pengirim maksimal 100 karakter.";
    }

    // 5. Server-side Paragraph Validation (Minimal 2, Maksimal 5 paragraf)
    $paragraphCount = count_paragraphs($contentVal);
    if ($paragraphCount < 2) {
        $errors[] = "Isi tulisan minimal terdiri dari 2 paragraf. Pisahkan setiap paragraf dengan menekan tombol Enter dua kali.";
    } elseif ($paragraphCount > 5) {
        $errors[] = "Isi tulisan maksimal 5 paragraf. Silakan ringkas ulasan Anda.";
    }

    // 6. Image File Validation
    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = "File gambar poster atau ulasan wajib diunggah.";
    } elseif ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Gagal mengunggah file gambar. Silakan gunakan file gambar lain.";
    } else {
        $file = $_FILES['image'];
        $maxSize = 2 * 1024 * 1024; // 2 MB

        if ($file['size'] > $maxSize) {
            $errors[] = "Ukuran gambar melebihi batas maksimal 2 MB.";
        } else {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $fileInfo = pathinfo($file['name']);
            $fileExt = strtolower($fileInfo['extension'] ?? '');

            if (!in_array($fileExt, $allowedExtensions, true)) {
                $errors[] = "Format file tidak diizinkan. Hanya file JPG, JPEG, PNG, dan WebP yang diperbolehkan.";
            } else {
                // Verify real MIME type using finfo
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                $allowedMimes = ['image/jpeg', 'image/pjpeg', 'image/png', 'image/webp'];
                if (!in_array($mimeType, $allowedMimes, true)) {
                    $errors[] = "File yang diunggah bukan format gambar yang valid.";
                } else {
                    // Verify with getimagesize
                    $imageDetails = @getimagesize($file['tmp_name']);
                    if ($imageDetails === false) {
                        $errors[] = "File gambar terdeteksi rusak atau tidak dapat dibaca.";
                    }
                }
            }
        }
    }

    // 7. Check for rapid identical duplicate submission in database
    if (empty($errors)) {
        try {
            $pdo = getDbConnection();
            $checkDup = $pdo->prepare("
                SELECT id FROM film_submissions 
                WHERE title = :title AND content = :content AND submitted_at >= (NOW() - INTERVAL 30 SECOND) 
                LIMIT 1
            ");
            $checkDup->execute([':title' => $titleVal, ':content' => $contentVal]);
            if ($checkDup->fetch()) {
                // Duplicate already saved recently! Redirect safely without re-inserting
                $_SESSION['last_processed_nonce'] = $submittedNonce;
                $_SESSION['submit_nonce'] = bin2hex(random_bytes(16));
                set_flash_message('success', 'Berhasil terkirim. Tunggu admin ACC review Anda.');
                header('Location: submit.php');
                exit;
            }
        } catch (Exception $dupEx) {
            // Ignore check error and continue to main try-catch
        }
    }

    // 8. Process Database Insertion if no errors
    if (empty($errors)) {
        try {
            $uploadDir = __DIR__ . '/uploads/posters/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $uniqueFilename = bin2hex(random_bytes(8)) . '_' . time() . '.' . $fileExt;
            $targetPath = $uploadDir . $uniqueFilename;

            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new Exception("Gagal menyimpan file gambar ke server.");
            }

            $authorFinal = !empty($authorVal) ? $authorVal : 'Anonim';
            $pdo = getDbConnection();

            $stmt = $pdo->prepare("
                INSERT INTO film_submissions 
                (title, author_name, category, content, image_path, status, submitted_at) 
                VALUES (:title, :author_name, 'COMMUNITY REVIEWS', :content, :image_path, 'pending', NOW())
            ");

            $stmt->execute([
                ':title'       => $titleVal,
                ':author_name' => $authorFinal,
                ':content'     => $contentVal,
                ':image_path'  => $uniqueFilename
            ]);

            // Save processed nonce and regenerate for next submission
            if (!empty($submittedNonce)) {
                $_SESSION['last_processed_nonce'] = $submittedNonce;
            }
            $_SESSION['submit_nonce'] = bin2hex(random_bytes(16));

            // Set exact required success flash message
            set_flash_message('success', 'Berhasil terkirim. Tunggu admin ACC review Anda.');

            // POST-Redirect-GET Pattern: Redirect to prevent duplicate submit on page refresh
            header('Location: submit.php');
            exit;

        } catch (Exception $e) {
            // Clean user-friendly error without leaking technical database/query details
            $errors[] = "Pengiriman review gagal diproses karena kendala server. Silakan coba sesaat lagi.";
        }
    }
}

// Render Header & Page (only reached on GET or if validation errors exist)
$pageTitle = "Submit Your Film - Azumi Movie Reviews";
require_once __DIR__ . '/includes/header.php';
?>

<main>
    <section class="submit-film-page">
        <div class="container">
            <h1>Submit Your Film</h1>
            <p>
                Punya rekomendasi film menarik atau ulasan mendalam yang ingin dibagikan kepada komunitas penikmat sinema?
                Kirimkan tulisan dan poster film favoritmu melalui formulir di bawah ini untuk ditinjau oleh tim kurator Azumi Movie Reviews.
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

            <div class="submit-form-card">
                <form action="submit.php" method="POST" enctype="multipart/form-data" id="submitFilmForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="submit_nonce" value="<?= e($currentNonce) ?>">
                    
                    <!-- Honeypot spam trap (hidden from real users) -->
                    <div style="display: none;">
                        <label for="website_hp">Leave this empty</label>
                        <input type="text" name="website_hp" id="website_hp" tabindex="-1" autocomplete="off">
                    </div>

                    <!-- Judul Tulisan / Film -->
                    <div class="form-group">
                        <label for="title">Judul Tulisan / Film <span class="required">*</span></label>
                        <input type="text" name="title" id="title" class="form-control" 
                               value="<?= e($titleVal) ?>" 
                               placeholder="Contoh: Still Walking (2008)" required maxlength="255">
                        <div class="form-help">Sertakan judul film dan tahun rilis (jika ada).</div>
                    </div>

                    <!-- Nama Pengirim (Opsional) -->
                    <div class="form-group">
                        <label for="author_name">Nama Pengirim <span class="hint">(Opsional)</span></label>
                        <input type="text" name="author_name" id="author_name" class="form-control" 
                               value="<?= e($authorVal) ?>" 
                               placeholder="Nama atau nama pena kamu (kosongkan jika anonim)" maxlength="100">
                    </div>

                    <!-- Isi Tulisan (2 - 5 Paragraf) -->
                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 8px;">
                            <label for="content" style="margin-bottom: 0;">
                                Isi Tulisan <span class="required">*</span>
                                <span class="hint">(Wajib 2 hingga 5 paragraf)</span>
                            </label>
                            <span id="pCountDisplay" class="paragraph-counter">0 paragraf</span>
                        </div>
                        <textarea name="content" id="content" class="form-control" rows="8" 
                                  placeholder="Tulis ulasan Anda di sini.&#10;&#10;Pisahkan setiap paragraf baru dengan menekan tombol Enter dua kali (jarak 1 baris kosong).&#10;&#10;Ulasan harus terdiri dari minimal 2 dan maksimal 5 paragraf." required><?= e($contentVal) ?></textarea>
                        <div class="form-help">
                            * Penting: Tekan tombol <strong>Enter dua kali</strong> untuk memisahkan setiap paragraf baru.
                        </div>
                    </div>

                    <!-- Upload Gambar Poster -->
                    <div class="form-group">
                        <label for="image">File Gambar Poster / Gambar Pendukung <span class="required">*</span></label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="image" id="image" accept=".jpg,.jpeg,.png,.webp" required>
                            <div class="file-upload-label" id="fileUploadLabel">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                                <span id="fileNameText"><strong>Klik untuk memilih gambar</strong> atau seret file ke sini</span>
                                <span style="font-size: 0.8rem; color: var(--lighter-grey);">Format: JPG, JPEG, PNG, WebP &bull; Maksimal 2 MB</span>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 30px; text-align: right;">
                        <button type="submit" class="btn-submit" id="submitBtn">
                            KIRIM PENGAJUAN FILM
                        </button>
                    </div>
                </form>
            </div>

            <!-- Kontak Info Kotak Referensi -->
            
        </div>
    </section>
</main>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const contentArea = document.getElementById("content");
    const countDisplay = document.getElementById("pCountDisplay");
    const imageInput = document.getElementById("image");
    const fileNameText = document.getElementById("fileNameText");
    const submitForm = document.getElementById("submitFilmForm");

    function updateParagraphCount() {
        if (!contentArea || !countDisplay) return;
        const text = contentArea.value.trim();
        if (!text) {
            countDisplay.textContent = "0 paragraf";
            countDisplay.className = "paragraph-counter";
            return;
        }

        // Split by 2 or more newlines
        const parts = text.split(/\n\s*\n+/);
        let validCount = 0;
        for (let i = 0; i < parts.length; i++) {
            if (parts[i].trim().length > 5) {
                validCount++;
            }
        }

        countDisplay.textContent = validCount + " paragraf";
        if (validCount >= 2 && validCount <= 5) {
            countDisplay.className = "paragraph-counter valid";
        } else {
            countDisplay.className = "paragraph-counter invalid";
        }
    }

    if (contentArea) {
        contentArea.addEventListener("input", updateParagraphCount);
        updateParagraphCount(); // Initial calculation
    }

    if (imageInput && fileNameText) {
        imageInput.addEventListener("change", function () {
            if (this.files && this.files.length > 0) {
                fileNameText.innerHTML = "<strong>File terpilih:</strong> " + this.files[0].name + " (" + (this.files[0].size / 1024 / 1024).toFixed(2) + " MB)";
            } else {
                fileNameText.innerHTML = "<strong>Klik untuk memilih gambar</strong> atau seret file ke sini";
            }
        });
    }

    // Disable submit button on form submission to prevent multiple clicks
    if (submitForm) {
        submitForm.addEventListener("submit", function () {
            const submitBtn = document.getElementById("submitBtn");
            if (submitBtn) {
                setTimeout(function() {
                    submitBtn.disabled = true;
                    submitBtn.innerText = "MENGIRIM PENGAJUAN...";
                    submitBtn.style.opacity = "0.7";
                    submitBtn.style.cursor = "not-allowed";
                }, 10);
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
