<?php
require_once 'database.php';
require_once 'User.php';
require_once 'Userdao.php';
require_once 'Postdao.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: register.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch all posts (implement getAllPosts() in your Postdao)
$db = new Database();
$conn = $db->connect();
$postDAO = new Postdao($conn);
$userDAO = new Userdao($conn);

$posts = $postDAO->getAllPosts(); // Should return posts with user info
$userObj = $userDAO->getUserByUsername($username);
$userId = $userObj ? $userObj->getUserId() : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Feed</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f6f9fc;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #2563eb;
            padding: 18px 32px;
            color: #fff;
            box-shadow: 0 2px 8px rgba(37,99,235,0.07);
        }
        .topbar-title {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #fff;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            cursor: pointer;
            border: 2px solid #2563eb;
            transition: box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(37,99,235,0.08);
        }
        .avatar:hover {
            box-shadow: 0 4px 16px rgba(37,99,235,0.18);
        }
        .feed-container {
            max-width: 600px;
            margin: 32px auto 0 auto;
            padding: 0 16px;
        }
        .post {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(37,99,235,0.07);
            padding: 20px 24px 16px 24px;
            margin-bottom: 28px;
            transition: box-shadow 0.2s;
        }
        .post:hover {
            box-shadow: 0 4px 16px rgba(37,99,235,0.13);
        }
        .post-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .post-user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #2563eb;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            font-weight: 700;
            margin-right: 12px;
        }
        .post-username {
            font-weight: 600;
            color: #2563eb;
            font-size: 1rem;
        }
        .post-date {
            margin-left: 10px;
            color: #8fa7d6;
            font-size: 0.92rem;
        }
        .post-content {
            font-size: 1.08rem;
            color: #222;
            margin-bottom: 8px;
        }
        .post-image {
            max-width: 100%;
            border-radius: 8px;
            margin-top: 8px;
            margin-bottom: 8px;
            box-shadow: 0 1px 4px rgba(37,99,235,0.08);
        }
        .logout-btn {
            background: #fff;
            color: #2563eb;
            border: 1.5px solid #fff;
            border-radius: 8px;
            padding: 8px 18px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .logout-btn:hover {
            background: #2563eb;
            color: #fff;
            border: 1.5px solid #fff;
        }
        .like-btn {
            background: none;
            border: none;
            cursor: pointer;
            outline: none;
            display: inline-flex;
            align-items: center;
            font-size: 1.1rem;
            color: #aaa;
            transition: color 0.2s;
            margin-right: 10px;
            margin-top: 8px;
            margin-bottom: 0;
            position: relative;
        }
        .like-btn .like-icon {
            font-size: 1.5rem;
            margin-right: 6px;
            transition: color 0.2s, transform 0.15s;
        }
        .like-btn.liked .like-icon {
            color: #2563eb;
            transform: scale(1.2);
            animation: pop 0.3s;
        }
        @keyframes pop {
            0% { transform: scale(1); }
            50% { transform: scale(1.4); }
            100% { transform: scale(1.2); }
        }
        .like-count {
            font-weight: 600;
            color: #2563eb;
            font-size: 1rem;
        }
        .comments-btn {
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 7px 16px;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 8px;
            margin-bottom: 0;
            transition: background 0.2s;
        }
        .comments-btn:hover {
            background: #1746a2;
        }
        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background: rgba(0,0,0,0.4);
        }
        .modal-content {
            background: #fff;
            margin: 5% auto;
            padding: 24px 24px 16px 24px;
            border-radius: 12px;
            width: 95%;
            max-width: 420px;
            position: relative;
        }
        .close {
            position: absolute;
            right: 18px;
            top: 10px;
            font-size: 28px;
            font-weight: bold;
            color: #aaa;
            cursor: pointer;
        }
        .close:hover {
            color: #2563eb;
        }
        .comments-section {
            margin-top: 10px;
        }
        .comment {
            border-bottom: 1px solid #eaeaea;
            padding: 7px 0;
            font-size: 1rem;
        }
        .comment b {
            color: #2563eb;
        }
        .comment-form textarea {
            width: 100%;
            border-radius: 6px;
            border: 1px solid #ccc;
            padding: 7px;
            font-size: 1rem;
            margin-top: 8px;
        }
        .comment-form button {
            margin-top: 6px;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 7px 16px;
            font-size: 1rem;
            cursor: pointer;
        }
        .comment-form button:hover {
            background: #1746a2;
        }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="topbar-title">Feed</div>
        <div class="topbar-actions">
            <button class="logout-btn" onclick="window.location.href='logout.php'">Logout</button>
            <div class="avatar" onclick="window.location.href='profile.php'">
                <?php echo strtoupper(substr($username, 0, 1)); ?>
            </div>
        </div>
    </div>
    <div class="feed-container">
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
                    <div class="post-content">
                        <?php
                            $caption = method_exists($post, 'getCaption') ? $post->getCaption() : (isset($post['caption']) ? $post['caption'] : '');
                            echo nl2br(htmlspecialchars($caption));
                        ?>
                    </div>
                    <?php
                        $contentURL = method_exists($post, 'getContentURL') ? $post->getContentURL() : (isset($post['content_url']) ? $post['content_url'] : '');
                        if ($contentURL):
                    ?>
                        <img class="post-image" src="<?php echo htmlspecialchars($contentURL); ?>" alt="Post image">
                    <?php endif; ?>
                    <button class="like-btn<?php echo $liked ? ' liked' : ''; ?>" data-post-id="<?php echo $postId; ?>">
                        <span class="like-icon">&#128077;</span>
                        <span class="like-count" id="like-count-<?php echo $postId; ?>"><?php echo $likeCount; ?></span>
                    </button>
                    <button class="comments-btn" onclick="openCommentsModal(<?php echo $postId; ?>)">View Comments</button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Comments Modal -->
    <div id="commentsModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <div id="commentsBody">
                <!-- Comments will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        // Like button AJAX
        document.querySelectorAll('.like-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const postId = this.getAttribute('data-post-id');
                fetch('like_post.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'post_id=' + postId
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const likeBtn = document.querySelector('.like-btn[data-post-id="' + postId + '"]');
                        const likeCount = document.getElementById('like-count-' + postId);
                        if (data.liked) {
                            likeBtn.classList.add('liked');
                        } else {
                            likeBtn.classList.remove('liked');
                        }
                        likeCount.textContent = data.like_count;
                    }
                });
            });
        });

        // Comments modal logic (unchanged)
        function openCommentsModal(postId) {
            fetch('get_comments.php?post_id=' + postId)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('commentsBody').innerHTML = html;
                    document.getElementById('commentsModal').style.display = 'block';
                    attachCommentFormHandler(postId);
                });
        }

        document.getElementById('closeModal').onclick = function() {
            document.getElementById('commentsModal').style.display = 'none';
        };
        window.onclick = function(event) {
            if (event.target == document.getElementById('commentsModal')) {
                document.getElementById('commentsModal').style.display = 'none';
            }
        };

        function attachCommentFormHandler(postId) {
            const form = document.getElementById('commentForm');
            if (form) {
                form.onsubmit = function(e) {
                    e.preventDefault();
                    const commentText = form.comment.value.trim();
                    if (!commentText) return;
                    fetch('add_comment.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'post_id=' + postId + '&comment=' + encodeURIComponent(commentText)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const commentsList = document.getElementById('commentsList');
                            const newComment = document.createElement('div');
                            newComment.className = 'comment';
                            newComment.innerHTML = '<b>' + data.username + ':</b> ' + data.comment;
                            commentsList.appendChild(newComment);
                            form.reset();
                        }
                    });
                }
            }
        }
    </script>
</body>
</html>