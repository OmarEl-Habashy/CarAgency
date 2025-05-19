<?php
session_start();
require_once '../../database/database.php';
require_once '../DAO/postdao.php';
require_once '../DAO/userdao.php';
require_once '../model/Post.php';
require_once '../utils/MediaHandler.php'; // We'll create this utility

if (!isset($_SESSION['username']) || !isset($_POST['caption'])) {
    header("Location: ../feed.php?error=missing_data");
    exit();
}

$username = $_SESSION['username'];
$caption = trim($_POST['caption']);
$contentURL = '';

// Process media file upload if present
if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
    $mediaHandler = new MediaHandler();
    $uploadResult = $mediaHandler->uploadMedia($_FILES['media']);
    
    if ($uploadResult['success']) {
        $contentURL = $uploadResult['url'];
    } else {
        header("Location: ../feed.php?error=" . $uploadResult['error']);
        exit();
    }
}

// Validate caption
if (empty($caption) && empty($contentURL)) {
    header("Location: ../feed.php?error=empty_post");
    exit();
}

$db = new Database();
$conn = $db->connect();
$userDAO = new Userdao($conn);
$postDAO = new Postdao($conn);

$user = $userDAO->getUserByUsername($username);
if (!$user) {
    header("Location: ../feed.php?error=user_not_found");
    exit();
}

$userId = $user->getUserId();

// Create post object
$post = new Post(null, $userId, $contentURL, $caption, date('Y-m-d H:i:s'));
$post->setUsername($username);

// Insert post
$success = $postDAO->insertPost($post);

if ($success) {
    header("Location: ../feed.php?success=post_created");
} else {
    header("Location: ../feed.php?error=post_failed");
}
exit();
?>