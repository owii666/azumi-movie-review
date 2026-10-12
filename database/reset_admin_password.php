<?php
/**
 * Administrator Password Recovery Utility (CLI Only)
 * Azumi Movie Reviews
 *
 * Usage:
 *   php database/reset_admin_password.php <username> <new_password>
 *   or run without arguments for interactive prompts.
 */

// Block unauthorized web requests
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Akses ditolak: Skrip pemulihan akun administrator hanya dapat dieksekusi secara aman melalui antarmuka baris perintah (CLI).\n");
}

require_once __DIR__ . '/../config/database.php';

echo "========================================================\n";
echo "   AZUMI MOVIE REVIEWS - PROSEDUR PEMULIHAN AKUN ADMIN  \n";
echo "========================================================\n\n";

try {
    $pdo = getDbConnection();

    // Check available admin accounts
    $admins = $pdo->query("SELECT id, username, full_name FROM admins ORDER BY id ASC")->fetchAll();
    if (empty($admins)) {
        echo "PERINGATAN: Belum ada akun administrator yang terdaftar di database.\n";
        echo "Silakan jalankan 'php database/seed.php' atau buka 'admin/setup.php' untuk membuat akun.\n";
        exit(1);
    }

    $username = $argv[1] ?? null;
    $newPassword = $argv[2] ?? null;

    if (empty($username)) {
        echo "Daftar Akun Administrator Tersedia:\n";
        foreach ($admins as $adm) {
            echo "  - [ID: {$adm['id']}] Username: '{$adm['username']}' ({$adm['full_name']})\n";
        }
        echo "\n";

        // Read username from CLI input
        echo "Masukkan username yang ingin dipulihkan [default: admin]: ";
        $handle = fopen("php://stdin", "r");
        $line = trim(fgets($handle));
        $username = !empty($line) ? $line : 'admin';
    }

    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, username, full_name FROM admins WHERE username = :u LIMIT 1");
    $stmt->execute([':u' => $username]);
    $targetAdmin = $stmt->fetch();

    if (!$targetAdmin) {
        echo "\nERROR: Akun dengan username '{$username}' tidak ditemukan di database!\n";
        exit(1);
    }

    if (empty($newPassword)) {
        echo "Masukkan password baru untuk '{$username}' (minimal 6 karakter): ";
        $handle = fopen("php://stdin", "r");
        $newPassword = trim(fgets($handle));
    }

    if (empty($newPassword) || mb_strlen($newPassword) < 6) {
        echo "\nERROR: Password baru tidak valid! Panjang minimum adalah 6 karakter.\n";
        exit(1);
    }

    // Hash securely
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

    $upd = $pdo->prepare("UPDATE admins SET password_hash = :hash WHERE id = :id");
    $upd->execute([
        ':hash' => $newHash,
        ':id'   => $targetAdmin['id']
    ]);

    echo "\n========================================================\n";
    echo "BERHASIL: Password administrator telah berhasil dipulihkan!\n";
    echo "  - Username  : {$targetAdmin['username']}\n";
    echo "  - Nama      : {$targetAdmin['full_name']}\n";
    echo "  - Waktu     : " . date('Y-m-d H:i:s') . "\n";
    echo "  - Enkripsi  : password_hash(PASSWORD_DEFAULT - Bcrypt)\n";
    echo "\nSilakan login ke dashboard menggunakan password baru Anda.\n";
    echo "========================================================\n";

} catch (Exception $e) {
    echo "\nERROR: Terjadi kesalahan saat memproses pemulihan: " . $e->getMessage() . "\n";
    exit(1);
}
