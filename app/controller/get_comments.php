<?php
session_start();
require_once '../../database/database.php';
require_once '../DAO/postdao.php';

if (!isset($_GET['post_id'])) exit('No post specified.');

$postId = intval($_GET['post_id']);
$db = new Database();
$conn = $db->connect();
$postDAO = new Postdao($conn);

$post = $postDAO->getPostById($postId);

// Debug check to ensure post is retrieved correctly
if (!$post) {
    echo "Error: Post not found";
    exit();
}

$comments = $postDAO->getCommentsByPostId($postId);

// Get user ID for like status if user is logged in
$userId = 0;
if (isset($_SESSION['username'])) {
    require_once '../DAO/userdao.php';
    $userDAO = new Userdao($conn);
    $userObj = $userDAO->getUserByUsername($_SESSION['username']);
    $userId = $userObj ? $userObj->getUserId() : 0;
}
$liked = $postDAO->hasUserLikedPost($postId, $userId);
$likeCount = $postDAO->getLikeCountByPostId($postId);

// Get post username directly if needed
$username = $post->getUsername();
?>
<div class="modal-post">
    <div class="post-header">
        <div class="post-user-avatar">
            <?php 
                $postUsername = $post->getUsername();
                echo $postUsername ? strtoupper(substr($postUsername, 0, 1)) : '?'; 
            ?>
        </div>
        <div class="post-username">
            <?php echo htmlspecialchars($username ?? 'Unknown User'); ?>
        </div>
    </div>
    <div class="post-content">
        <?php echo nl2br(htmlspecialchars($post->getCaption())); ?>
    </div>
    <?php if ($post->getContentURL() && !empty($post->getContentURL())): ?>
        <img class="post-image" src="<?php echo htmlspecialchars($post->getContentURL()); ?>" alt="Post image">
    <?php endif; ?>
    <button class="like-btn<?php echo $liked ? ' liked' : ''; ?>" data-post-id="<?php echo $postId; ?>">
        <span class="like-icon">👍</span>
        <span class="like-count" id="modal-like-count-<?php echo $postId; ?>"><?php echo $likeCount; ?></span>
    </button>
</div>
        <div class="post-date">
            <?php echo date("M d, Y H:i", strtotime($post->getCreatedAt())); ?>
        </div>
<div class="comments-section">
    <h3>Comments</h3>
    <div id="commentsList">
        <?php if (empty($comments)): ?>
            <div style="color:#888;">No comments yet.</div>
        <?php else: ?>
            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <b><?php echo htmlspecialchars($comment->getUsername() ?? 'Unknown User'); ?>:</b>
                    <?php echo htmlspecialchars($comment->getContent()); ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <form id="commentForm" class="comment-form" style="margin-top:12px;">
        <textarea name="comment" rows="2" required placeholder="Add a comment..."></textarea>
        <button type="submit">Reply</button>
    </form>
</div>