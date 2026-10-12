<?php
/**
 * All Reviews Page
 * Azumi Movie Reviews
 */

$pageTitle = "All Film Reviews - Azumi Movie Reviews";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/database.php';

$pdo = getDbConnection();

// Fetch all approved submissions
$stmt = $pdo->prepare("SELECT * FROM film_submissions WHERE status = 'approved' ORDER BY COALESCE(reviewed_at, submitted_at) DESC, id DESC");
$stmt->execute();
$reviews = $stmt->fetchAll();
?>

<main>
    <section class="all-reviews-page">
        <div class="container">
            <h1>All Film Reviews</h1>
            <p style="text-align: center; color: var(--lighter-grey); margin-bottom: 30px; font-size: 0.95rem;">
                Kumpulan ulasan film sinema Asia pilihan dan karya yang diterbitkan oleh kurator Azumi.
            </p>

            <div class="review-grid-page">
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $rev): ?>
                        <?php 
                        $paragraphs = get_paragraphs($rev['content']);
                        $excerpt = !empty($paragraphs) ? $paragraphs[0] : $rev['content'];
                        if (mb_strlen($excerpt) > 130) {
                            $excerpt = mb_substr($excerpt, 0, 130) . '...';
                        }
                        ?>
                        <div class="review-card-large">
                            <div class="review-card-poster">
                                <img src="<?= e(get_poster_url($rev['image_path'])) ?>" alt="<?= e($rev['title']) ?>" loading="lazy">
                            </div>
                            <div class="review-content">
                                <div>
                                    <span class="category"><?= e($rev['category'] ?: 'REVIEWS') ?></span>
                                    <h4><a href="review-detail.php?id=<?= $rev['id'] ?>"><?= e($rev['title']) ?></a></h4>
                                    <div class="review-meta">
                                        <span class="review-author"><?= e($rev['author_name'] ?: 'Azumi Team') ?></span> &bull; 
                                        <span class="review-date"><?= format_date($rev['reviewed_at'] ?: $rev['submitted_at']) ?></span>
                                    </div>
                                    <p class="review-excerpt"><?= e($excerpt) ?></p>
                                </div>
                                <div class="review-action">
                                    <a href="review-detail.php?id=<?= $rev['id'] ?>" class="read-review-btn">READ REVIEW</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; color: var(--lighter-grey); grid-column: 1 / -1; padding: 40px 0;">
                        Belum ada ulasan film yang diterbitkan saat ini.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
