<?php
require __DIR__ . '/../core/auth_guard.php';
require __DIR__ . '/../core/db.php';

header('Content-Type: application/json');

requireAuth('learner');

try {
    $stmt = $pdo->query(
        "SELECT id, name, description
        FROM subjects
        ORDER BY id ASC
        "
    );

    $subjects = $stmt->fetchAll();


    echo json_encode([
        'success' => true,
        'subjects' => $subjects
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch subjects'
    ]);
}