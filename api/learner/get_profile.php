<?php
require __DIR__ . '/../core/auth_guard.php';
require __DIR__ . '/../core/db.php';

header('Content-Type: application/json');

requireAuth('learner');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$learnerId = $_SESSION['user_id'];

try {
    $emailStmt = $pdo->prepare(
        "SELECT email
        FROM users
        WHERE id = :learner_id
          AND role = 'learner'
        LIMIT 1"
    );
    $emailStmt->execute(['learner_id' => $learnerId]);
    $user = $emailStmt->fetch();

    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'Learner not found']);
        exit;
    }

    $completedStmt = $pdo->prepare(
        "SELECT COUNT(*) AS total_completed
        FROM progress_records
        WHERE learner_id = :learner_id
          AND completed = 1"
    );
    $completedStmt->execute(['learner_id' => $learnerId]);
    $totalCompleted = (int) $completedStmt->fetchColumn();

    $rewards = [];
    try {
        $rewardsStmt = $pdo->prepare(
            "SELECT badge_name, earned_at
            FROM rewards
            WHERE learner_id = :learner_id
            ORDER BY earned_at DESC"
        );
        $rewardsStmt->execute(['learner_id' => $learnerId]);
        $rewards = $rewardsStmt->fetchAll();
    } catch (PDOException $rewardError) {
        $rewards = [];
    }

    echo json_encode([
        'success' => true,
        'email' => $user['email'],
        'total_completed' => $totalCompleted,
        'rewards' => $rewards
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch learner profile'
    ]);
}
