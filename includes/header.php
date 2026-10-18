<?php
/**
 * Public Header Template
 * Azumi Movie Reviews
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$pageTitle = $pageTitle ?? 'Azumi Movie Reviews';

// Dynamic ticker: fetch 3 latest upcoming films
$_tickerFilms = [];
try {
    $_tickerPdo = getDbConnection();
    $_tickerStmt = $_tickerPdo->prepare(
        "SELECT title FROM upcoming_films ORDER BY created_at DESC, id DESC LIMIT 3"
    );
    $_tickerStmt->execute();
    $_tickerFilms = $_tickerStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
} catch (Exception $_tickerEx) {
    // Silently fail — fallback text used below
    $_tickerFilms = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Playfair+Display:ital,wght@0,700;1,700&family=Montserrat:ital,wght@0,700;0,800;1,700;1,800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
    <header>
        <div class="top-news-bar">
            <div class="top-news-container">
                <span class="news-label">LATEST NEWS</span>
                <div class="news-ticker">
                    <span class="news-ticker-content">
                    <?php if (!empty($_tickerFilms)): ?>
                        <?php foreach ($_tickerFilms as $_idx => $_filmTitle): ?>
                            <span class="ticker-item"><strong class="ticker-prefix">Now Showing:</strong> <?= e($_filmTitle) ?> &mdash; Read the full review on Azumi Movie Reviews. Our editorial team is currently watching this film and preparing our thoughts for you.</span>
                            <span class="ticker-separator">&bull;&bull;&bull;</span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span class="ticker-item"><strong class="ticker-prefix">Now Showing:</strong> Upcoming Asian Cinema Highlights &mdash; Read the full review on Azumi Movie Reviews. Our editorial team is currently watching this film and preparing our thoughts for you.</span>
                    <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>
        <nav>
            <div class="logo">
                <span class="tagline">BRINGING THE BEST OF ASIAN CINEMA</span>
                <a href="index.php">AZUMI MOVIE REVIEWS</a>
                <span class="date"><?= date('F j, Y') ?></span>
            </div>
            <div class="nav-right">
                <ul class="nav-links">
                    <li><a href="about.php" class="<?= $currentPage === 'about.php' ? 'active-link' : '' ?>">ABOUT US</a></li>
                    <li><a href="upcoming.php" class="<?= $currentPage === 'upcoming.php' ? 'active-link' : '' ?>">UPCOMING</a></li>
                    <li><a href="reviews.php" class="<?= $currentPage === 'reviews.php' || $currentPage === 'review-detail.php' ? 'active-link' : '' ?>">REVIEWS</a></li>
                    <li><a href="submit.php" class="<?= $currentPage === 'submit.php' ? 'active-link' : '' ?>">SUBMIT YOUR FILM</a></li>
                    <li><a href="/admin" style="color: var(--accent); font-weight: 700;">ADMIN DASHBOARD</a></li>
                </ul>
                <div class="nav-actions">
                    <div class="nav-search-wrapper" id="nav-search-wrapper">
                        <a href="search.php" id="search-icon" class="nav-search-toggle" aria-label="Search" title="Cari film atau ulasan">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </a>

                        <!-- Floating Dropdown Search -->
                        <div class="search-dropdown-bar" id="search-dropdown-bar">
                            <div class="search-dropdown-container">
                                <form action="search.php" method="GET" class="search-inline-form" id="search-inline-form">
                                    <button type="submit" class="search-inline-icon-btn" title="Cari ulasan" aria-label="Cari ulasan">
                                        <svg class="search-inline-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="11" cy="11" r="8"></circle>
                                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                        </svg>
                                    </button>
                                    <input type="text" name="q" id="search-input" class="search-inline-input" 
                                           value="<?= isset($_GET['q']) ? e($_GET['q']) : '' ?>"
                                           placeholder="Cari ulasan atau film..." 
                                           autocomplete="off">
                                    <button type="button" id="close-search" class="search-btn-close" aria-label="Tutup pencarian" title="Tutup">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                        </svg>
                                    </button>
                                </form>
                                <div id="search-quick-results" class="search-quick-results"></div>
                            </div>
                        </div>
                    </div>

                    <div class="hamburger-menu" aria-label="Open menu">
                        <svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <?php 
    $flashMessages = get_flash_messages();
    if (!empty($flashMessages)): 
    ?>
        <div class="container" style="padding-top: 15px; padding-bottom: 0;">
            <?php foreach ($flashMessages as $flash): ?>
                <div class="alert-box alert-<?= e($flash['type']) ?>">
                    <span class="alert-dot"></span>
                    <span><?= e($flash['message']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
