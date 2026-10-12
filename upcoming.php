<?php
/**
 * Upcoming Watchlist Page
 * Azumi Movie Reviews
 */

$pageTitle = "Upcoming Films - Azumi Movie Reviews";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/database.php';

$pdo = getDbConnection();

// Fetch all upcoming films
$stmt = $pdo->prepare("SELECT * FROM upcoming_films ORDER BY created_at DESC, id DESC");
$stmt->execute();
$upcomingFilms = $stmt->fetchAll();
?>

<main>
    <section class="upcoming-page">
        <div class="container">
            <div style="text-align: center; margin-bottom: 35px;">
                <span class="news-label" style="display: inline-block; margin-bottom: 10px;">EDITORIAL WATCHLIST</span>
                <h1 style="font-family: var(--font-title); font-size: 2.2rem; color: var(--dark); margin-bottom: 8px;">
                    Upcoming Films
                </h1>
                <p style="color: var(--secondary-text); font-size: 0.95rem; max-width: 680px; margin: 0 auto; line-height: 1.6;">
                    Daftar film yang akan atau sedang ditonton oleh tim redaksi Azumi Movie Reviews.
                </p>
            </div>

            <?php if (!empty($upcomingFilms)): ?>
                <div class="upcoming-grid">
                    <?php foreach ($upcomingFilms as $film): ?>
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
            <?php else: ?>
                <div style="text-align: center; padding: 60px 20px; background-color: var(--white); border-radius: 8px; border: 1px solid var(--light-grey); max-width: 600px; margin: 0 auto;">
                    <p style="color: var(--lighter-grey); font-size: 1rem; margin-bottom: 0;">
                        Belum ada daftar film upcoming saat ini.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
