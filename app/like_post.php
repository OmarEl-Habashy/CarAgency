<?php
session_start();
header('Content-Type: application/json');
require_once 'database.php';
require_once 'postdao.php';
require_once 'userdao.php';

if (!isset($_SESSION['username']) || !isset($_POST['post_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$username = $_SESSION['username'];
$postId = intval($_POST['post_id']);

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
$liked = $postDAO->hasUserLikedPost($postId, $userId);

if ($liked) {
    $postDAO->removeLike($postId, $userId);
    $liked = false;
} else {
    $postDAO->insertLike($postId, $userId);
    $liked = true;
}
$likeCount = $postDAO->getLikeCountByPostId($postId);

echo json_encode([
    'success' => true,
    'liked' => $liked,
    'like_count' => $likeCount
]);