<?php
header('Content-Type: application/json');
require_once '../database/database.php';
require_once '../app/DAO/userdao.php';

try {
    // Get username from request
    $requestUsername = isset($_GET['username']) ? $_GET['username'] : null;
    
    if (!$requestUsername) {
        throw new Exception('Username parameter is required');
    }
    
    // Connect to database
    $db = new Database();
    $conn = $db->connect();
    
    if (!$conn) {
        throw new Exception('Failed to connect to the database');
    }
    
    $userDAO = new userdao($conn);
    $email = $userDAO->getUserEmail($requestUsername);
    
    if ($email) {
        echo json_encode([
            'success' => true,
            'email' => $email
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>