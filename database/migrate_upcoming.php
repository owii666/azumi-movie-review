<?php
/**
 * Migration: Create upcoming_films table
 * Azumi Movie Reviews
 */

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDbConnection();

    $sql = "
    CREATE TABLE IF NOT EXISTS `upcoming_films` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `poster_path` VARCHAR(255) NOT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($sql);

    $count = $pdo->query("SELECT COUNT(*) FROM `upcoming_films`")->fetchColumn();
    if ($count == 0) {
        $stmt = $pdo->prepare("
            INSERT INTO `upcoming_films` (`title`, `poster_path`, `created_at`) VALUES 
            (:t1, :p1, NOW()),
            (:t2, :p2, NOW()),
            (:t3, :p3, NOW())
        ");
        $stmt->execute([
            ':t1' => 'Still Walking (2008)',
            ':p1' => 'still_walking_00.jpg',
            ':t2' => 'The Taste of Tea (2004)',
            ':p2' => 'TWFBD060-Funuke-web.jpg',
            ':t3' => 'Drive My Car (2021)',
            ':p3' => 'recommendation-1.webp'
        ]);
        echo "SUCCESS: Created `upcoming_films` table and seeded initial films.\n";
    } else {
        echo "SUCCESS: `upcoming_films` already exists ($count items).\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
