<?php
function renderPost($postData, $userId) {
    extract($postData);
?>
    <div class="post" id="post-<?php echo $postId; ?>">
        <div class="post-header">
            <span class="post-username">@<?php echo strtolower(htmlspecialchars($postUsername)); ?></span>
            
            <?php if ($postUserId == $userId): ?>
                <form action="../app/controller/delete_post_controller.php" method="post" class="delete-form">
                    <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
                    <button type="submit" class="delete-post-btn" onclick="return confirm('Are you sure you want to delete this post?');">×</button>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="post-caption"><?php echo nl2br(htmlspecialchars($caption)); ?></div>
        
        <?php if ($contentURL): ?>
            <div class="post-media">
                <img src="<?php echo htmlspecialchars($contentURL); ?>" alt="Post media">
            </div>
        <?php endif; ?>
        
        <div class="post-meta">
            <span>Posted at: <?php echo date("M d, Y H:i", strtotime($createdAt)); ?></span>
            
            <div class="post-actions">
                <button class="like-btn<?php echo $liked ? ' liked' : ''; ?>" data-post-id="<?php echo $postId; ?>">
                    👍 <span class="like-count"><?php echo $likeCount; ?></span>
                </button>
                <button class="comments-btn" onclick="openCommentsModal(<?php echo $postId; ?>)">
                    💬 Comments
                </button>
            </div>
        </div>
    </div>
<?php
}
?>