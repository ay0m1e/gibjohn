<?php
session_start();

function requireAuth ($requireRole = null){

    if (!isset ($_SESSION['user_id'])){
        http_response_code(401);
        echo json_encode(['error' => 'Unauthenticated']);
    }

    if ($requiredRole !== null) {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== $requiredRole) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }
    }
}
