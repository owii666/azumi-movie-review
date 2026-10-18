<?php
/**
 * Admin Login Page
 * Azumi Movie Reviews
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// If already logged in, redirect to dashboard
if (is_admin_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
$usernameVal = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = "Sesi keamanan tidak valid atau telah kedaluwarsa. Silakan muat ulang halaman.";
    } else {
        $usernameVal = trim($_POST['username'] ?? '');
        $passwordVal = $_POST['password'] ?? '';

        $authResult = authenticate_admin($usernameVal, $passwordVal);
        if ($authResult['success']) {
            set_flash_message('success', 'Selamat datang kembali di panel editorial Azumi.');
            header('Location: index.php');
            exit;
        } else {
            $error = $authResult['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Azumi Movie Reviews</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Playfair+Display:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
    <header class="admin-header">
        <div class="admin-nav-container">
            <div class="admin-brand">
                <a href="../index.php">AZUMI MOVIE REVIEWS</a>
            </div>
            <div class="admin-nav-actions">
                <a href="../index.php">&larr; Kembali ke Website</a>
            </div>
        </div>
    </header>

    <div class="admin-login-wrapper">
        <div class="login-card">
            <h2>Admin Portal</h2>
            <p class="sub-subtitle">Masuk untuk mengelola ulasan dan publikasi film</p>

            <?php if (!empty($error)): ?>
                <div class="alert-box alert-danger">
                    <span class="alert-dot"></span>
                    <span><?= e($error) ?></span>
                </div>
            <?php endif; ?>

            <?php 
            $flashMessages = get_flash_messages();
            foreach ($flashMessages as $flash): 
            ?>
                <div class="alert-box alert-<?= e($flash['type']) ?>">
                    <span class="alert-dot"></span>
                    <span><?= e($flash['message']) ?></span>
                </div>
            <?php endforeach; ?>

            <form action="login.php" method="POST">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control" 
                           value="<?= e($usernameVal) ?>" required autocomplete="username" autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" required autocomplete="current-password">
                </div>

                <div style="margin-top: 25px;">
                    <button type="submit" class="btn-submit" style="width: 100%;">
                        MASUK KE DASHBOARD
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
