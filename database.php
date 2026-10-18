<?php
/**
 * Database Configuration & PDO Connection
 * Azumi Movie Reviews
 */

// 1. Muat konfigurasi independen jika tersedia (file ini diabaikan git)
$localConfig = __DIR__ . '/database.local.php';
if (file_exists($localConfig)) {
    require_once $localConfig;
}

// 2. Fallback ke environment variable atau setelan default Laragon lokal
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
if (!defined('DB_PORT')) define('DB_PORT', getenv('DB_PORT') ?: '3306');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'azumi_reviews');

/**
 * Returns a PDO instance connected to the application database.
 * If the database does not exist on local dev, it will attempt to initialize it safely.
 *
 * @return PDO
 */
function getDbConnection(): PDO {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // If database does not exist (error 1049), attempt to create it on local/development
        if ($e->getCode() == 1049 || strpos($e->getMessage(), 'Unknown database') !== false) {
            try {
                $rootDsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";charset=utf8mb4";
                $rootPdo = new PDO($rootDsn, DB_USER, DB_PASS, $options);
                $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $rootPdo->exec("USE `" . DB_NAME . "`");
                
                $schemaFile = __DIR__ . '/../database/schema.sql';
                if (file_exists($schemaFile)) {
                    $sql = file_get_contents($schemaFile);
                    $rootPdo->exec($sql);
                }
                
                // Reconnect with database
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                return $pdo;
            } catch (Exception $initEx) {
                die("Database Initialization Notice: Database '" . htmlspecialchars(DB_NAME) . "' belum tersedia atau hak akses CREATE DATABASE tidak diizinkan di server ini. Di InfinityFree/shared hosting, pastikan database sudah dibuat di vPanel dan schema.sql telah diimpor via phpMyAdmin. Detail: " . htmlspecialchars($initEx->getMessage()));
            }
        }
        die("Database Connection Error: Gagal terhubung ke MySQL (" . htmlspecialchars(DB_HOST) . "). Periksa kredensial database pada config/database.local.php atau config/database.php. Detail: " . htmlspecialchars($e->getMessage()));
    }

    return $pdo;
}
