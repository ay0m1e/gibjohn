<?php
require __DIR__ . '/../core/auth_guard.php';
require __DIR__. '/../core/db.php';

header('Content-Type: application/json');
requireAuth('learner');

$subjectId = $_GET['subject_id'] ?? null;

if (!$subjectId){
    http_response_code(400);
    echo json_encode(['error' => 'Subject ID required']);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "SELECT lc.id, lc.content_text, lc.content_order, pr.completed
        FROM learning_content lc
        LEFT JOIN progress_records pr
            ON pr.content_id = lc.id
            AND pr.learner_id = :learner_id
        WHERE lc.subject_id = :subject_id
        ORDER BY lc.content_order ASC
        "
    );

    $stmt->execute([
        'subject_id' => $subjectId,
        'learner_id' => $_SESSION['user_id']
        ]);
    $content = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'content' => $content
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error'=> 'Failed to fetch content'
    ]);
}
