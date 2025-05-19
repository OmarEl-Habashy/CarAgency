<?php
session_start();
require_once '../database/database.php';
require_once 'DAO/userdao.php';
require_once 'model/user.php';
require_once 'DAO/postdao.php';

if (!isset($_SESSION['username'])) {
    header("Location: register.php");
    exit();
}

$loggedInUsername = $_SESSION['username'];
$profileUsername = isset($_GET['username']) ? $_GET['username'] : $loggedInUsername;

$db = new database();
$conn = $db->connect();
$user = null;
$userDAO = null;
$followerCount = 0;
$followingCount = 0;
$profile_error = '';
$isOwnProfile = ($loggedInUsername === $profileUsername);
$isFollowing = false;

if ($conn) {
    $userDAO = new userdao($conn);
    $user = $userDAO->getUserByUsername($profileUsername);
    $posts = [];
    if ($user) {
        $followerCount = $userDAO->getFollowerCount($user->getUserId());
        $followingCount = $userDAO->getFollowingCount($user->getUserId());
        $postDAO = new postdao($conn);
        $posts = $postDAO->getPostsByUserId($user->getUserId());

        if (!$isOwnProfile) {
            $loggedInUser = $userDAO->getUserByUsername($loggedInUsername);
            if ($loggedInUser) {
                $isFollowing = $userDAO->isFollowing($loggedInUser->getUserId(), $user->getUserId());
            }
        }
    } else {
        $profile_error = "User not found.";
    }
} else {
    $profile_error = "Failed to connect to the database.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $user && !$isOwnProfile) {
    $loggedInUser = $userDAO->getUserByUsername($loggedInUsername);
    if ($loggedInUser) {
        $loggedInUserId = $loggedInUser->getUserId();
        $profileUserId = $user->getUserId();

        if ($_POST['action'] == 'follow') {
            $userDAO->followUser($loggedInUserId, $profileUserId);
            $isFollowing = true;
        } elseif ($_POST['action'] == 'unfollow') {
            $userDAO->unfollowUser($loggedInUserId, $profileUserId);
            $isFollowing = false;
        }
        $followerCount = $userDAO->getFollowerCount($profileUserId);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile: <?php echo htmlspecialchars($profileUsername); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f5f8fa; color: #14171a; }
        .container { max-width: 600px; margin: 20px auto; background-color: #fff; border: 1px solid #e1e8ed; border-radius: 8px; }
        .profile-header { background-color: #1da1f2; padding: 20px; color: white; border-top-left-radius: 8px; border-top-right-radius: 8px; }
        .profile-header h1 { margin: 0; font-size: 24px; }
        .profile-header .username-handle { font-size: 16px; opacity: 0.8; }
        .profile-avatar { width: 100px; height: 100px; border-radius: 50%; border: 3px solid white; margin-top: -50px; margin-left: 20px; background-color: #ccc; display: flex; align-items: center; justify-content: center; font-size: 30px; color: #fff; }
        .profile-info { padding: 20px; }
        .profile-info .display-name { font-size: 20px; font-weight: bold; }
        .profile-info .bio { margin-top: 10px; color: #657786; }
        .profile-stats { display: flex; padding: 10px 20px; border-top: 1px solid #e1e8ed; }
        .profile-stats div { margin-right: 20px; }
        .profile-stats span { font-weight: bold; }
        .error-message { color: red; text-align: center; padding: 10px; }
        .follow-button-container { padding: 0 20px 20px 20px; }
        .follow-button { background-color: #1da1f2; color: white; border: none; padding: 10px 15px; border-radius: 20px; cursor: pointer; font-weight: bold; }
        .follow-button.following { background-color: #fff; color: #1da1f2; border: 1px solid #1da1f2; }
        .edit-profile-button { background-color: #fff; color: #1da1f2; border: 1px solid #1da1f2; padding: 10px 15px; border-radius: 20px; cursor: pointer; font-weight: bold; text-decoration: none; }
        .post { cursor: pointer; }
        /* Modal styles */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background: rgba(0,0,0,0.4);}
        .modal-content { background: #fff; margin: 5% auto; padding: 20px; border-radius: 8px; width: 90%; max-width: 500px; position: relative;}
        .close { position: absolute; right: 15px; top: 10px; font-size: 28px; font-weight: bold; color: #aaa; cursor: pointer;}
        .close:hover { color: #000; }
        .like-btn { background: none; border: none; color: #e0245e; font-size: 1.2em; cursor: pointer; }
        .like-btn.liked { color: #e0245e; font-weight: bold; }
        .comments-section { margin-top: 20px; }
        .comment { border-bottom: 1px solid #eee; padding: 5px 0; }
        .comment-form textarea { width: 100%; border-radius: 5px; border: 1px solid #ccc; padding: 5px; }
        .comment-form button { margin-top: 5px; background: #1da1f2; color: #fff; border: none; border-radius: 5px; padding: 5px 10px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($profile_error): ?>
            <p class="error-message"><?php echo htmlspecialchars($profile_error); ?></p>
        <?php elseif ($user): ?>
            <div class="profile-header"></div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 0 20px;">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr(htmlspecialchars($user->getUsername()), 0, 1)); ?>
                </div>
                <div class="follow-button-container" style="margin-top: 10px;">
                    <?php if (!$isOwnProfile): ?>
                        <form action="Profile.php?username=<?php echo htmlspecialchars($profileUsername); ?>" method="POST">
                            <?php if ($isFollowing): ?>
                                <input type="hidden" name="action" value="unfollow">
                                <button type="submit" class="follow-button following">Following</button>
                            <?php else: ?>
                                <input type="hidden" name="action" value="follow">
                                <button type="submit" class="follow-button">Follow</button>
                            <?php endif; ?>
                        </form>
                    <?php else: ?>
                        <a href="edit_profile.php" class="edit-profile-button">Edit Profile</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="profile-info">
                <div class="display-name"><?php echo htmlspecialchars($user->getUsername()); ?></div>
                <p class="bio"><?php echo htmlspecialchars($user->getBio() ?: 'No bio yet.'); ?></p>
                <p style="font-size: 0.9em; color: #657786;">Joined: <?php echo date("M Y", strtotime($user->getCreatedAt())); ?></p>
            </div>
            <div class="profile-stats">
                <div><span><?php echo $followingCount; ?></span> Following</div>
                <div><span><?php echo $followerCount; ?></span> Followers</div>
            </div>
            <!-- Posts Section -->
            <div style="padding: 20px; border-top: 1px solid #e1e8ed;">
                <h3>Posts</h3>
                <?php if (empty($posts)): ?>
                    <p style="text-align:center; color: #657786;">No posts yet.</p>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="post" data-post-id="<?php echo $post->getPostId(); ?>" style="margin-bottom: 20px; padding: 15px; border: 1px solid #e1e8ed; border-radius: 5px;">
                            <?php if ($post->getContentURL()): ?>
                                <img src="<?php echo htmlspecialchars($post->getContentURL()); ?>"
                                     style="max-width: 100%; border-radius: 5px; margin-bottom: 10px;"
                                     alt="Post image">
                            <?php endif; ?>
                            <p><?php echo htmlspecialchars($post->getCaption()); ?></p>
                            <div style="color: #657786; font-size: 0.9em; margin-top: 10px;">
                                <?php echo date("F j, Y, g:i a", strtotime($post->getCreatedAt())); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <!-- Modal for Post Details -->
            <div id="postModal" class="modal">
                <div class="modal-content">
                    <span class="close" id="closeModal">&times;</span>
                    <div id="modalBody">
                        <!-- Post details will be loaded here via JS -->
                    </div>
                </div>
            </div>
        <?php else: ?>
            <p class="error-message">User profile could not be loaded.</p>
        <?php endif; ?>
    </div>
    <script>
        // Modal logic
        const modal = document.getElementById('postModal');
        const closeModal = document.getElementById('closeModal');
        const modalBody = document.getElementById('modalBody');
        document.querySelectorAll('.post').forEach(postDiv => {
            postDiv.addEventListener('click', function() {
                const postId = this.getAttribute('data-post-id');
                fetch('get_post_details.php?post_id=' + postId)
                    .then(res => res.text())
                    .then(html => {
                        modalBody.innerHTML = html;
                        modal.style.display = 'block';
                        attachLikeHandler();
                        attachCommentHandler();
                    });
            });
        });
        closeModal.onclick = function() { modal.style.display = 'none'; }
        window.onclick = function(event) { if (event.target == modal) modal.style.display = 'none'; }

        // Like handler
        function attachLikeHandler() {
            const likeBtn = document.getElementById('likeBtn');
            if (likeBtn) {
                likeBtn.onclick = function(e) {
                    e.preventDefault();
                    const postId = this.getAttribute('data-post-id');
                    fetch('like_post.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'post_id=' + postId
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            likeBtn.classList.toggle('liked', data.liked);
                            document.getElementById('likeCount').textContent = data.like_count;
                        }
                    });
                }
            }
        }
        // Comment handler
        function attachCommentHandler() {
            const commentForm = document.getElementById('commentForm');
            if (commentForm) {
                commentForm.onsubmit = function(e) {
                    e.preventDefault();
                    const postId = this.querySelector('input[name="post_id"]').value;
                    const commentText = this.querySelector('textarea[name="comment"]').value;
                    fetch('comment_post.php', {
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
                            newComment.textContent = data.comment_text;
                            commentsList.appendChild(newComment);
                            commentForm.reset();
                        }
                    });
                }
            }
        }
    </script>
</body>
</html>