<?php
/**
 * Database Migration & Seeder Script
 * Execute via CLI: php database/seed.php
 */

require_once __DIR__ . '/../config/database.php';

echo "=== Azumi Movie Reviews Database Setup ===\n";

try {
    $rootDsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
    $pdo = new PDO($rootDsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "[1/3] Creating database '" . DB_NAME . "' if not exists...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `" . DB_NAME . "`");

    echo "[2/3] Executing schema.sql...\n";
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("schema.sql not found at " . $schemaFile);
    }

    $sql = file_get_contents($schemaFile);
    // Execute SQL queries
    $pdo->exec($sql);

    echo "[3/3] Verifying tables and records...\n";
    $adminCount = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();
    $subCount = $pdo->query("SELECT COUNT(*) FROM film_submissions")->fetchColumn();

    echo "SUCCESS! Setup completed.\n";
    echo "- Admins count: $adminCount\n";
    echo "- Submissions count: $subCount\n";

} catch (Exception $e) {
    echo "ERROR during setup: " . $e->getMessage() . "\n";
    exit(1);
}
