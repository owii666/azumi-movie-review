<?php
/**
 * Admin Dashboard Header Template
 * Azumi Movie Reviews - Sidebar Layout
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Ensure admin is logged in
require_admin();

$pageTitle = $pageTitle ?? 'Admin Dashboard - Azumi Movie Reviews';
$currentAdminPage = basename($_SERVER['PHP_SELF']);

// Page category for breadcrumbs
$adminCategory = 'Moderasi Ulasan';
if (strpos($currentAdminPage, 'upcoming-edit') !== false) {
    $adminCategory = 'Sunting Upcoming';
} elseif (strpos($currentAdminPage, 'upcoming') !== false) {
    $adminCategory = 'Film Upcoming';
} elseif ($currentAdminPage === 'account.php' || $currentAdminPage === 'change-password.php') {
    $adminCategory = 'Pengaturan Akun';
} elseif (strpos($currentAdminPage, 'submission-edit') !== false) {
    $adminCategory = 'Sunting Ulasan';
} elseif (strpos($currentAdminPage, 'submission-view') !== false) {
    $adminCategory = 'Detail Ulasan';
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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&family=Playfair+Display:wght@700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Admin CSS -->
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
    <div class="admin-layout">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar" id="adminSidebar" aria-label="Sidebar Navigasi Admin">
            <div class="admin-sidebar-top">
                <div class="admin-sidebar-brand">
                    <a href="index.php">AZUMI REVIEWS</a>
                    <span class="admin-badge-role">ADMIN</span>
                </div>
                <button type="button" class="admin-sidebar-close" id="sidebarCloseBtn" aria-label="Tutup navigasi">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <div class="admin-sidebar-section">
                <span class="admin-sidebar-section-title">KONTEN EDITORIAL</span>
                <nav class="admin-sidebar-menu">
                    <a href="index.php" class="admin-nav-item <?= ($currentAdminPage === 'index.php' || strpos($currentAdminPage, 'submission') !== false) ? 'active' : '' ?>">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                        <span>Moderasi Ulasan</span>
                    </a>
                    <a href="upcoming.php" class="admin-nav-item <?= (strpos($currentAdminPage, 'upcoming') !== false) ? 'active' : '' ?>">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="15" rx="2" ry="2"/><polyline points="17 2 12 7 7 2"/></svg>
                        <span>Film Upcoming</span>
                    </a>
                </nav>
            </div>

            <div class="admin-sidebar-section">
                <span class="admin-sidebar-section-title">PENGATURAN</span>
                <nav class="admin-sidebar-menu">
                    <a href="account.php" class="admin-nav-item <?= ($currentAdminPage === 'account.php' || $currentAdminPage === 'change-password.php') ? 'active' : '' ?>">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="7" r="4"></circle><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path></svg>
                        <span>Pengaturan Akun</span>
                    </a>
                    <a href="../index.php" target="_blank" class="admin-nav-item">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                        <span>Lihat Website</span>
                    </a>
                </nav>
            </div>

            <div class="admin-sidebar-bottom">
                <div class="admin-user-card">
                    <div class="admin-user-meta">
                        <span class="admin-user-name"><?= e($_SESSION['admin_name'] ?? $_SESSION['admin_username']) ?></span>
                        <span class="admin-user-role">Administrator</span>
                    </div>
                    <a href="logout.php" class="btn-admin-logout" title="Keluar dari sesi admin">Keluar</a>
                </div>
            </div>
        </aside>

        <!-- Sidebar Backdrop for Mobile -->
        <div class="admin-sidebar-backdrop" id="sidebarBackdrop"></div>

        <!-- Main Content Area -->
        <div class="admin-main">
            <!-- Sleek Topbar -->
            <header class="admin-topbar">
                <div class="admin-topbar-left">
                    <button type="button" class="admin-menu-toggle" id="sidebarToggleBtn" aria-label="Buka navigasi sidebar">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                    <div class="admin-breadcrumbs">
                        <span class="admin-crumb-parent">Dashboard</span>
                        <span class="admin-crumb-sep">/</span>
                        <span class="admin-crumb-current"><?= e($adminCategory) ?></span>
                    </div>
                </div>
                <div class="admin-topbar-right">
                    <a href="../index.php" target="_blank" class="admin-topbar-link" title="Buka website publik">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                        <span>Pratinjau Web</span>
                    </a>
                    <a href="account.php" class="admin-topbar-user" title="Profil Akun Administrator">
                        <span class="admin-user-avatar"><?= strtoupper(substr($_SESSION['admin_name'] ?? $_SESSION['admin_username'] ?? 'A', 0, 1)) ?></span>
                        <span class="admin-topbar-username"><?= e($_SESSION['admin_name'] ?? $_SESSION['admin_username']) ?></span>
                    </a>
                </div>
            </header>

            <!-- Notifications / Flash messages container -->
            <?php 
            $flashMessages = get_flash_messages();
            if (!empty($flashMessages)): 
            ?>
                <div class="admin-flash-container">
                    <?php foreach ($flashMessages as $flash): ?>
                        <div class="alert-box alert-<?= e($flash['type']) ?>" role="alert">
                            <span class="alert-dot"></span>
                            <span><?= e($flash['message']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
