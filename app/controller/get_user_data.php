<?php
require_once '../../database/database.php';
require_once '../model/user.php';
require_once '../DAO/userdao.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$username = $_SESSION['username'];

$db = new Database();
$conn = $db->connect();
$userDAO = new Userdao($conn);

$userObj = $userDAO->getUserByUsername($username);
$userId = $userObj ? $userObj->getUserId() : 0;

$followingCount = $userDAO->getFollowingCount($userId) ?? 0;
$followersCount = $userDAO->getFollowerCount($userId) ?? 0;

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'username' => $username,
    'user_id' => $userId,
    'following_count' => $followingCount,
    'followers_count' => $followersCount
]);