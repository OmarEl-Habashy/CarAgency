<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../database/database.php';
require_once __DIR__ . '/../DAO/postdao.php';
require_once __DIR__ . '/../DAO/userdao.php';

if (!isset($_SESSION['username']) || !isset($_POST['post_id']) || !isset($_POST['comment'])) {
    echo json_encode(['success' => false]);
    exit;
}

$username = $_SESSION['username'];
$postId = intval($_POST['post_id']);
$comment = trim($_POST['comment']);

if ($comment === '') {
    echo json_encode(['success' => false]);
    exit;
}

$db = new Database();
$conn = $db->connect();
$userDAO = new Userdao($conn);
$postDAO = new Postdao($conn);

$user = $userDAO->getUserByUsername($username);
if (!$user) {
    echo json_encode(['success' => false]);
    exit;
}

$userId = $user->getUserId();
$success = $postDAO->insertComment($postId, $userId, $comment);

echo json_encode([
    'success' => $success,
    'username' => $username,
    'comment' => htmlspecialchars($comment)
]);