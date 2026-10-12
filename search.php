<?php
/**
 * Public Search Results Page
 * Azumi Movie Reviews
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$query = trim($_GET['q'] ?? '');
$pageTitle = !empty($query) ? 'Hasil Pencarian: "' . e($query) . '" - Azumi Movie Reviews' : 'Pencarian Film - Azumi Movie Reviews';

require_once __DIR__ . '/includes/header.php';

$reviews = [];
$upcoming = [];

if (!empty($query)) {
    $pdo = getDbConnection();
    $searchTerm = '%' . $query . '%';

    // 1. Search Approved Reviews Only
    $revStmt = $pdo->prepare("
        SELECT id, title, author_name, category, content, image_path, submitted_at, reviewed_at
        FROM film_submissions
        WHERE status = 'approved'
          AND (title LIKE :q1 OR author_name LIKE :q2 OR content LIKE :q3)
        ORDER BY COALESCE(reviewed_at, submitted_at) DESC
        LIMIT 20
    ");
    $revStmt->execute([
        ':q1' => $searchTerm,
        ':q2' => $searchTerm,
        ':q3' => $searchTerm
    ]);
    $reviews = $revStmt->fetchAll();

    // 2. Search Upcoming Films
    $upStmt = $pdo->prepare("
        SELECT id, title, poster_path, created_at
        FROM upcoming_films
        WHERE title LIKE :q
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $upStmt->execute([':q' => $searchTerm]);
    $upcoming = $upStmt->fetchAll();
}

$totalFound = count($reviews) + count($upcoming);
?>

<main>
    <section class="container" style="padding-top: 24px; padding-bottom: 40px;">
        <div style="border-bottom: 1.5px solid var(--light-grey); padding-bottom: 20px; margin-bottom: 30px;">
            <span class="news-label" style="display: inline-block; margin-bottom: 8px;">PENCARIAN</span>
            <h1 style="font-family: var(--font-title); font-size: 2rem; color: var(--dark); margin-bottom: 6px;">
                <?php if (!empty($query)): ?>
                    Hasil Pencarian untuk: "<?= e($query) ?>"
                <?php else: ?>
                    Cari Film & Ulasan
                <?php endif; ?>
            </h1>

            <?php if (!empty($query)): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                    <p style="color: var(--lighter-grey); font-size: 0.9rem; margin: 0;">
                        Ditemukan <strong><?= $totalFound ?></strong> hasil yang cocok (<?= count($reviews) ?> ulasan, <?= count($upcoming) ?> upcoming).
                    </p>
                    <a href="search.php" class="filter-pill" style="font-size: 0.8rem; padding: 6px 14px; background-color: #EAE8DE;">
                        &times; Bersihkan Pencarian
                    </a>
                </div>
            <?php else: ?>
                <p style="color: var(--lighter-grey); font-size: 0.9rem; margin: 0;">
                    Silakan masukkan kata kunci judul film atau ulasan pada kolom pencarian di atas.
                </p>
            <?php endif; ?>
        </div>

        <?php if (!empty($query)): ?>
            <!-- Section 1: Ulasan Film (Approved) -->
            <?php if (!empty($reviews)): ?>
                <div style="margin-bottom: 40px;">
                    <h3 style="font-family: var(--font-heading); font-size: 1rem; letter-spacing: 1.5px; color: var(--dark); margin-bottom: 20px;">
                        ULASAN FILM TERBIT (<?= count($reviews) ?>)
                    </h3>
                    <div class="review-grid-page">
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
                    </div>
                </div>
            <?php endif; ?>

            <!-- Section 2: Upcoming Films -->
            <?php if (!empty($upcoming)): ?>
                <div>
                    <h3 style="font-family: var(--font-heading); font-size: 1rem; letter-spacing: 1.5px; color: var(--dark); margin-bottom: 20px;">
                        UPCOMING WATCHLIST (<?= count($upcoming) ?>)
                    </h3>
                    <div class="upcoming-grid">
                        <?php foreach ($upcoming as $film): ?>
                            <div class="upcoming-card">
                                <div class="upcoming-poster-box">
                                    <img src="<?= e(get_poster_url($film['poster_path'])) ?>" 
                                         alt="<?= e($film['title']) ?>" 
                                         class="upcoming-poster-img"
                                         loading="lazy">
                                </div>
                                <div class="upcoming-info">
                                    <h3 class="upcoming-title"><?= e($film['title']) ?></h3>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- If Nothing Found -->
            <?php if (empty($reviews) && empty($upcoming)): ?>
                <div style="text-align: center; padding: 60px 20px; background-color: var(--white); border-radius: 8px; border: 1px solid var(--light-grey); max-width: 650px; margin: 30px auto;">
                    <h3 style="font-family: var(--font-title); font-size: 1.4rem; color: var(--dark); margin-bottom: 10px;">
                        Tidak Ada Hasil Ditemukan
                    </h3>
                    <p style="color: var(--lighter-grey); font-size: 0.95rem; margin-bottom: 20px;">
                        Tidak ada ulasan atau film yang cocok dengan kata kunci "<strong><?= e($query) ?></strong>".
                    </p>
                    <a href="reviews.php" class="btn-submit" style="text-decoration: none; display: inline-block;">
                        LIHAT SEMUA ULASAN FILM
                    </a>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
