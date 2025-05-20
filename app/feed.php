<?php
require_once '../database/database.php';
require_once 'model/user.php';
require_once 'DAO/userdao.php';
require_once 'controller/PostController.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: register.php");
    exit();
}

$username = $_SESSION['username'];
$db = new Database();
$conn = $db->connect();
$userDAO = new Userdao($conn);
$postController = new PostController($conn);

$userObj = $userDAO->getUserByUsername($username);
$userId = $userObj ? $userObj->getUserId() : 0;
$bio = $userObj ? $userObj->getBio() : '';
$followingCount = $userDAO->getFollowingCount($userId);
$followerCount = $userDAO->getFollowerCount($userId);

$message = "";
if (isset($_GET['success']) && $_GET['success'] == 'post_created') {
    $message = '<div class="post-message success">Post created successfully!</div>';
} else if (isset($_GET['error'])) {
    $errorMessages = [
        'missing_data' => '<div class="post-message error">Error: Required data is missing.</div>',
        'empty_post' => '<div class="post-message error">Error: Post cannot be empty.</div>',
        'user_not_found' => '<div class="post-message error">Error: User not found.</div>',
        'post_failed' => '<div class="post-message error">Error: Failed to create post.</div>',
        'invalid_file_type' => '<div class="post-message error">Error: Only images and videos are allowed.</div>',
        'file_too_large' => '<div class="post-message error">Error: File size exceeds the limit (10MB).</div>',
        'upload_failed' => '<div class="post-message error">Error: Failed to upload media. Please try again.</div>'
    ];
    $message = $errorMessages[$_GET['error']] ?? '';
}

// --- Like & Comment logic (session-based for demo) ---
if (!isset($_SESSION['likes'])) $_SESSION['likes'] = [];
if (!isset($_SESSION['user_likes'])) $_SESSION['user_likes'] = [];
if (!isset($_SESSION['comments'])) $_SESSION['comments'] = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Like (toggle per user per post)
    if (isset($_POST['like_post_id'])) {
        $pid = $_POST['like_post_id'];
        $user = $username;
        if (!isset($_SESSION['user_likes'][$pid])) $_SESSION['user_likes'][$pid] = [];
        if (!isset($_SESSION['likes'][$pid])) $_SESSION['likes'][$pid] = 0;
        if (!in_array($user, $_SESSION['user_likes'][$pid])) {
            $_SESSION['user_likes'][$pid][] = $user;
            $_SESSION['likes'][$pid]++;
            $liked = true;
        } else {
            $_SESSION['user_likes'][$pid] = array_diff($_SESSION['user_likes'][$pid], [$user]);
            $_SESSION['likes'][$pid] = max(0, $_SESSION['likes'][$pid] - 1);
            $liked = false;
        }
        echo json_encode(['count' => $_SESSION['likes'][$pid], 'liked' => $liked]);
        exit;
    }
    // Comment
    if (isset($_POST['comment_post_id'], $_POST['comment_text'])) {
        $pid = $_POST['comment_post_id'];
        $text = trim($_POST['comment_text']);
        if ($text !== '') {
            if (!isset($_SESSION['comments'][$pid])) $_SESSION['comments'][$pid] = [];
            $_SESSION['comments'][$pid][] = ['user' => $username, 'text' => $text];
        }
        echo json_encode($_SESSION['comments'][$pid]);
        exit;
    }
}

