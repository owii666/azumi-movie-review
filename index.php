<?php
/**
 * Home Page (Beranda)
 * Azumi Movie Reviews
 */

$pageTitle = "Azumi Movie Reviews - Asian Cinema";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/database.php';

$pdo = getDbConnection();

// 1. Fetch Latest Approved Review (ordered by publication/approval time)
$stmtLatest = $pdo->prepare("
    SELECT * FROM film_submissions 
    WHERE status = 'approved' 
    ORDER BY COALESCE(reviewed_at, submitted_at) DESC, id DESC 
    LIMIT 1
");
$stmtLatest->execute();
$latestReview = $stmtLatest->fetch();

$latestId = $latestReview ? (int)$latestReview['id'] : 0;

// 2. Fetch Other Recent Approved Reviews (excluding the latest review)
$stmtOthers = $pdo->prepare("
    SELECT * FROM film_submissions 
    WHERE status = 'approved' AND id != :latestId 
    ORDER BY COALESCE(reviewed_at, submitted_at) DESC, id DESC 
    LIMIT 6
");
$stmtOthers->execute([':latestId' => $latestId]);
$otherReviews = $stmtOthers->fetchAll();

// 3. Fetch Upcoming Watchlist Films from database
$stmtUpcoming = $pdo->prepare("
    SELECT id, title, poster_path 
    FROM upcoming_films 
    ORDER BY created_at DESC, id DESC 
    LIMIT 4
");
$stmtUpcoming->execute();
$upcomingHome = $stmtUpcoming->fetchAll();
?>

<main>
    <!-- LATEST REVIEW SECTION (Dynamically replaces previous review when a new one is approved) -->
    <?php if ($latestReview): ?>
        <section class="featured-review">
            <div class="container">
                <div class="featured-content">
                    <div class="featured-text-group">
                        <span class="featured-badge">LATEST REVIEW</span>
                        <h2><?= e($latestReview['title']) ?></h2>
                        <?php 
                        $paragraphs = get_paragraphs($latestReview['content']);
                        $excerpt = !empty($paragraphs) ? $paragraphs[0] : $latestReview['content'];
                        if (mb_strlen($excerpt) > 180) {
                            $excerpt = mb_substr($excerpt, 0, 180) . '...';
                        }
                        ?>
                        <p><?= e($excerpt) ?></p>
                    </div>
                    <div class="featured-footer">
                        <div class="review-meta">
                            <span>Oleh <?= e($latestReview['author_name'] ?: 'Azumi Team') ?></span> &bull; 
                            <span><?= format_date($latestReview['reviewed_at'] ?: $latestReview['submitted_at']) ?></span>
                        </div>
                        <a href="review-detail.php?id=<?= $latestReview['id'] ?>" class="read-review-btn">READ REVIEW</a>
                    </div>
                </div>
                <div class="featured-image">
                    <img src="<?= e(get_poster_url($latestReview['image_path'])) ?>" alt="<?= e($latestReview['title']) ?>">
                </div>
            </div>
        </section>
    <?php else: ?>
        <section class="featured-review">
            <div class="container">
                <div class="featured-content" style="max-width: 100%; text-align: center;">
                    <div class="featured-text-group">
                        <span class="featured-badge">LATEST REVIEW</span>
                        <h2>Belum Ada Ulasan Terbit</h2>
                        <p>Ulasan film terbaru yang disetujui oleh tim kurator akan otomatis ditampilkan di sini.</p>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <div class="dashboard-layout container">
        <div class="dashboard-main">
            <!-- MORE RECENT FILM REVIEWS -->
            <section class="latest-film-reviews">
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 15px;">
                    <h3>LATEST FILM REVIEWS</h3>
                    <a href="reviews.php" style="font-size: 0.8rem; font-family: var(--font-heading); font-weight: 700; color: var(--accent);">
                        SEMUA ULASAN &rarr;
                    </a>
                </div>
                <div class="latest-review-list">
                    <?php if (!empty($otherReviews)): ?>
                        <?php foreach ($otherReviews as $rev): ?>
                            <a href="review-detail.php?id=<?= $rev['id'] ?>" class="latest-review-item">
                                <div class="latest-review-poster">
                                    <img src="<?= e(get_poster_url($rev['image_path'])) ?>"
                                         alt="<?= e($rev['title']) ?>"
                                         loading="lazy">
                                </div>
                                <div class="latest-review-info">
                                    <span class="latest-review-category"><?= e($rev['category'] ?: 'REVIEWS') ?></span>
                                    <h4 class="latest-review-title"><?= e($rev['title']) ?></h4>
                                    <div class="latest-review-meta">
                                        <span><?= e($rev['author_name'] ?: 'Azumi Team') ?></span>
                                        <span class="latest-review-meta-sep">&bull;</span>
                                        <span><?= format_date($rev['reviewed_at'] ?: $rev['submitted_at']) ?></span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: var(--lighter-grey); padding: 20px 0;">Belum ada ulasan film lainnya yang diterbitkan.</p>
                    <?php endif; ?>
                </div>
            </section>

            <!-- UPCOMING SECTION (Admin Watchlist: Posters and Titles Only) -->
            <section class="azumi-interviews-features" style="margin-top: 36px;">
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 15px;">
                    <h3>UPCOMING</h3>
                    <a href="upcoming.php" style="font-size: 0.8rem; font-family: var(--font-heading); font-weight: 700; color: var(--accent);">
                        LIHAT SEMUA UPCOMING &rarr;
                    </a>
                </div>

                <?php if (!empty($upcomingHome)): ?>
                    <div class="upcoming-home-grid">
                        <?php foreach ($upcomingHome as $upFilm): ?>
                            <div class="upcoming-home-card">
                                <div class="upcoming-poster-box">
                                    <img src="<?= e(get_poster_url($upFilm['poster_path'])) ?>" 
                                         alt="<?= e($upFilm['title']) ?>" 
                                         class="upcoming-poster-img"
                                         loading="lazy">
                                </div>
                                <div class="upcoming-info">
                                    <h4 class="upcoming-title"><?= e($upFilm['title']) ?></h4>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="color: var(--lighter-grey); padding: 20px 0;">Belum ada daftar film upcoming.</p>
                <?php endif; ?>
            </section>
        </div>

        <!-- SIDEBAR -->
        <aside class="dashboard-sidebar">
            <div class="sidebar-promo-card">
                <span class="promo-label">AVAILABLE NOW</span>
                <h4>Watch on CathayPlay.com</h4>
                <p>Access over 900 Sinophone Independent Films directly on their official streaming platform.</p>
            </div>
            
            <div class="sidebar-partner-section">
                <span class="partner-heading">INDUSTRY PARTNERS</span>
                <div class="partner-card">
                    <span class="partner-text">third window films</span>
                </div>
            </div>
        </aside>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
