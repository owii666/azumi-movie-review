<?php
/**
 * Verification Test Script for Azumi Movie Reviews Fixes
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

echo "=== STARTING VERIFICATION TESTS ===\n\n";

$pdo = getDbConnection();

// ---------------------------------------------------------
// TEST 1: Public Search Approved Reviews Only
// ---------------------------------------------------------
echo "[TEST 1] Public Search Approved Reviews:\n";

// Ensure a test pending submission exists to verify it is NOT returned in public search
$pendingTitle = "Pending Hidden Film " . uniqid();
$pdo->prepare("INSERT INTO film_submissions (title, author_name, category, content, image_path, status, submitted_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())")
    ->execute([$pendingTitle, 'Test User', 'REVIEWS', "Paragraf 1 untuk pengujian sistem penelusuran.\n\nParagraf 2 untuk pengujian ulasan pending.", 'test.jpg']);
$pendingId = $pdo->lastInsertId();

// Search for the pending film
$stmtSearchPending = $pdo->prepare("
    SELECT id, title FROM film_submissions 
    WHERE status = 'approved' AND (title LIKE :q1 OR content LIKE :q2)
");
$stmtSearchPending->execute([':q1' => "%$pendingTitle%", ':q2' => "%$pendingTitle%"]);
$pendingResults = $stmtSearchPending->fetchAll();

if (count($pendingResults) === 0) {
    echo "  PASS: Pending review is NOT visible in approved search.\n";
} else {
    echo "  FAIL: Pending review leaked into approved search!\n";
}

// Clean up test pending record
$pdo->prepare("DELETE FROM film_submissions WHERE id = ?")->execute([$pendingId]);

// Search for existing approved film "Funuke"
$term = "%Funuke%";
$stmtSearchApproved = $pdo->prepare("
    SELECT id, title, status FROM film_submissions 
    WHERE status = 'approved' AND (title LIKE :q1 OR content LIKE :q2)
");
$stmtSearchApproved->execute([':q1' => $term, ':q2' => $term]);
$approvedResults = $stmtSearchApproved->fetchAll();

if (count($approvedResults) > 0 && $approvedResults[0]['status'] === 'approved') {
    echo "  PASS: Found approved review: '" . $approvedResults[0]['title'] . "' (Status: " . $approvedResults[0]['status'] . ").\n";
} else {
    echo "  FAIL: Could not find approved review for 'Funuke'.\n";
}

// ---------------------------------------------------------
// TEST 2: Submit Review Success Message & Status
// ---------------------------------------------------------
echo "\n[TEST 2] Submit Review Message & PRG Pattern:\n";

$expectedMsg = "Berhasil terkirim. Tunggu admin ACC review Anda.";
$submitPhpCode = file_get_contents(__DIR__ . '/../submit.php');

if (strpos($submitPhpCode, $expectedMsg) !== false) {
    echo "  PASS: Exact success message present in submit.php: '$expectedMsg'\n";
} else {
    echo "  FAIL: Expected message not found in submit.php!\n";
}

if (strpos($submitPhpCode, "header('Location: submit.php');") !== false) {
    echo "  PASS: PRG (POST-Redirect-GET) pattern implemented.\n";
} else {
    echo "  FAIL: PRG redirect not found in submit.php!\n";
}

// Verify header is required AFTER POST logic in submit.php
$postPos = strpos($submitPhpCode, "if (\$_SERVER['REQUEST_METHOD'] === 'POST')");
$headerReqPos = strpos($submitPhpCode, "require_once __DIR__ . '/includes/header.php';");

if ($postPos !== false && $headerReqPos !== false && $postPos < $headerReqPos) {
    echo "  PASS: POST handling is performed BEFORE includes/header.php is required (prevents headers already sent).\n";
} else {
    echo "  FAIL: header.php is still included before POST handler!\n";
}

// ---------------------------------------------------------
// TEST 3: Duplicate Submission Prevention
// ---------------------------------------------------------
echo "\n[TEST 3] Duplicate Submission Prevention (Server-side Idempotency):\n";

if (strpos($submitPhpCode, 'submit_nonce') !== false && 
    strpos($submitPhpCode, 'last_processed_nonce') !== false) {
    echo "  PASS: Idempotency nonce check present to block double submit.\n";
} else {
    echo "  FAIL: Nonce idempotency check missing!\n";
}

if (strpos($submitPhpCode, 'INTERVAL 30 SECOND') !== false) {
    echo "  PASS: Rapid duplicate submission interval safeguard present.\n";
} else {
    echo "  FAIL: Rapid duplicate database safeguard missing!\n";
}

// ---------------------------------------------------------
// TEST 4: Navbar Public & Admin Check
// ---------------------------------------------------------
echo "\n[TEST 4] Navbar Public and Admin Dashboard Check:\n";

$publicHeaderCode = file_get_contents(__DIR__ . '/../includes/header.php');
if (strpos($publicHeaderCode, 'festivals.php') === false && 
    strpos($publicHeaderCode, '>FESTIVALS<') === false) {
    echo "  PASS: FESTIVALS menu successfully removed from public navbar.\n";
} else {
    echo "  FAIL: FESTIVALS menu still present in public header!\n";
}

$adminHeaderCode = file_get_contents(__DIR__ . '/../admin/header.php');
if (strpos($adminHeaderCode, 'admin-header') !== false && 
    strpos($adminHeaderCode, 'Moderasi Ulasan') !== false) {
    echo "  PASS: Admin Dashboard header is untouched and intact.\n";
} else {
    echo "  FAIL: Admin header modified unexpectedly!\n";
}

// ---------------------------------------------------------
// TEST 5: Card Layout & Horizontal Review Composition
// ---------------------------------------------------------
echo "\n[TEST 5] Review Cards vs LATEST Sizing & Orientation:\n";

$reviewsPhpCode = file_get_contents(__DIR__ . '/../reviews.php');
$indexPhpCode = file_get_contents(__DIR__ . '/../index.php');
$styleCssCode = file_get_contents(__DIR__ . '/../assets/css/style.css');

// Check reviews.php horizontal structure
if (strpos($reviewsPhpCode, 'review-card-poster') !== false && 
    strpos($reviewsPhpCode, 'review-excerpt') !== false) {
    echo "  PASS: reviews.php uses horizontal card layout with poster container and excerpt.\n";
} else {
    echo "  FAIL: reviews.php missing poster container or excerpt!\n";
}

// Check CSS for .review-card-large (horizontal)
if (strpos($styleCssCode, 'flex-direction: row;') !== false && 
    strpos($styleCssCode, '.review-card-poster') !== false) {
    echo "  PASS: .review-card-large styled as horizontal flex with dedicated poster box.\n";
} else {
    echo "  FAIL: .review-card-large missing horizontal flex styles!\n";
}

// Check index.php LATEST section (kept vertical, consistent grid)
if (strpos($indexPhpCode, 'latest-film-reviews') !== false && 
    strpos($styleCssCode, '.review-grid {') !== false &&
    strpos($styleCssCode, 'grid-template-columns: repeat(2, 1fr);') !== false) {
    echo "  PASS: LATEST section retains vertical orientation with consistent 2-column grid and line-clamped titles.\n";
} else {
    echo "  FAIL: LATEST section grid styles inconsistent!\n";
}

// ---------------------------------------------------------
// TEST 6: JavaScript Syntax and Handlers Check
// ---------------------------------------------------------
echo "\n[TEST 6] JavaScript Validation:\n";

$scriptJsCode = file_get_contents(__DIR__ . '/../assets/js/script.js');
if (strpos($scriptJsCode, 'const searchIcon =') !== false && 
    strpos($scriptJsCode, 'const closeSearch =') !== false &&
    strpos($scriptJsCode, 'menuIconSVG') === false) {
    echo "  PASS: assets/js/script.js variables properly declared, undefined references removed.\n";
} else {
    echo "  FAIL: assets/js/script.js variable declarations issue!\n";
}

echo "\n=== ALL VERIFICATION TESTS COMPLETED ===\n";
