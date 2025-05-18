<?php
session_start();
require_once 'database.php';
require_once 'postdao.php';

if (!isset($_GET['post_id'])) exit('No post specified.');

$postId = intval($_GET['post_id']);
$db = new Database();
$conn = $db->connect();
$postDAO = new Postdao($conn);

$comments = $postDAO->getCommentsByPostId($postId);
?>
<div class="comments-section">
    <h3 style="margin-bottom:10px;">Comments</h3>
    <div id="commentsList">
        <?php if (empty($comments)): ?>
            <div style="color:#888;">No comments yet.</div>
        <?php else: ?>
            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <b><?php echo htmlspecialchars($comment->getUsername()); ?>:</b>
                    <?php echo htmlspecialchars($comment->getContent()); ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <form id="commentForm" class="comment-form" style="margin-top:12px;">
        <textarea name="comment" rows="2" required placeholder="Add a comment..."></textarea>
        <button type="submit">Post Comment</button>
    </form>
</div>