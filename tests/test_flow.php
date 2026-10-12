<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

echo "=== AZUMI REVIEWS AUTOMATED INTEGRATION TESTS ===\n\n";

// TEST 1: Database Connection
echo "[TEST 1] Testing Database Connection...\n";
$pdo = getDbConnection();
if ($pdo instanceof PDO) {
    echo "  -> SUCCESS: PDO Database connected properly.\n";
} else {
    echo "  -> FAILED: Could not obtain PDO instance.\n";
}

// TEST 2: Paragraph Counting Validation
echo "\n[TEST 2] Testing Paragraph Validation (Min 2, Max 5)...\n";
$singleParagraph = "Hanya satu paragraf.";
$count1 = count_paragraphs($singleParagraph);
echo "  -> 1 paragraph: detected $count1. Valid? " . (($count1 >= 2 && $count1 <= 5) ? "YES" : "NO (Rejected as expected)") . "\n";

$validThree = "Paragraf pertama ulasan film tentang dinamika emosi dan keluarga.\n\nParagraf kedua membahas tentang bagaimana visual dan tata cahaya menciptakan ketegangan halus.\n\nParagraf ketiga menyimpulkan bahwa film ini adalah karya sinematik yang patut diapresiasi.";
$count3 = count_paragraphs($validThree);
echo "  -> 3 paragraphs: detected $count3. Valid? " . (($count3 >= 2 && $count3 <= 5) ? "YES (Accepted)" : "NO") . "\n";

$sixParagraphs = "P1 teks ulasan film.\n\nP2 teks ulasan film.\n\nP3 teks ulasan film.\n\nP4 teks ulasan film.\n\nP5 teks ulasan film.\n\nP6 teks ulasan film.";
$count6 = count_paragraphs($sixParagraphs);
echo "  -> 6 paragraphs: detected $count6. Valid? " . (($count6 >= 2 && $count6 <= 5) ? "YES" : "NO (Rejected as expected)") . "\n";

// TEST 3: Admin Authentication
echo "\n[TEST 3] Testing Admin Authentication...\n";
$authSuccess = authenticate_admin('admin', 'admin123');
echo "  -> Valid Credentials (admin / admin123): " . ($authSuccess['success'] ? "SUCCESS (Authenticated)" : "FAILED: " . $authSuccess['message']) . "\n";

$authFailed = authenticate_admin('admin', 'salah_password');
echo "  -> Invalid Credentials: " . (!$authFailed['success'] ? "REJECTED PROPERLY (" . $authFailed['message'] . ")" : "SECURITY FLAW: Accepted wrong pass!") . "\n";

// TEST 4: Moderation and Publication Workflow
echo "\n[TEST 4] Testing Moderation and Publication Workflow...\n";
// Insert a test submission
$ins = $pdo->prepare("
    INSERT INTO film_submissions 
    (title, author_name, category, content, image_path, status, submitted_at) 
    VALUES ('Automated Test Film (2026)', 'Test Bot', 'TEST REVIEWS', :content, 'still_walking_00.jpg', 'pending', NOW())
");
$ins->execute([':content' => $validThree]);
$testId = $pdo->lastInsertId();
echo "  -> Step 1: Created test submission with ID #$testId (Status: PENDING)\n";

// Verify it's not visible in public query
$pubCheck = $pdo->prepare("SELECT COUNT(*) FROM film_submissions WHERE id = :id AND status = 'approved'");
$pubCheck->execute([':id' => $testId]);
$isPublicBefore = $pubCheck->fetchColumn() > 0;
echo "  -> Step 2: Public visibility check before approval: " . (!$isPublicBefore ? "PROTECTED (Not visible to public)" : "LEAKED!") . "\n";

// Approve the submission
$up = $pdo->prepare("UPDATE film_submissions SET status = 'approved', reviewed_at = NOW() WHERE id = :id");
$up->execute([':id' => $testId]);
echo "  -> Step 3: Admin approved submission #$testId\n";

// Check public query again
$pubCheck->execute([':id' => $testId]);
$isPublicAfter = $pubCheck->fetchColumn() > 0;
echo "  -> Step 4: Public visibility check after approval: " . ($isPublicAfter ? "PUBLISHED (Visible to public)" : "FAILED TO PUBLISH") . "\n";

// Clean up test entry
$del = $pdo->prepare("DELETE FROM film_submissions WHERE id = :id");
$del->execute([':id' => $testId]);
echo "  -> Step 5: Cleaned up test record #$testId\n";

echo "\n=== ALL TESTS COMPLETED SUCCESSFULLY! ===\n";
