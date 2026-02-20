<?php
// Handles user login requests.
session_start();

require __DIR__ . '/../core/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';


if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)){
    http_response_code(400);
    echo json_encode(['error' => 'Valid email required']);
    exit;
}

if (!$password){
    http_response_code(400);
    echo json_encode(['error' => 'Password required']);
    exit;
}


$stmt = $pdo->prepare(
    "SELECT id, password_hash, role
    FROM users
    WHERE email = :email
    LIMIT 1
    "
);


$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid credentials']);
    exit;
}


$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];

echo json_encode([
    'success' => true,
    'role' => $user['role']
]);
