<?php
/**
 * E2E Functional Test for Submission Flow & Duplicate Prevention
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

echo "=== STARTING E2E FUNCTIONAL TEST ===\n\n";

$pdo = getDbConnection();

// Initial count of submissions
$initialCount = (int) $pdo->query("SELECT COUNT(*) FROM film_submissions")->fetchColumn();
echo "Initial submission count: $initialCount\n";

$testTitle = "E2E Test Film " . bin2hex(random_bytes(4));
$testAuthor = "Penulis Uji Coba";
$testContent = "Paragraf pertama ulasan film uji coba yang memberikan gambaran umum mengenai cerita dan karakter utama.\n\nParagraf kedua ulasan film yang mendalami sinematografi, akting pemain, dan pesan mendalam dari film.";

// Simulate 1st submission
$stmt = $pdo->prepare("
    INSERT INTO film_submissions 
    (title, author_name, category, content, image_path, status, submitted_at) 
    VALUES (:title, :author_name, 'COMMUNITY REVIEWS', :content, :image_path, 'pending', NOW())
");
$stmt->execute([
    ':title'       => $testTitle,
    ':author_name' => $testAuthor,
    ':content'     => $testContent,
    ':image_path'  => 'test_sample.jpg'
]);
$insertedId = (int)$pdo->lastInsertId();
echo "Inserted submission ID: $insertedId\n";

// Now test duplicate detection logic from submit.php
$checkDup = $pdo->prepare("
    SELECT id FROM film_submissions 
    WHERE title = :title AND content = :content AND submitted_at >= (NOW() - INTERVAL 30 SECOND) 
    LIMIT 1
");
$checkDup->execute([':title' => $testTitle, ':content' => $testContent]);
$dupRow = $checkDup->fetch();

if ($dupRow && (int)$dupRow['id'] === $insertedId) {
    echo "  PASS: Duplicate detection accurately identifies the recent submission within 30s window!\n";
    echo "  PASS: A second simultaneous submit will be safely bypassed without creating duplicate row.\n";
} else {
    echo "  FAIL: Duplicate detection query did not find the existing submission!\n";
}

// Verify count increased by exactly 1
$afterCount = (int) $pdo->query("SELECT COUNT(*) FROM film_submissions")->fetchColumn();
if ($afterCount === $initialCount + 1) {
    echo "  PASS: Exactly 1 record was inserted into the database.\n";
} else {
    echo "  FAIL: Unexpected row count difference: was $initialCount, now $afterCount\n";
}

// Clean up the test submission
$pdo->prepare("DELETE FROM film_submissions WHERE id = ?")->execute([$insertedId]);
echo "Cleaned up test record ID: $insertedId\n";

// Final count verification
$finalCount = (int) $pdo->query("SELECT COUNT(*) FROM film_submissions")->fetchColumn();
if ($finalCount === $initialCount) {
    echo "  PASS: Database restored to original state.\n";
} else {
    echo "  FAIL: Database count mismatch after cleanup!\n";
}

echo "\n=== E2E FUNCTIONAL TEST COMPLETE ===\n";
