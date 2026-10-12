<?php
/**
 * Search API endpoint
 * Azumi Movie Reviews
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$q = trim($_GET['q'] ?? '');

if (empty($q) || mb_strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $pdo = getDbConnection();
    $term = '%' . $q . '%';
    $results = [];

    // 1. Search Approved Reviews
    $stmt = $pdo->prepare("
        SELECT id, title, category, author_name 
        FROM film_submissions 
        WHERE status = 'approved' 
          AND (title LIKE :term1 OR content LIKE :term2 OR author_name LIKE :term3)
        ORDER BY COALESCE(reviewed_at, submitted_at) DESC 
        LIMIT 6
    ");
    $stmt->execute([
        ':term1' => $term,
        ':term2' => $term,
        ':term3' => $term
    ]);

    while ($row = $stmt->fetch()) {
        $results[] = [
            'id'       => (int) $row['id'],
            'title'    => $row['title'],
            'type'     => 'review',
            'category' => $row['category'] ?: 'REVIEWS',
            'meta'     => $row['author_name'] ?: 'Azumi Team',
            'url'      => 'review-detail.php?id=' . $row['id']
        ];
    }

    // 2. Search Upcoming Films
    $upStmt = $pdo->prepare("
        SELECT id, title 
        FROM upcoming_films 
        WHERE title LIKE :term 
        ORDER BY created_at DESC 
        LIMIT 4
    ");
    $upStmt->execute([':term' => $term]);

    while ($upRow = $upStmt->fetch()) {
        $results[] = [
            'id'       => (int) $upRow['id'],
            'title'    => $upRow['title'],
            'type'     => 'upcoming',
            'category' => 'UPCOMING WATCHLIST',
            'meta'     => 'Editorial Watchlist',
            'url'      => 'upcoming.php'
        ];
    }

    echo json_encode($results);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database search error']);
}
