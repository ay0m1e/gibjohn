<?php
require __DIR__ . '/../core/auth_guard.php';
require __DIR__ . '/../core/db.php';

header('Content-Type: application/json');

requireAuth('learner');

try {
    $learnerId = $_SESSION['user_id'];

    $stmt = $pdo->prepare(
        "SELECT   s.id, s.name, s.description,
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


    $stmt->execute([
        'learner_id' => $learnerId
    ]);
    $subjects = $stmt->fetchAll();


    foreach($subjects as &$subject) {
        if ($subject['total_content'] > 0) {
            $subject['progress_percentage'] = round(
                ($subject['completed_content'] / $subject['total_content']) * 100
            );
        } else {
            $subject['progress_percentage'] = 0;
        }
    }

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
