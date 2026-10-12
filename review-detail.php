<?php
/**
 * Single Review Detail Page
 * Azumi Movie Reviews
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: reviews.php');
    exit;
}

$pdo = getDbConnection();

// Allow admin to preview, but visitors can only view approved reviews
if (is_admin_logged_in()) {
    $stmt = $pdo->prepare("SELECT * FROM film_submissions WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM film_submissions WHERE id = :id AND status = 'approved' LIMIT 1");
    $stmt->execute([':id' => $id]);
}

$review = $stmt->fetch();

if (!$review) {
    header('HTTP/1.0 404 Not Found');
    $pageTitle = "Review Tidak Ditemukan - Azumi Movie Reviews";
    require_once __DIR__ . '/includes/header.php';
    echo '<main class="container" style="padding: 60px 0; text-align: center;">';
    echo '<h2>Ulasan Film Tidak Ditemukan</h2>';
    echo '<p style="margin-top: 15px; color: var(--lighter-grey);">Ulasan yang Anda cari mungkin belum disetujui atau sudah dihapus.</p>';
    echo '<a href="reviews.php" class="read-review-btn" style="margin-top: 25px; display: inline-block;">KEMBALI KE DAFTAR ULASAN</a>';
    echo '</main>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = e($review['title']) . " - Azumi Movie Reviews";
require_once __DIR__ . '/includes/header.php';

$paragraphs = get_paragraphs($review['content']);
?>

<main class="detail-review-page">
    <div class="container">
        <div class="content-wrapper">
            <article class="review-article">
                <span class="category"><?= e($review['category'] ?: 'REVIEWS') ?></span>
                
                <?php if ($review['status'] !== 'approved'): ?>
                    <div style="margin: 10px 0;">
                        <span class="badge badge-<?= e($review['status']) ?>">Status: <?= strtoupper(e($review['status'])) ?> (Admin Preview)</span>
                    </div>
                <?php endif; ?>

                <h1><?= e($review['title']) ?></h1>

                <div class="review-meta-detail">
                    <span class="meta-author">
                        <svg class="meta-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <?= e($review['author_name'] ?: 'Azumi Team') ?>
                    </span>
                    <span class="meta-date">
                        <svg class="meta-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <?= format_date($review['submitted_at']) ?>
                    </span>
                </div>

                <img src="<?= e(get_poster_url($review['image_path'])) ?>" alt="<?= e($review['title']) ?>" class="article-image">

                <section class="review-paragraphs" style="margin-top: 30px;">
                    <?php if (!empty($paragraphs)): ?>
                        <?php foreach ($paragraphs as $index => $paragraph): ?>
                            <?php if ($index === 0): ?>
                                <p class="teaser" style="font-weight: 500; font-size: 1.1rem; line-height: 1.8; color: var(--dark); margin-bottom: 24px;">
                                    <?= nl2br(e($paragraph)) ?>
                                </p>
                            <?php else: ?>
                                <p style="margin-bottom: 20px; line-height: 1.8; color: var(--secondary-text);">
                                    <?= nl2br(e($paragraph)) ?>
                                </p>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?= render_paragraphs_html($review['content']) ?>
                    <?php endif; ?>
                </section>
            </article>

            <aside class="sidebar">
                <div class="sidebar-block available-now">
                    <h4>AVAILABLE NOW</h4>
                    <p>Watch on CathayPlay.com</p>
                    <span>Access over 900 Southeast & Sinophone Independent films directly on their official streaming platform.</span>
                </div>
                
                <div class="sidebar-block film-details">
                    <h4>FILM DETAILS</h4>
                    <p><strong>Title:</strong> <?= e($review['title']) ?></p>
                    <p><strong>Contributor:</strong> <?= e($review['author_name'] ?: 'Azumi Team') ?></p>
                    <p><strong>Published:</strong> <?= format_date($review['submitted_at']) ?></p>
                    <p><strong>Status:</strong> <?= ucfirst(e($review['status'])) ?></p>
                </div>
            </aside>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
