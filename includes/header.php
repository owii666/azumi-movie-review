<?php
/**
 * Public Header Template
 * Azumi Movie Reviews
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$pageTitle = $pageTitle ?? 'Azumi Movie Reviews';
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
                    <span class="news-ticker-content">A Fan Fest 2026 (JF 2026) is Calling For Entries For Both Traditional and VR films &bull; Follow Our Tribute to the Art Theatre Guild &bull; Stay tuned for upcoming Summer Festival coverage!</span>
                </div>
            </div>
        </div>
        <nav>
            <div class="logo">
                <span class="tagline">BRINGING THE BEST OF ASIAN CINEMA</span>
                <a href="index.php">AZUMI MOVIE REVIEWS</a>
                <span class="date"><?= date('F j, Y') ?></span>
            </div>
            <ul class="nav-links">
                <li><a href="about.php" class="<?= $currentPage === 'about.php' ? 'active-link' : '' ?>">ABOUT US</a></li>
                <li><a href="upcoming.php" class="<?= $currentPage === 'upcoming.php' ? 'active-link' : '' ?>">UPCOMING</a></li>
                <li><a href="reviews.php" class="<?= $currentPage === 'reviews.php' || $currentPage === 'review-detail.php' ? 'active-link' : '' ?>">REVIEWS</a></li>
                <li><a href="submit.php" class="<?= $currentPage === 'submit.php' ? 'active-link' : '' ?>">SUBMIT YOUR FILM</a></li>
                <li><a href="/admin" style="color: var(--accent); font-weight: 700;">ADMIN DASHBOARD</a></li>
                <li>
                    <a href="search.php" id="search-icon" aria-label="Search" title="Cari film atau ulasan">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </a>
                </li>
            </ul>
            <div class="hamburger-menu" aria-label="Open menu">
                <svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </div>
        </nav>

        <!-- Compact Search Bar Dropdown (Non-intrusive) -->
        <div class="search-dropdown-bar" id="search-dropdown-bar">
            <div class="search-dropdown-container">
                <form action="search.php" method="GET" class="search-inline-form" id="search-inline-form">
                    <button type="submit" class="search-inline-icon-btn" title="Cari ulasan" aria-label="Cari ulasan">
                        <svg class="search-inline-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                    <input type="text" name="q" id="search-input" class="search-inline-input" 
                           value="<?= isset($_GET['q']) ? e($_GET['q']) : '' ?>"
                           placeholder="Cari ulasan atau film lalu tekan Enter..." 
                           autocomplete="off">
                    <button type="submit" class="search-btn-submit" title="Cari" aria-label="Cari">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                    <button type="button" id="close-search" class="search-btn-close" aria-label="Tutup pencarian" title="Tutup">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </form>
                <div id="search-quick-results" class="search-quick-results"></div>
            </div>
        </div>
    </header>

    <?php 
    $flashMessages = get_flash_messages();
    if (!empty($flashMessages)): 
    ?>
        <div class="container" style="padding-top: 15px; padding-bottom: 0;">
            <?php foreach ($flashMessages as $flash): ?>
                <div class="alert-box alert-<?= e($flash['type']) ?>">
                    <span><?= e($flash['message']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