$postId = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;
if ($postId > 0) {
    $singlePost = $postController->getPostById($postId);
    $posts = $singlePost ? [$singlePost] : [];
} else {
    $posts = $postController->getFollowingPosts($userId);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Feed</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/css/feed.css">
</head>
<body>
<div class="layout">
    <!-- Left Sidebar -->
    <div class="sidebar left">
        <h3 style="margin-bottom:18px;">Friends</h3>
        <a href="#" class="profile-link" style="margin-bottom:10px;">Find Friends</a>
        <a href="#" class="profile-link" style="margin-bottom:10px;">Friend Requests</a>
        <a href="#" class="profile-link" style="margin-bottom:10px;">Groups</a>
        <a href="#" class="profile-link" style="margin-bottom:10px;">Messages</a>
        <a href="#" class="profile-link" style="margin-bottom:10px;">Explore</a>
    </div>
    <!-- Main Feed -->
    <div class="main-feed">
        <div class="theme-toggle">
            <label class="switch">
                <input type="checkbox" id="themeSwitch">
                <span class="slider"></span>
            </label>
        </div>
        <div class="search-bar-container">
            <form action="search.php" method="get" style="width:100%;display:flex;">
                <input type="text" name="query" placeholder="Search users..." class="search-input">
                <button type="submit" class="search-button" title="Search">
                    <svg viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7" stroke="white" stroke-width="2" fill="none"/>
                        <line x1="16.5" y1="16.5" x2="21" y2="21" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </form>
        </div>
        <div class="create-post-container">
            <div class="create-post-header">
                <div class="create-post-avatar"><?php echo strtoupper(substr($username, 0, 1)); ?></div>
                <span style="font-weight:bold;"><?php echo htmlspecialchars($username); ?></span>
            </div>
            <form action="../app/controller/create_post_controller.php" method="post" enctype="multipart/form-data" style="width:100%;">
                <textarea name="caption" maxlength="280" placeholder="What's on your mind?" required></textarea>
                <div class="create-post-actions">
                    <div class="media-upload">
                        <label for="media-file" class="media-label">
                            <span class="media-icon">📷</span>
                            <span>Add photo/video</span>
                        </label>
                        <input type="file" name="media" id="media-file" accept="image/*,video/*" style="display:none">
                        <div id="media-preview" class="media-preview"></div>
                    </div>
                    <button type="submit">Post</button>
                </div>
            </form>
            <?php if (!empty($message)): ?>
                <?php echo $message; ?>
            <?php endif; ?>
        </div>
        <?php
        foreach ($posts as $post) {
            if (is_object($post)) {
                $postId = method_exists($post, 'getPostId') ? $post->getPostId() : null;
                $postUserId = method_exists($post, 'getUserId') ? $post->getUserId() : null;
                $postUser = $postUserId ? $userDAO->selectUser($postUserId) : $userObj;
                $postUsername = $postUser ? $postUser->getUsername() : $username;
                $avatarLetter = strtoupper(substr($postUsername, 0, 1));
                $caption = method_exists($post, 'getCaption') ? $post->getCaption() : '';
                $createdAt = method_exists($post, 'getCreatedAt') ? $post->getCreatedAt() : '';
                $mediaPath = method_exists($post, 'getContentURL') ? $post->getContentURL() : '';
            } else {
                $postId = isset($post['PostID']) ? $post['PostID'] : null;
                $postUser = isset($post['UserID']) ? $userDAO->selectUser($post['UserID']) : $userObj;
                $postUsername = $postUser ? $postUser->getUsername() : $username;
                $avatarLetter = strtoupper(substr($postUsername, 0, 1));
                $caption = isset($post['Caption']) ? $post['Caption'] : '';
                $createdAt = isset($post['CreatedAt']) ? $post['CreatedAt'] : '';
                $mediaPath = isset($post['MediaPath']) ? $post['MediaPath'] : '';
            }
            $likeCount = isset($_SESSION['likes'][$postId]) ? $_SESSION['likes'][$postId] : 0;
            $userLiked = isset($_SESSION['user_likes'][$postId]) && in_array($username, $_SESSION['user_likes'][$postId]);
            $comments = isset($_SESSION['comments'][$postId]) ? $_SESSION['comments'][$postId] : [];
            ?>
            <div class="feed-post" data-postid="<?php echo htmlspecialchars($postId); ?>">
                <div class="post-header">
                    <div class="post-avatar"><?php echo $avatarLetter; ?></div>
                    <a href="profile.php?username=<?php echo urlencode($postUsername); ?>" class="post-user"><?php echo htmlspecialchars($postUsername); ?></a>
                    <span class="post-date"><?php echo htmlspecialchars(date("F j, Y, g:i a", strtotime($createdAt))); ?></span>
                </div>
                <div class="post-caption"><?php echo nl2br(htmlspecialchars($caption)); ?></div>
                <?php if (!empty($mediaPath)): ?>
                    <?php
                    $ext = strtolower(pathinfo($mediaPath, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                        echo '<img src="' . htmlspecialchars($mediaPath) . '" alt="Post Media">';
                    } elseif (in_array($ext, ['mp4', 'webm', 'ogg'])) {
                        echo '<video controls><source src="' . htmlspecialchars($mediaPath) . '" type="video/' . $ext . '">Your browser does not support the video tag.</video>';
                    }
                    ?>
                <?php endif; ?>
                <div class="post-actions">
                    <button class="like-btn<?php echo $userLiked ? ' liked' : ''; ?>" onclick="likePost(<?php echo htmlspecialchars($postId); ?>, this)">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41 0.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" fill="currentColor"/>
                        </svg>
                        Like <span class="like-count"><?php echo $likeCount; ?></span>
                    </button>
                    <button class="comment-btn" onclick="toggleComments(this)">
                        <svg viewBox="0 0 24 24">
                            <path d="M21 6h-2V5c0-1.1-.9-2-2-2H7C5.9 3 5 3.9 5 5v1H3c-1.1 0-2 .9-2 2v9c0 1.1.9 2 2 2h5l4 4v-4h7c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-4 11H9v2.59L6.41 17H3V8h18v9z" fill="currentColor"/>
                        </svg>
                        Comment <span class="comment-count"><?php echo count($comments); ?></span>
                    </button>
                </div>
                <div class="comment-section">
                    <?php foreach ($comments as $c): ?>
                        <div class="comment">
                            <strong><?php echo htmlspecialchars($c['user']); ?>:</strong>
                            <?php echo htmlspecialchars($c['text']); ?>
                        </div>
                    <?php endforeach; ?>
                    <form class="add-comment-form" onsubmit="return addComment(event, <?php echo htmlspecialchars($postId); ?>)">
                        <input type="text" name="comment" placeholder="Write a comment..." required>
                        <button type="submit">Post</button>
                    </form>
                </div>
            </div>
        <?php } ?>
    </div>
    <!-- Right Sidebar -->
    <div class="sidebar right">
        <button class="menu-btn" id="menuBtn" aria-label="Menu">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
        <div class="dropdown-menu" id="dropdownMenu">
            <a href="profile.php">My Profile</a>
            <a href="settings.php">Settings</a>
            <form action="logout.php" method="post" style="margin:0;">
                <button type="submit">Logout</button>
            </form>
        </div>
        <div class="profile-pic"><?php echo strtoupper(substr($username, 0, 1)); ?></div>
        <div class="profile-name">
            <a href="profile.php?user=<?php echo urlencode($username); ?>" class="profile-link">
                <?php echo htmlspecialchars($username); ?>
            </a>
        </div>
        <div class="profile-handle">
            <a href="profile.php?user=<?php echo urlencode($username); ?>" class="profile-link">
                @<?php echo htmlspecialchars(strtolower($username)); ?>
            </a>
        </div>
        <div class="profile-bio"><?php echo htmlspecialchars($bio); ?></div>
        <div class="profile-stats">
            <div><span><?php echo $followingCount; ?></span> Following</div>
            <div><span><?php echo $followerCount; ?></span> Followers</div>
        </div>
    </div>
</div>

<script src="../public/js/send_email.js"></script>

<!-- Then run our email checking code -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Feed page loaded, checking for pending registration...');
    
    // Check for sendWelcomeEmail function to make sure it's loaded
    if (typeof sendWelcomeEmail !== 'function') {
        console.error('sendWelcomeEmail function is not defined! Script may not be loaded correctly.');
    }
    
    function checkPendingRegistration() {
        // Check localStorage for registration data
        const registrationData = localStorage.getItem('pendingRegistration');
        console.log('Found registration data:', registrationData);
        
        if (registrationData) {
            try {
                const data = JSON.parse(registrationData);
                console.log('Processing registration data:', data);
                
                // Make sure the function exists before calling it
                if (typeof sendWelcomeEmail === 'function') {
                    console.log('Recent registration detected, sending welcome email to:', data.email);
                    
                    // Send welcome email
                    sendWelcomeEmail(data.username, data.email);
                    
                    // Show a small notification to the user
                    showEmailNotification(data.email);
                } else {
                    console.error('Cannot send email: sendWelcomeEmail function is not available');
                }
                
                // Clear the registration data
                localStorage.removeItem('pendingRegistration');
                console.log('Registration data cleared');
                
            } catch (e) {
                console.error('Error processing registration data:', e);
                localStorage.removeItem('pendingRegistration');
            }
        } else {
            console.log('No pending registration found');
        }
    }
    
    // Function to show a small notification
    function showEmailNotification(email) {
        // Create notification element
        const notificationDiv = document.createElement('div');
        notificationDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; background-color: #4CAF50; color: white; padding: 15px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.3); z-index: 1000; max-width: 300px;';
        notificationDiv.innerHTML = `<p><strong>Welcome!</strong></p><p>A welcome email has been sent to ${email}</p>`;
        
        // Add notification to page
        document.body.appendChild(notificationDiv);
        
        // Remove notification after 5 seconds
        setTimeout(() => {
            notificationDiv.style.opacity = '0';
            notificationDiv.style.transition = 'opacity 0.5s';
            setTimeout(() => document.body.removeChild(notificationDiv), 500);
        }, 5000);
    }
    
    // Delayed check to ensure scripts are loaded
    setTimeout(checkPendingRegistration, 1000);
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hamburger menu logic
    const menuBtn = document.getElementById('menuBtn');
    const dropdownMenu = document.getElementById('dropdownMenu');
    if (menuBtn && dropdownMenu) {
        menuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });
        document.addEventListener('click', function(e) {
            if (!dropdownMenu.contains(e.target) && !menuBtn.contains(e.target)) {
                dropdownMenu.classList.remove('show');
            }
        });
    }

    // Dark/Light mode toggle logic
    const themeSwitch = document.getElementById('themeSwitch');
    function setTheme(mode) {
        if (mode === 'light') {
            document.body.classList.remove('dark-mode');
            themeSwitch.checked = false;
            localStorage.setItem('theme', 'light');
        } else {
            document.body.classList.add('dark-mode');
            themeSwitch.checked = true;
            localStorage.setItem('theme', 'dark');
        }
    }
    if (themeSwitch) {
        const saved = localStorage.getItem('theme');
        if (saved === 'light') setTheme('light');
        else setTheme('dark');
        themeSwitch.addEventListener('change', function() {
            setTheme(this.checked ? 'dark' : 'light');
        });
    }

    // Media preview for post
    const mediaFile = document.getElementById('media-file');
    if (mediaFile) {
        mediaFile.addEventListener('change', function(e) {
            const preview = document.getElementById('media-preview');
            preview.innerHTML = '';
            const file = e.target.files[0];
            if (!file) return;
            const ext = file.name.split('.').pop().toLowerCase();
            const url = URL.createObjectURL(file);
            if (['jpg','jpeg','png','gif'].includes(ext)) {
                const img = document.createElement('img');
                img.src = url;
                img.style.maxWidth = '100%';
                img.style.maxHeight = '300px';
                img.style.borderRadius = '8px';
                img.style.objectFit = 'cover';
                preview.appendChild(img);
            } else if (['mp4','webm','ogg'].includes(ext)) {
                const video = document.createElement('video');
                video.src = url;
                video.controls = true;
                video.style.maxWidth = '100%';
                video.style.maxHeight = '300px';
                video.style.borderRadius = '8px';
                video.style.objectFit = 'cover';
                preview.appendChild(video);
            }
        });
    }
});

