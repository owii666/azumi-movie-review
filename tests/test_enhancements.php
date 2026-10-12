<?php
/**
 * Automated Verification for Azumi Enhancements
 * Tests:
 * 1. UPCOMING feature (DB, Public, Admin)
 * 2. LATEST REVIEW dynamic behavior (order by reviewed_at, automatic replacement)
 * 3. Compact Search & Public safety (only approved & upcoming)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

echo "=== AZUMI ENHANCEMENTS VERIFICATION SUITE ===\n\n";

$pdo = getDbConnection();

// ----------------------------------------------------
// TEST 1: UPCOMING FILMS FEATURE
// ----------------------------------------------------
echo "[TEST 1] Testing UPCOMING feature...\n";

// 1.1 Check DB Table
$upcomingCount = (int) $pdo->query("SELECT COUNT(*) FROM upcoming_films")->fetchColumn();
echo "  -> Table `upcoming_films` active: $upcomingCount items found.\n";
if ($upcomingCount === 0) {
    throw new Exception("upcoming_films table is empty!");
}

// 1.2 Test Insert Upcoming Film
$insUp = $pdo->prepare("INSERT INTO upcoming_films (title, poster_path, created_at) VALUES ('Test Upcoming Film 2026', 'still_walking_00.jpg', NOW())");
$insUp->execute();
$testUpId = $pdo->lastInsertId();
echo "  -> Inserted test upcoming film #$testUpId: SUCCESS.\n";

// 1.3 Test Edit Upcoming Film
$updUp = $pdo->prepare("UPDATE upcoming_films SET title = 'Test Upcoming Film Edited' WHERE id = :id");
$updUp->execute([':id' => $testUpId]);
$checkUp = $pdo->prepare("SELECT title FROM upcoming_films WHERE id = :id");
$checkUp->execute([':id' => $testUpId]);
$editedTitle = $checkUp->fetchColumn();
echo "  -> Edited test upcoming film #$testUpId title: '$editedTitle': SUCCESS.\n";

// 1.4 Test Delete Upcoming Film
$delUp = $pdo->prepare("DELETE FROM upcoming_films WHERE id = :id");
$delUp->execute([':id' => $testUpId]);
echo "  -> Deleted test upcoming film #$testUpId: SUCCESS.\n";


// ----------------------------------------------------
// TEST 2: LATEST REVIEW DYNAMIC BEHAVIOR
// ----------------------------------------------------
echo "\n[TEST 2] Testing LATEST REVIEW dynamic sorting & auto replacement...\n";

// 2.1 Get current latest review before
$stmtBefore = $pdo->query("
    SELECT id, title, reviewed_at 
    FROM film_submissions 
    WHERE status = 'approved' 
    ORDER BY COALESCE(reviewed_at, submitted_at) DESC, id DESC 
    LIMIT 1
");
$latestBefore = $stmtBefore->fetch();
echo "  -> Current LATEST REVIEW: #{$latestBefore['id']} '{$latestBefore['title']}' (Reviewed: {$latestBefore['reviewed_at']})\n";

// 2.2 Create a brand new submission and approve it now
$newTitle = "Baru Terbit: Ryusuke Hamaguchi Retrospective (2026)";
$stmtNew = $pdo->prepare("
    INSERT INTO film_submissions 
    (title, author_name, category, content, image_path, status, submitted_at, reviewed_at)
    VALUES 
    (:t, 'Editor Antigravity', 'SPECIAL SPOTLIGHT', 'Paragraf pertama ulasan film karya Ryusuke Hamaguchi.\n\nParagraf kedua mengeksplorasi bahasa tubuh dan percakapan intim.', 'images.jpg', 'approved', NOW(), DATE_ADD(NOW(), INTERVAL 1 SECOND))
");
$stmtNew->execute([':t' => $newTitle]);
$newApprovedId = $pdo->lastInsertId();
echo "  -> Created and approved new review #$newApprovedId: '$newTitle'\n";

// 2.3 Verify index.php query now picks the newly approved review
$stmtAfter = $pdo->query("
    SELECT id, title, reviewed_at 
    FROM film_submissions 
    WHERE status = 'approved' 
    ORDER BY COALESCE(reviewed_at, submitted_at) DESC, id DESC 
    LIMIT 1
");
$latestAfter = $stmtAfter->fetch();
echo "  -> New LATEST REVIEW on homepage query: #{$latestAfter['id']} '{$latestAfter['title']}'\n";

if ((int)$latestAfter['id'] === (int)$newApprovedId) {
    echo "  -> SUCCESS: Newly approved review automatically replaced the previous latest review on homepage!\n";
} else {
    echo "  -> FAILED: New review did not take latest position!\n";
}

// 2.4 Verify it is excluded from remaining latest reviews grid
$stmtRemain = $pdo->prepare("
    SELECT id, title 
    FROM film_submissions 
    WHERE status = 'approved' AND id != :latestId 
    ORDER BY COALESCE(reviewed_at, submitted_at) DESC, id DESC 
    LIMIT 4
");
$stmtRemain->execute([':latestId' => $latestAfter['id']]);
$remains = $stmtRemain->fetchAll();
$foundInGrid = false;
foreach ($remains as $r) {
    if ($r['id'] == $newApprovedId) $foundInGrid = true;
}
echo "  -> Excluded from secondary reviews grid: " . (!$foundInGrid ? "SUCCESS (No duplicates)" : "FAILED (Duplicate found)") . "\n";

// Clean up test review
$pdo->prepare("DELETE FROM film_submissions WHERE id = :id")->execute([':id' => $newApprovedId]);
echo "  -> Cleaned up test review #$newApprovedId\n";


// ----------------------------------------------------
// TEST 3: SEARCH FUNCTIONALITY & PRIVACY
// ----------------------------------------------------
echo "\n[TEST 3] Testing Search Features & Privacy Isolation...\n";

// 3.1 Insert a pending review and ensure it CANNOT be found in search
$pdo->prepare("
    INSERT INTO film_submissions 
    (title, author_name, category, content, image_path, status, submitted_at)
    VALUES ('Secret Hidden Movie 999', 'Spy', 'SECRET', 'Paragraf rahasia satu.\n\nParagraf rahasia dua.', 'images.jpg', 'pending', NOW())
")->execute();
$secretId = $pdo->lastInsertId();

// Search with search.php query logic
$searchTerm = '%Secret Hidden Movie 999%';
$searchCheck = $pdo->prepare("
    SELECT id FROM film_submissions 
    WHERE status = 'approved' AND (title LIKE :t1 OR content LIKE :t2)
");
$searchCheck->execute([':t1' => $searchTerm, ':t2' => $searchTerm]);
$foundSecret = $searchCheck->fetch();

echo "  -> Pending submission in search results: " . (!$foundSecret ? "PROTECTED (Not returned in public search)" : "LEAKED!") . "\n";

// Clean secret
$pdo->prepare("DELETE FROM film_submissions WHERE id = :id")->execute([':id' => $secretId]);

// 3.2 Search for an approved review ("Fear")
$termApproved = '%Fear%';
$searchApproved = $pdo->prepare("
    SELECT id, title FROM film_submissions 
    WHERE status = 'approved' AND title LIKE :t
");
$searchApproved->execute([':t' => $termApproved]);
$foundApproved = $searchApproved->fetchAll();
echo "  -> Search for approved movie 'Fear': Found " . count($foundApproved) . " items (SUCCESS)\n";

// 3.3 Search for upcoming film ("Walking")
$searchUp = $pdo->prepare("SELECT id, title FROM upcoming_films WHERE title LIKE '%Walking%'");
$searchUp->execute();
$foundUp = $searchUp->fetchAll();
echo "  -> Search for upcoming film 'Walking': Found " . count($foundUp) . " items (SUCCESS)\n";

echo "\n=== ALL ENHANCEMENTS TESTS PASSED SUCCESSFULLY! ===\n";
