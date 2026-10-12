<?php
/**
 * Admin Dashboard Header Template
 * Azumi Movie Reviews
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Ensure admin is logged in
require_admin();

$pageTitle = $pageTitle ?? 'Admin Dashboard - Azumi Movie Reviews';
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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&family=Playfair+Display:wght@700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Admin CSS -->
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
    <header class="admin-header">
        <div class="admin-nav-container">
            <div class="admin-brand">
                <a href="index.php">AZUMI REVIEWS</a>
                <span class="admin-badge-role">ADMIN PANEL</span>
            </div>

            <div style="display: flex; gap: 15px; margin-left: 20px;">
                <?php $currentAdminPage = basename($_SERVER['PHP_SELF']); ?>
                <a href="index.php" style="color: <?= ($currentAdminPage === 'index.php' || strpos($currentAdminPage, 'submission') !== false) ? 'var(--white)' : '#AAA' ?>; font-weight: 700; font-size: 0.85rem; text-decoration: none; padding: 6px 12px; border-radius: 4px; background: <?= ($currentAdminPage === 'index.php' || strpos($currentAdminPage, 'submission') !== false) ? 'rgba(255,255,255,0.1)' : 'transparent' ?>;">
                    Moderasi Ulasan
                </a>
                <a href="upcoming.php" style="color: <?= (strpos($currentAdminPage, 'upcoming') !== false) ? 'var(--white)' : '#AAA' ?>; font-weight: 700; font-size: 0.85rem; text-decoration: none; padding: 6px 12px; border-radius: 4px; background: <?= (strpos($currentAdminPage, 'upcoming') !== false) ? 'rgba(255,255,255,0.1)' : 'transparent' ?>;">
                    Film Upcoming
                </a>
                <a href="account.php" style="color: <?= ($currentAdminPage === 'account.php' || $currentAdminPage === 'change-password.php') ? 'var(--white)' : '#AAA' ?>; font-weight: 700; font-size: 0.85rem; text-decoration: none; padding: 6px 12px; border-radius: 4px; background: <?= ($currentAdminPage === 'account.php' || $currentAdminPage === 'change-password.php') ? 'rgba(255,255,255,0.1)' : 'transparent' ?>;">
                    Pengaturan Akun
                </a>
            </div>

            <div class="admin-nav-actions">
                <a href="../index.php" target="_blank" title="Buka website publik">
                    <span style="display: inline-flex; align-items: center; gap: 4px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                        Lihat Website
                    </span>
                </a>
                
                <a href="account.php" class="admin-user-pill" title="Pengaturan Akun & Ganti Password" style="text-decoration: none;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <span><?= e($_SESSION['admin_name'] ?? $_SESSION['admin_username']) ?></span>
                </a>

                <a href="logout.php" class="btn-admin-logout">Keluar</a>
            </div>
        </div>
    </header>

    <?php 
    $flashMessages = get_flash_messages();
    if (!empty($flashMessages)): 
    ?>
        <div class="admin-container" style="margin-bottom: 0;">
            <?php foreach ($flashMessages as $flash): ?>
                <div class="alert-box alert-<?= e($flash['type']) ?>">
                    <span><?= e($flash['message']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
