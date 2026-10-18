<?php
/**
 * Helper Functions & Security Utilities
 * Azumi Movie Reviews
 */

if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set('Asia/Jakarta');
}

if (session_status() === PHP_SESSION_NONE) {
    // Detect SSL whether direct or through reverse proxy / Cloudflare
    $isHttps = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ||
               (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') ||
               (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

    // Enforce secure session cookie parameters
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

/**
 * Escapes HTML output safely
 */
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generates or retrieves the current CSRF token
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Generates a hidden input field containing the CSRF token
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Verifies the submitted CSRF token
 */
function verify_csrf_token(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sets a flash message to display on the next page view
 */
function set_flash_message(string $type, string $message): void {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = [
        'type'    => $type, // 'success', 'danger', 'warning', 'info'
        'message' => $message
    ];
}

/**
 * Gets and clears all flash messages
 */
function get_flash_messages(): array {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Splits text into paragraphs based on double line breaks
 *
 * @param string $text
 * @return array List of non-empty paragraphs
 */
function get_paragraphs(string $text): array {
    // Normalize newlines to \n
    $normalized = str_replace(["\r\n", "\r"], "\n", trim($text));
    // Split by two or more newlines
    $rawParagraphs = preg_split('/\n\s*\n+/', $normalized);
    
    $paragraphs = [];
    foreach ($rawParagraphs as $p) {
        $clean = trim($p);
        // Only consider non-empty paragraphs
        if (mb_strlen($clean) > 5) {
            $paragraphs[] = $clean;
        }
    }
    return $paragraphs;
}

/**
 * Counts the number of valid paragraphs
 */
function count_paragraphs(string $text): int {
    return count(get_paragraphs($text));
}

/**
 * Converts stored text into safe HTML <p> tags
 */
function render_paragraphs_html(string $text): string {
    $paragraphs = get_paragraphs($text);
    if (empty($paragraphs)) {
        return '<p>' . nl2br(e($text)) . '</p>';
    }
    
    $html = '';
    foreach ($paragraphs as $p) {
        $html .= '<p>' . nl2br(e($p)) . '</p>' . "\n";
    }
    return $html;
}

/**
 * Resolves the public web URL for an image/poster
 */
function get_poster_url(?string $filename): string {
    if (empty($filename)) {
        return 'assets/images/images.jpg';
    }
    
    // Check if file is in uploads/posters/
    if (file_exists(__DIR__ . '/../uploads/posters/' . $filename)) {
        return 'uploads/posters/' . $filename;
    }
    
    // Check if file is in assets/images/
    if (file_exists(__DIR__ . '/../assets/images/' . $filename)) {
        return 'assets/images/' . $filename;
    }

    return 'assets/images/' . $filename;
}

/**
 * Formats date string to English readable format (e.g., "June 16, 2026")
 */
function format_date(?string $datetime): string {
    if (empty($datetime)) {
        return '-';
    }
    $timestamp = strtotime($datetime);
    if (!$timestamp) {
        return $datetime;
    }
    return date('F j, Y', $timestamp);
}
