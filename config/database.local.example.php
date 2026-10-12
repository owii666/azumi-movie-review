<?php
/**
 * ========================================================
 * Template Konfigurasi Database Lokal / Hosting
 * Azumi Movie Reviews
 * ========================================================
 * 
 * PANDUAN PENGGUNAAN:
 * 1. Salin file ini menjadi 'database.local.php' pada folder yang sama (config/).
 * 2. File 'database.local.php' sudah otomatis diabaikan oleh .gitignore,
 *    sehingga kredensial Anda TIDAK AKAN PERNAH terunggah ke GitHub Private/Publik.
 * 
 * UNTUK LOKAL (Laragon):
 * - Anda TIDAK WAJIB membuat database.local.php jika menggunakan setelan standar
 *   Laragon (host 127.0.0.1, user root, tanpa password).
 * 
 * UNTUK DEPLOYMENT (InfinityFree / Shared Hosting):
 * - Buat file 'database.local.php' di dalam folder config/ di File Manager vPanel
 *   atau unggah via FTP (FileZilla).
 * - Sesuaikan konstanta di bawah ini dengan detail MySQL dari VistaPanel InfinityFree Anda:
 *   * MySQL Hostname : contoh 'sql123.infinityfree.com' atau 'sql123.epizy.com'
 *   * MySQL Port     : '3306'
 *   * MySQL Username : contoh 'if0_12345678' atau 'epiz_12345678'
 *   * MySQL Password : password akun hosting vPanel Anda
 *   * MySQL DB Name  : contoh 'if0_12345678_azumi' (nama lengkap dengan prefix)
 */

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'azumi_reviews');
