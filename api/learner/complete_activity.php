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
        try {
            $checkRewardStmt = $pdo->prepare(
                "SELECT id
                FROM rewards
                WHERE learner_id = :learner_id
                  AND (
                      badge_name = :badge_name
                      OR reward_type = :badge_name
                  )
                LIMIT 1"
            );
        } catch (PDOException $checkWithTypeError) {
            $checkRewardStmt = $pdo->prepare(
                "SELECT id
                FROM rewards
                WHERE learner_id = :learner_id
                  AND badge_name = :badge_name
                LIMIT 1"
            );
        }

        $insertRewardWithTypeStmt = $pdo->prepare(
            "INSERT INTO rewards (learner_id, badge_name, reward_type)
            VALUES (:learner_id, :badge_name, 'badge')"
        );
        $insertRewardBasicStmt = $pdo->prepare(
            "INSERT INTO rewards (learner_id, badge_name)
            VALUES (:learner_id, :badge_name)"
        );

        foreach ($milestones as $threshold => $badgeName) {
            if ($totalCompleted >= $threshold) {
                $checkRewardStmt->execute([
                    'learner_id' => $learnerId,
                    'badge_name' => $badgeName
                ]);

                if (!$checkRewardStmt->fetch()) {
                    $params = [
                        'learner_id' => $learnerId,
                        'badge_name' => $badgeName
                    ];

                    try {
                        $insertRewardWithTypeStmt->execute([
                            'learner_id' => $learnerId,
                            'badge_name' => $badgeName
                        ]);
                    } catch (PDOException $insertWithTypeError) {
                        try {
                            $fallbackWithTypeStmt = $pdo->prepare(
                                "INSERT INTO rewards (learner_id, badge_name, reward_type)
                                VALUES (:learner_id, :badge_name, :reward_type)"
                            );
                            $fallbackWithTypeStmt->execute([
                                'learner_id' => $learnerId,
                                'badge_name' => $badgeName,
                                'reward_type' => $badgeName
                            ]);
                        } catch (PDOException $fallbackWithTypeError) {
                            $insertRewardBasicStmt->execute($params);
                        }
                    }

                    $newReward = $badgeName;
                }
            }
        }
    } catch (PDOException $rewardException) {
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
