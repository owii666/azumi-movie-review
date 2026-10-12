<?php
/**
 * Admin Setup & Account Initialization Utility
 * Azumi Movie Reviews
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getDbConnection();
$adminCount = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();

// If admins exist and no admin is logged in, restrict setup for security
$canSetup = ($adminCount == 0) || is_admin_logged_in();

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$canSetup) {
        $errors[] = "Akun administrator sudah terdaftar. Silakan login terlebih dahulu untuk menambah admin baru.";
    } elseif (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Sesi keamanan tidak valid. Silakan muat ulang.";
    } else {
        $username = trim($_POST['username'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($username) || mb_strlen($username) < 3) {
            $errors[] = "Username minimal 3 karakter.";
        }
        if (empty($password) || mb_strlen($password) < 6) {
            $errors[] = "Password minimal 6 karakter.";
        }
        if ($password !== $confirmPassword) {
            $errors[] = "Konfirmasi password tidak cocok.";
        }

        if (empty($errors)) {
            try {
                // Check if username already taken
                $checkStmt = $pdo->prepare("SELECT id FROM admins WHERE username = :u LIMIT 1");
                $checkStmt->execute([':u' => $username]);
                if ($checkStmt->fetch()) {
                    $errors[] = "Username '$username' sudah digunakan.";
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $ins = $pdo->prepare("
                        INSERT INTO admins (username, password_hash, full_name, created_at) 
                        VALUES (:username, :hash, :full_name, NOW())
                    ");
                    $ins->execute([
                        ':username'  => $username,
                        ':hash'      => $hash,
                        ':full_name' => !empty($fullName) ? $fullName : 'Administrator'
                    ]);

                    set_flash_message('success', "Akun admin '$username' berhasil dibuat! Silakan login.");
                    header('Location: login.php');
                    exit;
                }
            } catch (Exception $e) {
                $errors[] = "Gagal membuat akun: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Akun Admin - Azumi Movie Reviews</title>
    
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
                <a href="login.php">&larr; Kembali ke Login</a>
            </div>
        </div>
    </header>

    <div class="admin-login-wrapper">
        <div class="login-card" style="max-width: 480px;">
            <h2>Setup Akun Admin</h2>
            <p class="sub-subtitle">Buat kredensial administrator dengan password terenkripsi aman</p>

            <?php if (!empty($errors)): ?>
                <div class="alert-box alert-danger">
                    <ul style="margin: 0; padding-left: 20px;">
                        <?php foreach ($errors as $err): ?>
                            <li><?= e($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (!$canSetup): ?>
                <div class="alert-box alert-warning">
                    <span>
                        Sistem telah memiliki <?= (int)$adminCount ?> akun administrator terdaftar. 
                        Untuk alasan keamanan, registrasi admin baru hanya dapat dilakukan setelah masuk ke dashboard.
                        <br><br>
                        <small style="display: block; color: var(--secondary-text);">
                            <strong>Lupa Password?</strong> Jalankan prosedur pemulihan akun melalui CLI server: <code>php database/reset_admin_password.php</code>
                        </small>
                    </span>
                </div>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="login.php" class="btn-submit" style="display: block; text-decoration: none;">
                        MASUK KE HALAMAN LOGIN
                    </a>
                </div>
            <?php else: ?>
                <form action="setup.php" method="POST">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label for="username">Username Admin <span class="required">*</span></label>
                        <input type="text" name="username" id="username" class="form-control" required placeholder="Contoh: editorial_admin">
                    </div>

                    <div class="form-group">
                        <label for="full_name">Nama Lengkap / Redaksi</label>
                        <input type="text" name="full_name" id="full_name" class="form-control" placeholder="Contoh: Tim Redaksi Azumi">
                    </div>

                    <div class="form-group">
                        <label for="password">Password <span class="required">*</span></label>
                        <input type="password" name="password" id="password" class="form-control" required minlength="6" placeholder="Minimal 6 karakter">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password <span class="required">*</span></label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="6" placeholder="Ulangi password">
                    </div>

                    <div style="margin-top: 25px;">
                        <button type="submit" class="btn-submit" style="width: 100%;">
                            BUAT AKUN ADMINISTRATOR
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
