<?php
require_once __DIR__ . '/post.php';

function renderFeed($posts, $postController, $userId) {
?>
    <div class="feed-posts">
        <?php if (empty($posts)): ?>
            <div class="empty-feed-message">No posts yet. Be the first to post!</div>
        <?php else: ?>
            <?php foreach ($posts as $post): ?>
                <?php 
                    $postData = $postController->getPostData($post, $userId);
                    renderPost($postData, $userId);
                ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php
}
?>