<?php
/**
 * Admin Logout Action
 * Azumi Movie Reviews
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

logout_admin();
set_flash_message('info', 'Anda telah berhasil keluar dari akun admin.');
header('Location: login.php');
exit;