// Like post (AJAX, toggle per user)
function likePost(postId, btn) {
    fetch(window.location.href, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'like_post_id=' + encodeURIComponent(postId)
    })
    .then(response => response.json())
    .then(data => {
        let countSpan = btn.querySelector('.like-count');
        countSpan.textContent = data.count;
        if (data.liked) btn.classList.add('liked');
        else btn.classList.remove('liked');
    })
    .catch(error => console.error('Error:', error));
}

// Toggle comments section
function toggleComments(btn) {
    const post = btn.closest('.feed-post');
    const section = post.querySelector('.comment-section');
    section.style.display = section.style.display === 'none' || section.style.display === '' ? 'block' : 'none';
}

// Add comment (AJAX, session demo)
function addComment(event, postId) {
    event.preventDefault();
    const form = event.target;
    const input = form.querySelector('input[name="comment"]');
    const text = input.value.trim();
    if (!text) return false;
    fetch(window.location.href, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'comment_post_id=' + encodeURIComponent(postId) + '&comment_text=' + encodeURIComponent(text)
    })
    .then(response => response.json())
    .then(comments => {
        const section = form.closest('.comment-section');
        // Remove all .comment divs before form
        Array.from(section.querySelectorAll('.comment')).forEach(e => e.remove());
        // Add all comments
        comments.forEach(c => {
            const newComment = document.createElement('div');
            newComment.className = 'comment';
            newComment.innerHTML = '<strong>' + escapeHtml(c.user) + ':</strong> ' + escapeHtml(c.text);
            section.insertBefore(newComment, form);
        });
        input.value = '';
        // Update comment count
        const post = form.closest('.feed-post');
        const countSpan = post.querySelector('.comment-count');
        countSpan.textContent = comments.length;
    })
    .catch(error => console.error('Error:', error));
    return false;
}

function escapeHtml(text) {
    return text.replace(/[&<>"']/g, function(m) {
        return ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[m];
    });
}
</script>
</body>
</html>