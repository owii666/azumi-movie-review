<?php
/**
 * Test Suite: Admin Authentication & Account Management
 * Tests for Azumi Movie Reviews
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

echo "=== STARTING AUTH & ACCOUNT MANAGEMENT TEST SUITE ===\n\n";

$pdo = getDbConnection();

// --------------------------------------------------------------------------
// TEST 1: Public Navbar contains ADMIN DASHBOARD permanently linking to /admin
// --------------------------------------------------------------------------
echo "[TEST 1] Public Navbar 'ADMIN DASHBOARD' link to /admin:\n";
$publicHeader = file_get_contents(__DIR__ . '/../includes/header.php');

$hasAdminLink = strpos($publicHeader, 'ADMIN DASHBOARD') !== false;
$pointsToAdmin = strpos($publicHeader, 'href="/admin"') !== false;
$isConditional = strpos($publicHeader, 'if (is_admin_logged_in())') !== false;

if ($hasAdminLink && $pointsToAdmin && !$isConditional) {
    echo "  PASS: 'ADMIN DASHBOARD' is permanently displayed and links directly to '/admin'.\n";
} else {
    echo "  FAIL: Admin navbar link issue! (hasLink: " . ($hasAdminLink ? 'Y' : 'N') . ", href: " . ($pointsToAdmin ? 'Y' : 'N') . ", conditional: " . ($isConditional ? 'Y' : 'N') . ")\n";
    exit(1);
}

// --------------------------------------------------------------------------
// TEST 2: Admin Login page does NOT leak default credentials
// --------------------------------------------------------------------------
echo "\n[TEST 2] Admin Login Page Credential Leaks Check:\n";
$loginCode = file_get_contents(__DIR__ . '/../admin/login.php');

$leaksAdmin123 = strpos($loginCode, 'admin123') !== false;
$leaksBawaan = stripos($loginCode, 'akun bawaan') !== false;
$leaksSetup = strpos($loginCode, 'setup.php') !== false;
$hasForm = strpos($loginCode, '<form action="login.php" method="POST">') !== false;

if (!$leaksAdmin123 && !$leaksBawaan && !$leaksSetup && $hasForm) {
    echo "  PASS: No default credentials, sample accounts, or setup hints on login page. Clean form rendered.\n";
} else {
    echo "  FAIL: Login page still contains leaked credentials or hints!\n";
    exit(1);
}

// --------------------------------------------------------------------------
// TEST 3: Admin Header & Dashboard contains 'Pengaturan Akun' / 'Ganti Password'
// --------------------------------------------------------------------------
echo "\n[TEST 3] Admin Dashboard & Header Menu Check:\n";
$adminHeader = file_get_contents(__DIR__ . '/../admin/header.php');
$adminIndex = file_get_contents(__DIR__ . '/../admin/index.php');

$headerHasAccount = strpos($adminHeader, 'account.php') !== false && strpos($adminHeader, 'Pengaturan Akun') !== false;
$indexHasAccount = strpos($adminIndex, 'account.php') !== false;

if ($headerHasAccount && $indexHasAccount) {
    echo "  PASS: 'Pengaturan Akun' menu added in admin header and dashboard quick actions.\n";
} else {
    echo "  FAIL: 'Pengaturan Akun' menu missing in admin header or dashboard!\n";
    exit(1);
}

// --------------------------------------------------------------------------
// TEST 4: Initial Login Verification with original password (admin123)
// --------------------------------------------------------------------------
echo "\n[TEST 4] Initial Login with Existing Credentials (admin / admin123):\n";
$loginInitial = authenticate_admin('admin', 'admin123');
if ($loginInitial['success']) {
    echo "  PASS: Initial login successful for 'admin'.\n";
    echo "  PASS: Session populated with admin_id: " . ($_SESSION['admin_id'] ?? 'null') . "\n";
} else {
    echo "  FAIL: Initial login failed: " . $loginInitial['message'] . "\n";
    exit(1);
}

$adminId = $_SESSION['admin_id'];

// --------------------------------------------------------------------------
// TEST 5: Change Password Form Validations
// --------------------------------------------------------------------------
echo "\n[TEST 5] Password Change Validations:\n";

// 5.1 Invalid Old Password
$resWrongOld = change_admin_password($adminId, 'wrong_old_password', 'newPass123', 'newPass123');
if (!$resWrongOld['success'] && strpos($resWrongOld['message'], 'tidak sesuai') !== false) {
    echo "  PASS: Wrong old password rejected properly: '{$resWrongOld['message']}'\n";
} else {
    echo "  FAIL: Wrong old password was not rejected properly!\n";
    exit(1);
}

// 5.2 Confirmation Mismatch
$resMismatch = change_admin_password($adminId, 'admin123', 'newPass123', 'differentPass456');
if (!$resMismatch['success'] && strpos($resMismatch['message'], 'tidak cocok') !== false) {
    echo "  PASS: Confirmation mismatch rejected properly: '{$resMismatch['message']}'\n";
} else {
    echo "  FAIL: Confirmation mismatch was not rejected properly!\n";
    exit(1);
}

// 5.3 Below Minimum Length (< 6 chars)
$resTooShort = change_admin_password($adminId, 'admin123', '12345', '12345');
if (!$resTooShort['success'] && strpos($resTooShort['message'], 'minimal') !== false) {
    echo "  PASS: Short password (< 6 chars) rejected properly: '{$resTooShort['message']}'\n";
} else {
    echo "  FAIL: Short password was not rejected properly!\n";
    exit(1);
}

// --------------------------------------------------------------------------
// TEST 6: Change Password Success, Hashing & Storage Verification
// --------------------------------------------------------------------------
echo "\n[TEST 6] Change Password to New Password ('AzumiNew#2026'):\n";
$newPasswordTest = 'AzumiNew#2026';
$resChange = change_admin_password($adminId, 'admin123', $newPasswordTest, $newPasswordTest);

if ($resChange['success']) {
    echo "  PASS: Password successfully changed: '{$resChange['message']}'\n";
} else {
    echo "  FAIL: Failed to change password: '{$resChange['message']}'\n";
    exit(1);
}

// Verify database hash
$stmt = $pdo->prepare("SELECT password_hash FROM admins WHERE id = :id");
$stmt->execute([':id' => $adminId]);
$storedHash = $stmt->fetchColumn();

if (strpos($storedHash, '$2y$') === 0 || strpos($storedHash, '$2b$') === 0) {
    echo "  PASS: New password stored using secure bcrypt hash: " . substr($storedHash, 0, 15) . "...\n";
} else {
    echo "  FAIL: Password not stored as proper bcrypt hash!\n";
    exit(1);
}

if ($storedHash === $newPasswordTest) {
    echo "  FAIL: CRITICAL - Password stored in plaintext!\n";
    exit(1);
} else {
    echo "  PASS: Verified password is NOT stored in plain text.\n";
}

// --------------------------------------------------------------------------
// TEST 7: Logout and Login with New Password
// --------------------------------------------------------------------------
echo "\n[TEST 7] Logout & Login with New Password:\n";
logout_admin();

if (!is_admin_logged_in()) {
    echo "  PASS: Admin successfully logged out (session cleared).\n";
} else {
    echo "  FAIL: Session not cleared after logout!\n";
    exit(1);
}

// 7.1 Login with new password
$loginNew = authenticate_admin('admin', $newPasswordTest);
if ($loginNew['success']) {
    echo "  PASS: Login with new password ('$newPasswordTest') SUCCEEDED!\n";
} else {
    echo "  FAIL: Login with new password failed: " . $loginNew['message'] . "\n";
    exit(1);
}

// --------------------------------------------------------------------------
// TEST 8: Rejection of Old Password
// --------------------------------------------------------------------------
echo "\n[TEST 8] Rejection of Old Password ('admin123'):\n";
logout_admin();

$loginOld = authenticate_admin('admin', 'admin123');
if (!$loginOld['success']) {
    echo "  PASS: Old password rejected properly: '{$loginOld['message']}'\n";
} else {
    echo "  FAIL: Old password was accepted! Password change did not invalidate old password.\n";
    exit(1);
}

// --------------------------------------------------------------------------
// TEST 9: Password Recovery Tool (CLI: database/reset_admin_password.php)
// --------------------------------------------------------------------------
echo "\n[TEST 9] Password Recovery CLI Tool:\n";

// Use reset_admin_password.php to restore password to 'admin123'
$output = shell_exec('php ' . escapeshellarg(__DIR__ . '/../database/reset_admin_password.php') . ' admin admin123 2>&1');
if (strpos($output, 'BERHASIL') !== false) {
    echo "  PASS: CLI recovery script successfully recovered admin password.\n";
} else {
    echo "  FAIL: Recovery script error: $output\n";
    exit(1);
}

// Verify login with recovered password
logout_admin();
$loginRecovered = authenticate_admin('admin', 'admin123');
if ($loginRecovered['success']) {
    echo "  PASS: Successfully authenticated with recovered password ('admin123').\n";
} else {
    echo "  FAIL: Could not authenticate with recovered password!\n";
    exit(1);
}

echo "\n=== ALL AUTH & ACCOUNT MANAGEMENT TESTS PASSED PERFECTLY! ===\n";
