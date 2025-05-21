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

$searchQuery = isset($_GET['query']) ? trim($_GET['query']) : '';
$userResults = [];
$postResults = [];

if ($searchQuery !== '') {
    // Search users by username (case-insensitive, partial match)
    $sql = "SELECT * FROM Users WHERE Username LIKE ?";
    $stmt = $conn->prepare($sql);
    $likeQuery = '%' . $searchQuery . '%';
    $stmt->bindParam(1, $likeQuery);
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $userResults[] = $userDAO->getUserByUsername($row['Username']);
    }

    // Search posts by caption (case-insensitive, partial match)
    $sql = "SELECT * FROM Posts WHERE Caption LIKE ? ORDER BY CreatedAt DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(1, $likeQuery);
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $postResults[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search</title>
    <link rel="stylesheet" href="../public/css/search.css">
</head>
<body>
<div class="main-search-container">
    <div class="search-header">
        <a href="feed.php" class="back-link">&larr; Feed</a>
        <form action="search.php" method="get" class="search-bar-container" autocomplete="off">
            <input type="text" name="query" placeholder="Search users or posts..." class="search-input" value="<?php echo htmlspecialchars($searchQuery); ?>">
            <button type="submit" class="search-icon-btn" title="Search">
                <!-- Search SVG icon -->
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7" stroke="white" stroke-width="2" fill="none"/>
                    <line x1="16.5" y1="16.5" x2="21" y2="21" stroke="white" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </button>
        </form>
        <div class="theme-toggle">
            <label class="switch">
                <input type="checkbox" id="themeSwitch">
                <span class="slider"></span>
            </label>
        </div>
    </div>
    <div class="search-content">
        <h2 style="margin-bottom: 24px;">Search</h2>
        <div class="subtitle" style="margin-bottom: 32px;">Find users or posts</div>
        <?php if ($searchQuery !== ''): ?>
            <div class="search-section">
                <div class="search-title">User Results</div>
                <?php if (count($userResults) > 0): ?>
                    <?php foreach ($userResults as $user): ?>
                        <?php if ($user): ?>
                        <div class="user-result">
                            <strong>
                                <a href="profile.php?username=<?php echo urlencode($user->getUsername()); ?>" style="color:#3f51b5;">
                                    <?php echo htmlspecialchars($user->getUsername()); ?>
                                </a>
                            </strong>
                            <div class="profile-handle">@<?php echo strtolower(htmlspecialchars($user->getUsername())); ?></div>
                            <div class="profile-bio"><?php echo htmlspecialchars($user->getBio()); ?></div>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-results">No users found.</div>
                <?php endif; ?>
            </div>
            <div class="search-section">
                <div class="search-title">Post Results</div>
                <?php if (count($postResults) > 0): ?>
                    <?php foreach ($postResults as $post): ?>
                        <a href="profile.php?username=<?php echo urlencode($userDAO->selectUser($post['UserID'])->getUsername()); ?>&post_id=<?php echo urlencode($post['PostID']); ?>" class="post-link">
                        <div class="post-result">
                            <div class="caption"><?php echo htmlspecialchars($post['Caption']); ?></div>
                            <div class="post-meta">
                                <span>By 
                                    <a href="profile.php?username=<?php echo urlencode($userDAO->selectUser($post['UserID'])->getUsername()); ?>" style="color:#3f51b5;" onclick="event.stopPropagation();">
                                        <?php echo htmlspecialchars($userDAO->selectUser($post['UserID'])->getUsername()); ?>
                                    </a>
                                </span>
                                <span> | <?php echo htmlspecialchars($post['CreatedAt']); ?></span>
                            </div>
                            <?php if (!empty($post['MediaPath'])): ?>
                                <?php
                                    $mediaPath = htmlspecialchars($post['MediaPath']);
                                    $ext = strtolower(pathinfo($mediaPath, PATHINFO_EXTENSION));
                                ?>
                                <?php if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                    <div><img src="<?php echo $mediaPath; ?>" alt="Post Media"></div>
                                <?php elseif (in_array($ext, ['mp4', 'webm', 'ogg'])): ?>
                                    <div>
                                        <video controls>
                                            <source src="<?php echo $mediaPath; ?>" type="video/<?php echo $ext; ?>">
                                            Your browser does not support the video tag.
                                        </video>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-results">No posts found.</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<script>
    // Dark mode toggle logic
    const themeSwitch = document.getElementById('themeSwitch');
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    function setTheme(dark) {
        if (dark) {
            document.body.classList.add('dark-mode');
            themeSwitch.checked = true;
            localStorage.setItem('theme', 'dark');
        } else {
            document.body.classList.remove('dark-mode');
            themeSwitch.checked = false;
            localStorage.setItem('theme', 'light');
        }
    }
    // On load
    (function() {
        const saved = localStorage.getItem('theme');
        if (saved === 'dark' || (!saved && prefersDark)) {
            setTheme(true);
        } else {
            setTheme(false);
        }
    })();
    themeSwitch.addEventListener('change', function() {
        setTheme(this.checked);
    });
</script>
</body>
</html>