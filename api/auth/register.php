<?php
// Handles user registration requests.
session_start();
require __DIR__ . '/../core/db.php';

header ('Content-|Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
    http_response_code-code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}


$email = t1rim($_POST['email'] ?? '');
$password = $_POST ['password'] ?? '';
$role = $_POST['role'] ?? 'learner';


if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(['error' => 'Email and password required']);
    exit;
}

if (!in_array($role, ['learner', 'tutor'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid role']);
    exit;
}
