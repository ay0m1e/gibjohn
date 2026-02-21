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

$learnerId = $_GET['learner_id'] ?? null;

if (!$learnerId || !ctype_digit((string) $learnerId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Valid learner_id required']);
    exit;
}

try {
    $learnerStmt = $pdo->prepare(
        "SELECT id, email
        FROM users
        WHERE id = :learner_id
          AND role = 'learner'
        LIMIT 1"
    );
    $learnerStmt->execute(['learner_id' => $learnerId]);
    $learner = $learnerStmt->fetch();

    if (!$learner) {
        http_response_code(404);
        echo json_encode(['error' => 'Learner not found']);
        exit;
    }

    $progressStmt = $pdo->prepare(
        "SELECT s.id, s.name, s.description,
            COUNT(lc.id) AS total_content,
            COUNT(pr.id) AS completed_content
        FROM subjects s
        LEFT JOIN learning_content lc
            ON lc.subject_id = s.id
        LEFT JOIN progress_records pr
            ON pr.content_id = lc.id
            AND pr.learner_id = :learner_id
            AND pr.completed = 1
        GROUP BY s.id
        ORDER BY s.id ASC"
    );
    $progressStmt->execute(['learner_id' => $learnerId]);

    $subjects = $progressStmt->fetchAll();

    foreach ($subjects as &$subject) {
        $total = (int) $subject['total_content'];
        $completed = (int) $subject['completed_content'];

        $subject['progress_percentage'] = $total > 0
            ? (int) round(($completed / $total) * 100)
            : 0;
    }
    unset($subject);

    echo json_encode([
        'success' => true,
        'learner' => $learner,
        'subjects' => $subjects
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch learner progress'
    ]);
}
