-- ========================================================
-- Azumi Movie Reviews - Database Schema
-- Database: azumi_reviews
-- ========================================================

-- CATATAN PORTABILITAS DEPLOYMENT:
-- Di shared hosting (InfinityFree / cPanel), user TIDAK memiliki hak akses CREATE DATABASE.
-- Buat database terlebih dahulu di Control Panel hosting (contoh: if0_12345678_azumi),
-- lalu buka phpMyAdmin, pilih database tersebut di panel kiri, dan impor file ini.
-- Untuk Laragon lokal, skrip database/seed.php sudah otomatis membuat dan memilih database.
--
-- CREATE DATABASE IF NOT EXISTS `azumi_reviews` 
-- DEFAULT CHARACTER SET utf8mb4 
-- COLLATE utf8mb4_unicode_ci;
-- USE `azumi_reviews`;

-- --------------------------------------------------------
-- Table structure for `admins`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL DEFAULT 'Administrator',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for `film_submissions`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `film_submissions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `author_name` VARCHAR(100) NOT NULL DEFAULT 'Anonim',
    `category` VARCHAR(100) NOT NULL DEFAULT 'REVIEWS',
    `content` TEXT NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    `admin_notes` TEXT NULL,
    `submitted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `reviewed_at` DATETIME NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`),
    INDEX `idx_submitted_at` (`submitted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Seed Data: Akun Administrator Awal (Keamanan Deployment)
-- CATATAN KEAMANAN GITHUB:
-- Kredensial admin sengaja tidak ditanam secara permanen di file ini.
-- Setelah mengimpor database ini, Anda dapat langsung membuat akun admin pertama
-- dengan membuka URL: /admin/setup.php pada browser.
-- 
-- Jika Anda memerlukan akun default untuk pengujian lokal offline (admin / admin123),
-- Anda dapat mengaktifkan query INSERT di bawah ini:
-- --------------------------------------------------------
-- INSERT INTO `admins` (`username`, `password_hash`, `full_name`, `created_at`) 
-- VALUES (
--     'admin',
--     '$2y$10$wK1Ww4L1sS8Z297i5R/cDuM8fF0E7eLq8m6sF6e612WbCeqcT.rve',
--     'Azumi Editorial Team',
--     NOW()
-- ) ON DUPLICATE KEY UPDATE `username` = `username`;

