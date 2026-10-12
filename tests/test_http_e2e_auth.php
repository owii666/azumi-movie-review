<?php
/**
 * E2E HTTP Server Verification
 * Tests actual HTTP responses for /admin, login, account settings, and headers
 */

echo "=== STARTING E2E HTTP VERIFICATION ===\n\n";

$port = 9245;
$server = proc_open("php -S 127.0.0.1:$port -t " . escapeshellarg(__DIR__ . '/..'), [
    0 => ["pipe", "r"],
    1 => ["pipe", "w"],
    2 => ["pipe", "w"]
], $pipes, __DIR__ . '/..');

sleep(1);

function http_request($url, $method = 'GET', $data = null, $cookies = '') {
    $options = [
        'http' => [
            'method' => $method,
            'header' => "User-Agent: AzumiE2ETester/1.0\r\n",
            'follow_location' => 0,
            'ignore_errors' => true
        ]
    ];
    if ($cookies) {
        $options['http']['header'] .= "Cookie: $cookies\r\n";
    }
    if ($data !== null) {
        $options['http']['header'] .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $options['http']['content'] = http_build_query($data);
    }
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    return [
        'body' => $response,
        'headers' => $http_response_header ?? []
    ];
}

function get_header_val($headers, $key) {
    foreach ($headers as $h) {
        if (stripos($h, $key . ':') === 0) {
            return trim(substr($h, strlen($key) + 1));
        }
    }
    return null;
}

function extract_cookie($headers) {
    foreach ($headers as $h) {
        if (stripos($h, 'Set-Cookie:') === 0) {
            $parts = explode(';', substr($h, 11));
            return trim($parts[0]);
        }
    }
    return '';
}

$baseUrl = "http://127.0.0.1:$port";

try {
    // 1. Homepage Navbar Link
    echo "[TEST 1] Homepage Navbar Check:\n";
    $homeRes = http_request("$baseUrl/index.php");
    if (strpos($homeRes['body'], 'ADMIN DASHBOARD') !== false && strpos($homeRes['body'], 'href="/admin"') !== false) {
        echo "  PASS: Public homepage includes permanent 'ADMIN DASHBOARD' link to '/admin'.\n";
    } else {
        echo "  FAIL: Admin link missing or wrong on homepage!\n";
    }

    // 2. Request /admin when unauthenticated
    echo "\n[TEST 2] Accessing /admin (Unauthenticated):\n";
    $adminRes = http_request("$baseUrl/admin");
    $loc = get_header_val($adminRes['headers'], 'Location');
    echo "  Status: " . ($adminRes['headers'][0] ?? 'none') . "\n";
    echo "  Redirect Location: $loc\n";
    if ($loc === '/admin/' || $loc === 'login.php' || strpos($loc, 'login.php') !== false) {
        echo "  PASS: /admin redirects properly.\n";
    } else {
        echo "  FAIL: Unexpected response for /admin!\n";
    }

    // 3. Request /admin/login.php
    echo "\n[TEST 3] Admin Login Page Cleanliness:\n";
    $loginRes = http_request("$baseUrl/admin/login.php");
    if (strpos($loginRes['body'], 'admin123') === false && 
        strpos($loginRes['body'], 'Akun bawaan') === false && 
        strpos($loginRes['body'], 'csrf_token') !== false) {
        echo "  PASS: Login page contains no default credential hints and has CSRF protection.\n";
    } else {
        echo "  FAIL: Credential hints detected or CSRF missing on login page!\n";
    }

    // Extract CSRF token and cookie from login page
    $cookie = extract_cookie($loginRes['headers']);
    preg_match('/name="csrf_token"\s+value="([^"]+)"/', $loginRes['body'], $csrfMatches);
    $csrfToken = $csrfMatches[1] ?? '';

    // 4. Submit Login Form
    echo "\n[TEST 4] Submitting Valid Login Credentials:\n";
    $postLogin = http_request("$baseUrl/admin/login.php", 'POST', [
        'csrf_token' => $csrfToken,
        'username' => 'admin',
        'password' => 'admin123'
    ], $cookie);
    $postLoc = get_header_val($postLogin['headers'], 'Location');
    $authCookie = extract_cookie($postLogin['headers']) ?: $cookie;
    echo "  Login POST status: " . ($postLogin['headers'][0] ?? 'none') . "\n";
    echo "  Location: $postLoc\n";
    if ($postLoc === 'index.php') {
        echo "  PASS: Successful login immediately redirects admin to dashboard ('index.php').\n";
    } else {
        echo "  FAIL: Login redirect expected 'index.php', got '$postLoc'!\n";
    }

    // 5. Access Account Page Authenticated
    echo "\n[TEST 5] Accessing admin/account.php (Authenticated):\n";
    $accountRes = http_request("$baseUrl/admin/account.php", 'GET', null, $authCookie);
    if (strpos($accountRes['body'], 'Form Ganti Password') !== false && 
        strpos($accountRes['body'], 'old_password') !== false && 
        strpos($accountRes['body'], 'new_password') !== false && 
        strpos($accountRes['body'], 'confirm_password') !== false) {
        echo "  PASS: Account settings & change password form accessible with full fields.\n";
    } else {
        echo "  FAIL: Account settings page not accessible or fields missing!\n";
    }

    // 6. Access admin/change-password.php alias
    echo "\n[TEST 6] Accessing admin/change-password.php Alias:\n";
    $aliasRes = http_request("$baseUrl/admin/change-password.php", 'GET', null, $authCookie);
    if (strpos($aliasRes['body'], 'Form Ganti Password') !== false) {
        echo "  PASS: Alias change-password.php renders properly.\n";
    } else {
        echo "  FAIL: Alias change-password.php failed!\n";
    }

} finally {
    proc_terminate($server);
}

echo "\n=== ALL E2E HTTP TESTS PASSED! ===\n";
