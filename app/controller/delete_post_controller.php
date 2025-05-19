<?php
session_start();
require_once '../../database/database.php';
require_once '../DAO/postdao.php';
require_once '../DAO/userdao.php';

// Check if user is logged in and post ID is provided
if (!isset($_SESSION['username']) || !isset($_POST['post_id'])) {
    header("Location: ../feed.php?error=delete_missing_data");
    exit();
}

$username = $_SESSION['username'];
$postId = intval($_POST['post_id']);

$db = new Database();
$conn = $db->connect();
$userDAO = new Userdao($conn);
$postDAO = new Postdao($conn);

// Get user ID
$user = $userDAO->getUserByUsername($username);
if (!$user) {
    header("Location: ../feed.php?error=user_not_found");
    exit();
}
$userId = $user->getUserId();

// Get post to check ownership
$post = $postDAO->getPostById($postId);
if (!$post) {
    header("Location: ../feed.php?error=post_not_found");
    exit();
}

// Check if user owns this post
if ($post->getUserId() != $userId) {
    header("Location: ../feed.php?error=unauthorized");
    exit();
}

// Delete the post
$success = $postDAO->deletePost($postId);

if ($success) {
    header("Location: ../feed.php?success=post_deleted");
} else {
    header("Location: ../feed.php?error=delete_failed");
}
exit();
?>