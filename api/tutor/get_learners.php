<?php
require __DIR__ . '/../core/auth_guard.php';
require __DIR__ . '/../core/db.php';

header('Content-Type: application/json');

requireAuth('tutor');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $stmt = $pdo->query(
        "SELECT id, email
        FROM users
        WHERE role = 'learner'
        ORDER BY email ASC"
    );

    echo json_encode([
        'success' => true,
        'learners' => $stmt->fetchAll()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch learners'
    ]);
}
