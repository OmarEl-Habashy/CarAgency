<?php
require_once '../database/database.php';
require_once 'model/user.php';
require_once 'DAO/userdao.php';
require_once 'DAO/postdao.php';
session_start();
$message = "";
if (isset($_GET['success']) && $_GET['success'] == 'post_created') {
    $message = "Post created successfully!";
} else if (isset($_GET['error'])) {
    switch($_GET['error']) {
        case 'missing_data':
            $message = "Error: Required data is missing.";
            break;
        case 'empty_post':
            $message = "Error: Post cannot be empty.";
            break;
        case 'user_not_found':
            $message = "Error: User not found.";
            break;
        case 'post_failed':
            $message = "Error: Failed to create post.";
            break;
    }
}
if (!isset($_SESSION['username'])) {
    header("Location: register.php");
    exit();
}


$username = $_SESSION['username'];

$db = new Database();
$conn = $db->connect();
$postDAO = new Postdao($conn);
$userDAO = new Userdao($conn);

$userObj = $userDAO->getUserByUsername($username);
$userId = $userObj ? $userObj->getUserId() : 0;
$posts = $postDAO->getFollowingPosts($userId);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Feed</title>
<head>
    <title>Feed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Project/public/css/feed.css">
    <link rel="stylesheet" href="/Project/public/css/navbar.css">
    <!-- Load the components.js file -->
    <script src="../public/js/components.js"></script>
    <script defer src="../public/js/feed.js"></script>
</head>
</head>
<body>
    <div class="page-container">
        <!-- Navbar will be loaded here -->
        <div data-component="navbar" class="navbar-container"></div>
        
        <div class="main-content">
            <div class="topbar">
                <div class="topbar-title">Feed</div>
            </div>
            
            <div class="feed-container">
                                <!-- Add create post form at the top of the feed -->
                <div class="create-post">
                    <div class="post-user-avatar">
                        <?php echo strtoupper(substr($username, 0, 1)); ?>
                    </div>
                    <div class="post-form-container">
                        <form action="../app/controller/create_post_controller.php" method="post">
                            <textarea name="caption" placeholder="What's happening?" rows="3" required></textarea>
                            <div class="post-form-actions">
                                <button type="submit" class="post-btn">Post</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php if (empty($posts)): ?>
                    <div style="color:#2563eb;text-align:center;font-size:1.1rem;margin-top:40px;">No posts yet. Be the first to post!</div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <?php
                            $postId = $post->getPostId();
                            $liked = $postDAO->hasUserLikedPost($postId, $userId);
                            $likeCount = $postDAO->getLikeCountByPostId($postId);
                        ?>
                        <div class="post" id="post-<?php echo $postId; ?>">
                            <!-- Post header with user info -->
                            <div class="post-header">
                                <div class="post-user-avatar">
                                    <?php
                                        $postUsername = method_exists($post, 'getUsername') ? $post->getUsername() : (isset($post['username']) ? $post['username'] : '');
                                        echo strtoupper(substr($postUsername, 0, 1));
                                    ?>
                                </div>
                                <div class="post-username"><?php echo htmlspecialchars($postUsername); ?></div>
                                <div class="post-date">
                                    <?php
                                        $createdAt = method_exists($post, 'getCreatedAt') ? $post->getCreatedAt() : (isset($post['created_at']) ? $post['created_at'] : '');
                                        echo date("M d, Y H:i", strtotime($createdAt));
                                    ?>
                                </div>
                            </div>
                            
                            <!-- Delete button (only for user's own posts) -->
                            <?php 
                            $postUserId = method_exists($post, 'getUserId') ? $post->getUserId() : (isset($post['user_id']) ? $post['user_id'] : 0);
                            if ($postUserId == $userId): 
                            ?>
                            <form action="../app/controller/delete_post_controller.php" method="post" class="delete-form">
                                <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
                                <button type="submit" class="delete-post-btn" onclick="return confirm('Are you sure you want to delete this post?');">×</button>
                            </form>
                            <?php endif; ?>
                            
                            <!-- Post content -->
                            <div class="post-content">
                                <?php
                                    $caption = method_exists($post, 'getCaption') ? $post->getCaption() : (isset($post['caption']) ? $post['caption'] : '');
                                    echo nl2br(htmlspecialchars($caption));
                                ?>
                                
                                <?php
                                    $contentURL = method_exists($post, 'getContentURL') ? $post->getContentURL() : (isset($post['content_url']) ? $post['content_url'] : '');
                                    if ($contentURL):
                                ?>
                                    <img class="post-image" src="<?php echo htmlspecialchars($contentURL); ?>" alt="Post image">
                                <?php endif; ?>
                            </div>
                            
                            <!-- Post actions -->
                            <div class="post-actions">
                                <button class="like-btn<?php echo $liked ? ' liked' : ''; ?>" data-post-id="<?php echo $postId; ?>">
                                    <span class="like-icon">👍</span>
                                    <span class="like-count" id="like-count-<?php echo $postId; ?>"><?php echo $likeCount; ?></span>
                                </button>
                                <button class="comments-btn" onclick="openCommentsModal(<?php echo $postId; ?>)">
                                    <span class="comment-icon">💬</span> Comments
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div data-component="footer"></div>

    <div id="commentsModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <div id="commentsBody">
                <!-- Comments will be loaded here -->
            </div>
        </div>
    </div>
</body>
</html>