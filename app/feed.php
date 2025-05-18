<?php
require_once 'database.php';
require_once 'User.php';
require_once 'Userdao.php';
require_once 'postdao.php';
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: register.php");
    exit();
}
// else{
//     $username = $_SESSION['username'];
//     echo "<h1>Welcome, $username!</h1>";
//     echo "<h2>Feed</h2>";
// }
$username = $_SESSION['username'];
$feedError = null;
$post = [];
$user = null;
$db = new database();
$conn = $db->connect();

if ($conn) {
    $userDAO = new UserDAO($conn);
    $user = $userDAO->getUserByUsername($username);
    
    if ($user) {
        $userId = $user->getUserId();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['caption'])) {
            $caption = trim($_POST['caption']);
            $contentURL = isset($_POST['contentURL']) ? trim($_POST['contentURL']) : null;
            
            if (!empty($caption)) {
                $postDAO = new postdao($conn);
                $newPost = new Post(null, $userId, $contentURL, $caption);
                
                if ($postDAO->insertPost($newPost)) {
                    $postMessage = "Post created successfully!";
                } else {
                    $postMessage = "Error creating post. Please try again.";
                }
            }
        }
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        $postDAO = new postdao($conn);
        $posts = $postDAO->getFeedPostsForUser($userId, $offset, $limit);
        
        $totalPosts = count($posts); 
    } else {
        $feedError = "User not found.";
    }
} else {
    $feedError = "Database connection failed.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed</title>
    <link rel="stylesheet" href="/Project/public/css/feed.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>ConPay</h1>
            <div>
                <a href="profile.php" class="btn">Profile</a>
                <a href="logout.php" class="btn">Logout</a>
            </div>
        </div>

        <?php if ($feedError): ?>
            <div class="error-message"><?php echo htmlspecialchars($feedError); ?></div>
        <?php else: ?>
            <!-- Profile Section -->
            <div class="profile-section">
                <div class="profile-pic">
                    <?php echo strtoupper(substr($user->getUsername(), 0, 1)); ?>
                </div>
                <div class="profile-details">
                    <div class="profile-name"><?php echo htmlspecialchars($user->getUsername()); ?></div>
                    <div class="profile-handle">@<?php echo htmlspecialchars(strtolower($user->getUsername())); ?></div>
                    <div class="profile-bio"><?php echo htmlspecialchars($user->getBio()); ?></div>
                    <div class="profile-stats">
                        <div><span><?php echo $userDAO->getFollowingCount($user->getUserId()); ?></span> Following</div>
                        <div><span><?php echo $userDAO->getFollowerCount($user->getUserId()); ?></span> Followers</div>
                    </div>
                    <div class="action-buttons">
                        <a href="profile.php" class="btn">View Profile</a>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="search-bar">
                <form action="search.php" method="get" style="width: 100%; display: flex;">
                    <input type="text" name="query" placeholder="Search users...">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>

            <!-- Create Post Form -->
            <div class="create-post">
                <form action="feed.php" method="post">
                    <textarea name="caption" maxlength="280" placeholder="What's on your mind?" required></textarea>
                    <input type="url" name="contentURL" placeholder="Optional: Image/Video URL">
                    <button type="submit" class="btn btn-primary">Post</button>
                </form>
                <?php if (isset($postMessage)): ?>
                    <div class="post-message"><?php echo htmlspecialchars($postMessage); ?></div>
                <?php endif; ?>
            </div>

            <!-- Feed Posts -->
            <div class="feed-posts">
                <?php if (empty($posts)): ?>
                    <div class="post">
                        <p>No posts to show. Follow some users or create your first post!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="post">
                            <div class="post-header">
                                <span class="post-username">@<?php echo htmlspecialchars($post->getUsername()); ?></span>
                            </div>
                            <div class="post-caption"><?php echo htmlspecialchars($post->getCaption()); ?></div>
                            <?php if ($post->getContentURL()): ?>
                                <div class="post-media">
                                    <img src="<?php echo htmlspecialchars($post->getContentURL()); ?>" alt="Post media">
                                </div>
                            <?php endif; ?>
                            <div class="post-meta">
                                <span>Posted: <?php echo date('M j, Y g:i A', strtotime($post->getCreatedAt())); ?></span>
                            </div>
                            <div class="post-actions">
                                <button class="post-action">
                                    ❤️ Like
                                </button>
                                <button class="post-action">
                                    💬 Comment
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>