<?php
header('Content-Type: application/json');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => true,
        'logged_in' => true,
        'role' => $_SESSION['role'] ?? null
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'logged_in' => false
]);
