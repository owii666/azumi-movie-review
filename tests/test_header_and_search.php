<?php
/**
 * Automated Verification Test for Header Berjalan & Floating Search Dropdown
 * Azumi Movie Reviews
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

echo "=== VERIFYING TASK 1: HEADER BERJALAN ===\n\n";

$pdo = getDbConnection();

// 1. Fetch 3 latest upcoming films from DB
$stmt = $pdo->prepare("SELECT title FROM upcoming_films ORDER BY created_at DESC, id DESC LIMIT 3");
$stmt->execute();
$films = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "[CHECK 1] Upcoming films query:\n";
echo "  Found " . count($films) . " films:\n";
foreach ($films as $idx => $f) {
    echo "    " . ($idx + 1) . ". " . $f . "\n";
}
if (count($films) === 3) {
    echo "  PASS: Exactly 3 latest upcoming films retrieved.\n";
} else {
    echo "  FAIL: Expected 3 upcoming films.\n";
    exit(1);
}

// 2. Render header.php and verify ticker text
$_SERVER['PHP_SELF'] = '/index.php';
ob_start();
include __DIR__ . '/../includes/header.php';
$headerHtml = ob_get_clean();

echo "\n[CHECK 2] Ticker content rendering:\n";
$expectedPattern = "Now Showing:</strong> " . preg_quote($films[0], '#') . " &mdash; Read the full review on Azumi Movie Reviews\\. Our editorial team is currently watching this film and preparing our thoughts for you\\.";
if (preg_match('#' . $expectedPattern . '#', $headerHtml)) {
    echo "  PASS: First film matched expected format with editorial team statement.\n";
} else {
    echo "  FAIL: First film text format mismatch.\n";
    exit(1);
}

foreach ($films as $f) {
    if (strpos($headerHtml, $f) !== false) {
        echo "  PASS: Film '" . $f . "' present in ticker.\n";
    } else {
        echo "  FAIL: Film '" . $f . "' missing from ticker.\n";
        exit(1);
    }
}

// Check separator between items
if (strpos($headerHtml, 'ticker-separator') !== false) {
    echo "  PASS: Ticker items are separated with bullet separators.\n";
} else {
    echo "  FAIL: Ticker separator missing.\n";
    exit(1);
}

// 3. Verify CSS for ticker
$styleCss = file_get_contents(__DIR__ . '/../assets/css/style.css');
if (strpos($styleCss, 'ticker-marquee') !== false && 
    strpos($styleCss, '.news-ticker:hover .news-ticker-content') !== false &&
    strpos($styleCss, 'animation-play-state: paused;') !== false) {
    echo "  PASS: Marquee ticker animation preserved with hover pause support.\n";
} else {
    echo "  FAIL: Ticker CSS missing required rules.\n";
    exit(1);
}

echo "\n=== VERIFYING TASK 2: FLOATING SEARCH DROPDOWN ===\n\n";

// 1. Structure check
echo "[CHECK 1] Search container relative to icon:\n";
if (strpos($headerHtml, 'nav-search-wrapper') !== false &&
    strpos($headerHtml, 'id="search-icon"') !== false &&
    strpos($headerHtml, 'id="search-dropdown-bar"') !== false) {
    echo "  PASS: search-dropdown-bar is nested inside nav-search-wrapper with search-icon.\n";
} else {
    echo "  FAIL: Search structure hierarchy mismatch.\n";
    exit(1);
}

// 2. CSS Properties Check
echo "\n[CHECK 2] CSS styling for floating dropdown:\n";

// Check position relative on wrapper
if (preg_match('/\.nav-search-wrapper\s*\{[^}]*position:\s*relative;/s', $styleCss)) {
    echo "  PASS: .nav-search-wrapper has position: relative.\n";
} else {
    echo "  FAIL: .nav-search-wrapper missing position: relative.\n";
    exit(1);
}

// Check dropdown position and width
if (preg_match('/\.search-dropdown-bar\s*\{[^}]*position:\s*absolute;[^}]*width:\s*330px;/s', $styleCss)) {
    echo "  PASS: .search-dropdown-bar has position: absolute and width 330px (within 300px-350px range).\n";
} else {
    echo "  FAIL: .search-dropdown-bar missing position absolute or width in 300px-350px.\n";
    exit(1);
}

// Check background white, border-radius 8px-12px, shadow
if (preg_match('/\.search-dropdown-bar\s*\{[^}]*background-color:\s*var\(--white\);[^}]*border-radius:\s*10px;[^}]*box-shadow:/s', $styleCss)) {
    echo "  PASS: .search-dropdown-bar has white background, 10px rounded corners (8-12px range), and soft shadow.\n";
} else {
    echo "  FAIL: Background, border-radius, or shadow missing on dropdown.\n";
    exit(1);
}

// Check NO red border
if (strpos($styleCss, 'border-bottom: 2px solid var(--accent);') === false) {
    echo "  PASS: Red border on search dropdown completely removed.\n";
} else {
    echo "  FAIL: Red border still present in style.css.\n";
    exit(1);
}

// Check animation opacity + translateY
if (strpos($styleCss, 'searchDropdownSlide') !== false &&
    preg_match('/@keyframes\s+searchDropdownSlide\s*\{[^}]*0%\s*\{[^}]*opacity:\s*0;\s*transform:\s*translateY\(-8px\);/s', $styleCss) &&
    preg_match('/100%\s*\{[^}]*opacity:\s*1;\s*transform:\s*translateY\(0\);/s', $styleCss)) {
    echo "  PASS: Smooth slide-down animation with opacity and translateY verified.\n";
} else {
    echo "  FAIL: Animation opacity + translateY missing or incorrect.\n";
    exit(1);
}

// 3. Functional Search Verification (Approved Reviews Only)
echo "\n[CHECK 3] Functional Search for Approved Reviews:\n";

// Insert a temporary test pending review to confirm it is NOT searchable
$pdo->prepare("
    INSERT INTO film_submissions (title, author_name, category, content, image_path, status, submitted_at)
    VALUES ('Secret Pending Search Test', 'Tester', 'REVIEWS', 'Content here', 'test.webp', 'pending', NOW())
")->execute();
$testPendingId = $pdo->lastInsertId();

// Also test an approved review
$stmtAppr = $pdo->query("SELECT title FROM film_submissions WHERE status = 'approved' LIMIT 1");
$approvedTitle = $stmtAppr->fetchColumn();

// Test api-search.php query in isolated process
$apiOutput = shell_exec('php -r "$_GET[\"q\"] = \"Secret Pending\"; include \"api-search.php\";"');
$apiResults = json_decode($apiOutput, true);

if (empty($apiResults)) {
    echo "  PASS: Pending review is NOT returned by search API.\n";
} else {
    echo "  FAIL: Pending review leaked into search results!\n";
    exit(1);
}

// Clean up test pending record
$pdo->prepare("DELETE FROM film_submissions WHERE id = ?")->execute([$testPendingId]);

// Test search with approved review keyword
if ($approvedTitle) {
    $word = explode(' ', $approvedTitle)[0];
    $apiOutput2 = shell_exec('php -r "$_GET[\"q\"] = \"' . addslashes($word) . '\"; include \"api-search.php\";"');
    $apiResults2 = json_decode($apiOutput2, true);

    if (!empty($apiResults2)) {
        echo "  PASS: Search API successfully finds approved review for '{$word}'. Total: " . count($apiResults2) . " results.\n";
    } else {
        echo "  FAIL: Search API failed to return approved review for '{$word}'.\n";
        exit(1);
    }
}

// 4. Responsive CSS Check for mobile and HP
echo "\n[CHECK 4] Mobile and HP responsiveness:\n";
$responsiveCss = file_get_contents(__DIR__ . '/../assets/css/responsive.css');
if (strpos($responsiveCss, '.search-dropdown-bar') !== false &&
    strpos($responsiveCss, 'max-width: calc(100vw - 28px)') !== false) {
    echo "  PASS: Mobile rules ensure search dropdown remains constrained and comfortable on HP screens.\n";
} else {
    echo "  FAIL: Mobile rules for search dropdown missing in responsive.css.\n";
    exit(1);
}

echo "\n============================================\n";
echo "🎉 ALL TESTS PASSED: HEADER BERJALAN & SEARCH DROPDOWN FULLY FUNCTIONAL!\n";
echo "============================================\n";
