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
$milestones = [
    5 => 'Starter Badge',
    10 => 'Momentum Badge',
    20 => 'Committed Learner'
];

try {
    $pdo->beginTransaction();

    $checkContent = $pdo->prepare(
        "SELECT id
        FROM learning_content
        WHERE id = :content_id
        LIMIT 1"
    );
    $checkContent->execute(['content_id' => $contentId]);

    if (!$checkContent->fetch()) {
        $pdo->rollBack();
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

    $countStmt = $pdo->prepare(
        "SELECT COUNT(*)
        FROM progress_records
        WHERE learner_id = :learner_id
          AND completed = 1"
    );
    $countStmt->execute(['learner_id' => $learnerId]);
    $totalCompleted = (int) $countStmt->fetchColumn();

    $newReward = null;
    try {
        $rewardExists = function ($badgeName) use ($pdo, $learnerId) {
            $queries = [
                "SELECT id
                FROM rewards
                WHERE learner_id = :learner_id
                  AND (badge_name = :badge_name OR reward_type = :badge_name)
                LIMIT 1",
                "SELECT id
                FROM rewards
                WHERE learner_id = :learner_id
                  AND badge_name = :badge_name
                LIMIT 1",
                "SELECT id
                FROM rewards
                WHERE learner_id = :learner_id
                  AND reward_type = :badge_name
                LIMIT 1"
            ];

            foreach ($queries as $query) {
                try {
                    $stmt = $pdo->prepare($query);
                    $stmt->execute([
                        'learner_id' => $learnerId,
                        'badge_name' => $badgeName
                    ]);

                    if ($stmt->fetch()) {
                        return true;
                    }
                } catch (PDOException $ignored) {
                    // Try the next compatible query shape.
                }
            }

            return false;
        };

        $tryInsertReward = function ($badgeName) use ($pdo, $learnerId) {
            $insertAttempts = [
                [
                    'sql' => "INSERT INTO rewards (learner_id, badge_name, reward_type)
                              VALUES (:learner_id, :badge_name, :reward_type)",
                    'params' => [
                        'learner_id' => $learnerId,
                        'badge_name' => $badgeName,
                        'reward_type' => 'badge'
                    ]
                ],
                [
                    'sql' => "INSERT INTO rewards (learner_id, badge_name, reward_type)
                              VALUES (:learner_id, :badge_name, :reward_type)",
                    'params' => [
                        'learner_id' => $learnerId,
                        'badge_name' => $badgeName,
                        'reward_type' => $badgeName
                    ]
                ],
                [
                    'sql' => "INSERT INTO rewards (learner_id, badge_name)
                              VALUES (:learner_id, :badge_name)",
                    'params' => [
                        'learner_id' => $learnerId,
                        'badge_name' => $badgeName
                    ]
                ],
                [
                    'sql' => "INSERT INTO rewards (learner_id, reward_type)
                              VALUES (:learner_id, :reward_type)",
                    'params' => [
                        'learner_id' => $learnerId,
                        'reward_type' => $badgeName
                    ]
                ]
            ];

            foreach ($insertAttempts as $attempt) {
                try {
                    $stmt = $pdo->prepare($attempt['sql']);
                    $stmt->execute($attempt['params']);
                    return;
                } catch (PDOException $ignored) {
                    // Try the next compatible insert shape.
                }
            }
        };

        foreach ($milestones as $threshold => $badgeName) {
            if ($totalCompleted < $threshold) {
                continue;
            }

            if ($rewardExists($badgeName)) {
                continue;
            }

            $tryInsertReward($badgeName);

            if ($rewardExists($badgeName)) {
                $newReward = $badgeName;
            }
        }
    } catch (PDOException $rewardError) {
        $newReward = null;
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'total_completed' => $totalCompleted,
        'new_reward' => $newReward,
        'reward' => $newReward ? ['name' => $newReward] : null
    ]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode(['error' => 'Failed to mark activity complete']);
}