-- --------------------------------------------------------
-- Seed Data: Initial Film Reviews (Approved for Public Display)
-- --------------------------------------------------------
INSERT INTO `film_submissions` (`id`, `title`, `author_name`, `category`, `content`, `image_path`, `status`, `admin_notes`, `submitted_at`, `reviewed_at`)
VALUES
(
    1,
    'Funuke Show Some Love, You Losers! (2007)',
    'Azumi Team',
    'JAPANESE REVIEWS',
    'Sebuah potret keluarga yang gelap namun komikal. Film ini menyoroti dinamika rumit di mana Sumika terus-menerus menindas saudaranya, Kiyomi, yang diam-diam merupakan seorang seniman manga berbakat.\r\n\r\nKisah ini berkembang menjadi eksplorasi absurditas keluarga ketika orang tua mereka meninggal mendadak dalam kecelakaan tragis yang aneh saat berusaha menyelamatkan seekor kucing.\r\n\r\nDengan gaya penyutradaraan Daihachi Yoshida yang khas, film ini berhasil menyeimbangkan kepedihan mendalam dengan humor satir yang tajam, menjadikannya salah satu karya klasik sinema independen Jepang yang wajib diapresiasi.',
    'funuke-show-some-love.webp',
    'approved',
    'Review resmi Azumi Team untuk featured spotlight.',
    '2026-06-16 10:00:00',
    '2026-06-16 10:30:00'
),
(
    2,
    'Fear (2025) by Apollinaria Degtiareva Film Review',
    'Panos Kotzathanasis',
    'REVIEWS',
    'Fear (2025) adalah film thriller psikologis yang disutradarai oleh Apollinaria Degtiareva. Film ini mengikuti kisah seorang wanita muda bernama Anya yang dihantui oleh trauma masa lalu setelah mengalami peristiwa mengerikan di kotanya.\r\n\r\nAnya mencoba membangun kembali hidupnya di lingkungan baru, tetapi ketakutan dan paranoia terus menghantuinya. Dia mulai meragukan realitas di sekitarnya dan apakah ancaman yang dia rasakan itu nyata atau sekadar bayangan semu.\r\n\r\nDegtiareva dengan cerdik menciptakan suasana mencekam yang membangun ketegangan secara perlahan. Permainan cahaya dan bayangan yang efektif serta musik atmosferik kian memperkuat nuansa horor psikologis yang mendalam.',
    'fear-2025.webp',
    'approved',
    'Disetujui untuk ulasan festival internasional.',
    '2026-06-13 14:00:00',
    '2026-06-13 14:45:00'
),
(
    3,
    'Where The Sun Shines Bright (2026) by Lam Can-zhao',
    'Panos Kotzathanasis',
    'CHINESE REVIEWS',
    'Where The Sun Shines Bright (2026) menyajikan potret kehidupan pedesaan yang puitis dan reflektif. Sutradara Lam Can-zhao menangkap keindahan sederhana dari ritme harian yang sering kali terabaikan dalam hiruk-pikuk modernisasi.\r\n\r\nMelalui sinematografi berlapis cahaya alami dan komposisi visual yang cermat, film ini mengajak penonton merenungi hubungan antara manusia, alam, dan ingatan masa lampau yang perlahan memudar.\r\n\r\nSebuah mahakarya kontemplatif yang membuktikan kekuatan penceritaan visual yang tenang tanpa perlu dialog yang berlebihan.',
    'where-the-sun-shines-bright.webp',
    'approved',
    'Ulasan terpilih kategori Sinema Tiongkok Independen.',
    '2026-06-13 16:20:00',
    '2026-06-13 17:00:00'
),
(
    4,
    'Jane (2017) by Cho Hyun-hoon Film Review',
    'Azumi Team',
    'CATHAYPLAY REVIEW',
    'Jane (2017) karya sutradara Cho Hyun-hoon mengangkat kisah kelompok remaja marjinal yang mencari kehangatan keluarga di pinggiran kota Seoul. Jane, seorang wanita transgender yang karismatik, menjadi sosok pelindung bagi anak-anak terasing ini.\r\n\r\nFilm ini tidak menyajikan drama sentimental biasa, melainkan lukisan emosional yang jujur dan menyentuh tentang kerinduan manusia akan penerimaan diri dan kasih sayang sejati.\r\n\r\nPenampilan akting yang memukau dan atmosfer melankolis yang konsisten menjadikan Jane karya yang tak terlupakan dalam sinema independen Korea Selatan.',
    'jane-2017.webp',
    'approved',
    'Disetujui untuk kemitraan CathayPlay ulasan ulasan indie.',
    '2026-06-03 11:15:00',
    '2026-06-03 12:00:00'
),
(
    5,
    'Drive My Car (2021) - Melodi Duka dan Rekonsiliasi',
    'Rian Ardiansyah',
    'REVIEWS',
    'Adaptasi megah Ryusuke Hamaguchi atas cerita pendek Haruki Murakami ini adalah sebuah meditasi panjang tentang kehilangan, penyesalan, dan kekuatan komunikasi antarmanusia.\r\n\r\nSepanjang perjalanan panjang di dalam mobil Saab 900 merah, dialog-dialog jujur perlahan meruntuhkan benteng emosional kedua tokoh utama yang sama-sama menyimpan duka tak terucap.\r\n\r\nFilm ini pantas mendapatkan tempat terhormat sebagai salah satu capaian artistik terbaik sinema Jepang dekade ini.',
    'TWFBD060-Funuke-web.jpg',
    'pending',
    NULL,
    '2026-10-10 18:30:00',
    NULL
)
ON DUPLICATE KEY UPDATE `id` = `id`;

-- --------------------------------------------------------
-- Table structure for `upcoming_films`
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `upcoming_films` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `poster_path` VARCHAR(255) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_upcoming_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Seed Data: Upcoming Films Watchlist
-- --------------------------------------------------------
INSERT INTO `upcoming_films` (`id`, `title`, `poster_path`, `created_at`)
VALUES
(1, 'Still Walking (2008)', 'still_walking_00.jpg', NOW()),
(2, 'The Taste of Tea (2004)', 'TWFBD060-Funuke-web.jpg', NOW()),
(3, 'Drive My Car (2021)', 'recommendation-1.webp', NOW())
ON DUPLICATE KEY UPDATE `id` = `id`;

