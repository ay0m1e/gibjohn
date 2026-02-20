<?php
require __DIR__ . '/../core/auth_guard.php';
require __DIR__ . '/../core/db.php';

header('Content-Type: application/json');

requireAuth('learner');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$contentId = $_POST['content_id'] ?? null;

if (!$contentId || !ctype_digit((string) $contentId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Valid content_id required']);
    exit;
}

$learnerId = $_SESSION['user_id'];

try {
    $checkContent = $pdo->prepare(
        "SELECT id
        FROM learning_content
        WHERE id = :content_id
        LIMIT 1"
    );
    $checkContent->execute(['content_id' => $contentId]);

    if (!$checkContent->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Content not found']);
        exit;
    }

    $upsert = $pdo->prepare(
        "INSERT INTO progress_records (learner_id, content_id, completed)
        VALUES (:learner_id, :content_id, 1)
        ON DUPLICATE KEY UPDATE completed = 1"
    );

    $upsert->execute([
        'learner_id' => $learnerId,
        'content_id' => $contentId
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to mark activity complete']);
}
