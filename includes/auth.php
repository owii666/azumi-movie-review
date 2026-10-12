<?php
/**
 * Admin Authentication & Session Security
 * Azumi Movie Reviews
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Checks if an administrator is currently authenticated
 */
function is_admin_logged_in(): bool {
    if (empty($_SESSION['admin_id']) || empty($_SESSION['admin_username'])) {
        return false;
    }

    // Optional: session inactivity timeout (2 hours)
    $maxLifetime = 7200;
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $maxLifetime)) {
        logout_admin();
        return false;
    }
    $_SESSION['last_activity'] = time();

    return true;
}

/**
 * Enforces admin authentication. Redirects to login if not authenticated.
 */
function require_admin(string $redirectTo = 'login.php'): void {
    if (!is_admin_logged_in()) {
        set_flash_message('warning', 'Silakan login terlebih dahulu untuk mengakses dashboard admin.');
        
        $destination = $redirectTo;
        if ($destination === 'login.php') {
            $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
            $scriptDir = rtrim($scriptDir, '/');
            if (basename($scriptDir) === 'admin') {
                $destination = $scriptDir . '/login.php';
            }
        }
        header('Location: ' . $destination);
        exit;
    }
}

/**
 * Attempts to authenticate an administrator
 *
 * @param string $username
 * @param string $password
 * @return array ['success' => bool, 'message' => string]
 */
function authenticate_admin(string $username, string $password): array {
    $username = trim($username);

    // Rate limiting using session
    $lockoutKey = 'admin_lockout_until';
    $attemptsKey = 'admin_login_attempts';

    if (isset($_SESSION[$lockoutKey]) && time() < $_SESSION[$lockoutKey]) {
        $remaining = $_SESSION[$lockoutKey] - time();
        return [
            'success' => false,
            'message' => "Terlalu banyak percobaan gagal. Silakan tunggu $remaining detik lagi."
        ];
    }

    if (empty($username) || empty($password)) {
        return [
            'success' => false,
            'message' => 'Username dan password wajib diisi.'
        ];
    }

    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT id, username, password_hash, full_name FROM admins WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        // Reset login attempts
        unset($_SESSION[$attemptsKey], $_SESSION[$lockoutKey]);

        // Prevent session fixation safely
        if (!headers_sent()) {
            session_regenerate_id(true);
        }

        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['last_activity'] = time();

        return [
            'success' => true,
            'message' => 'Login berhasil.'
        ];
    }

    // Increment failed attempts
    $attempts = ($_SESSION[$attemptsKey] ?? 0) + 1;
    $_SESSION[$attemptsKey] = $attempts;

    if ($attempts >= 5) {
        $_SESSION[$lockoutKey] = time() + 300; // 5 minutes lock
        return [
            'success' => false,
            'message' => 'Terlalu banyak percobaan gagal. Akun dikunci selama 5 menit.'
        ];
    }

    return [
        'success' => false,
        'message' => 'Username atau password tidak sesuai.'
    ];
}

/**
 * Logs out the administrator and cleans session
 */
function logout_admin(): void {
    unset($_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_name'], $_SESSION['last_activity']);
    if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
        session_regenerate_id(true);
    }
}

/**
 * Changes an administrator password securely with full validation and password_hash
 *
 * @param int $adminId
 * @param string $oldPassword
 * @param string $newPassword
 * @param string $confirmPassword
 * @return array ['success' => bool, 'message' => string]
 */
function change_admin_password(int $adminId, string $oldPassword, string $newPassword, string $confirmPassword): array {
    if (empty($oldPassword)) {
        return ['success' => false, 'message' => 'Password lama wajib diisi.'];
    }

    if (empty($newPassword)) {
        return ['success' => false, 'message' => 'Password baru wajib diisi.'];
    }

    if (mb_strlen($newPassword) < 6) {
        return ['success' => false, 'message' => 'Password baru minimal harus terdiri dari 6 karakter.'];
    }

    if ($newPassword !== $confirmPassword) {
        return ['success' => false, 'message' => 'Konfirmasi password baru tidak cocok.'];
    }

    $pdo = getDbConnection();
    $stmt = $pdo->prepare("SELECT id, username, password_hash FROM admins WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $adminId]);
    $admin = $stmt->fetch();

    if (!$admin) {
        return ['success' => false, 'message' => 'Akun administrator tidak ditemukan.'];
    }

    if (!password_verify($oldPassword, $admin['password_hash'])) {
        return ['success' => false, 'message' => 'Password lama yang dimasukkan tidak sesuai.'];
    }

    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $upd = $pdo->prepare("UPDATE admins SET password_hash = :hash WHERE id = :id");
    $upd->execute([':hash' => $newHash, ':id' => $adminId]);

    return ['success' => true, 'message' => 'Password berhasil diperbarui.'];
}
