<?php
session_start();
require __DIR__ . '/../core/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}


$email = trim($_POST['email'] ?? '');
$password = $_POST ['password'] ?? '';
$role = $_POST['role'] ?? 'learner';


if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Valid email required']);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['error' => 'Password must be at least 6 characters']);
    exit;
}

if (!in_array($role, ['learner', 'tutor'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid role']);
    exit;
}


$passwordHash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare(
        "INSERT INTO users (email, password_hash, role)
        VALUES (:email, :password_hash, :role)"
    );


    $stmt -> execute([
        'email' => $email,
        'password_hash' => $passwordHash,
        'role' => $role
    ]);

    $userId = $pdo->lastInsertId();
    if ($role === 'learner') {
        $pdo->prepare(
            "INSERT INTO learner_profiles (user_id)
            VALUES (:user_id)"
        )->execute(['user_id' => $userId]);
    } else {
        $pdo->prepare(
            "INSERT INTO tutor_profiles (user_id)
            VALUES (:user_id)"
        )-> execute (['user_id' => $userId]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e){
    if ($e->errorInfo[1] == 1062){
        http_response_code(409);
        echo json_encode(['error' => 'Email already exists']);
    } else{
        http_response_code(500);
        echo json_encode(['error' =>'Registration failed']);
    }
}



