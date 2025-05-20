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
    <title>Search</title>
    <link rel="stylesheet" href="../public/css/login.css">
    <style>
        html, body { height: 100%; }
        body {
            min-height: 100vh;
            height: 100%;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
            color: #333;
            transition: background-color 0.3s, color 0.3s;
        }
        .main-search-container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: 100vw;
        }
        .search-header {
            background: #fff;
            padding: 24px 0 16px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .search-bar-container {
            display: flex;
            align-items: center;
            width: 100%;
            max-width: 500px;
            margin: 0 16px;
        }
        .search-input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #ccc;
            border-radius: 24px 0 0 24px;
            font-size: 16px;
            outline: none;
            background: #f9f9f9;
            transition: border 0.2s;
        }
        .search-input:focus {
            border: 1.5px solid #3f51b5;
        }
        .search-icon-btn {
            background: #3f51b5;
            border: none;
            border-radius: 0 24px 24px 0;
            padding: 0 20px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s;
        }
        .search-icon-btn:hover {
            background: #303f9f;
        }
        .search-icon-btn svg {
            width: 22px;
            height: 22px;
            fill: #fff;
        }
        .back-link {
            position: absolute;
            left: 24px;
            top: 50%;
            transform: translateY(-50%);
            color: #3f51b5;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
        }
        .theme-toggle {
            position: absolute;
            right: 24px;
            top: 50%;
            transform: translateY(-50%);
        }
        .search-content {
            flex: 1;
            width: 100%;
            max-width: 700px;
            margin: 0 auto;
            padding: 32px 16px 24px 16px;
        }
        .search-section { margin-bottom: 36px; }
        .search-title { font-size: 1.2em; margin-bottom: 12px; font-weight: bold; }
        .user-result, .post-result {
            padding: 18px 20px;
            border-bottom: 1px solid #eee;
            background: #fff;
            border-radius: 10px;
            margin-bottom: 14px;
            transition: background 0.2s;
            box-shadow: 0 1px 4px rgba(0,0,0,0.03);
        }
        .user-result:hover, .post-result:hover {
            background: #e3e8ff;
        }
        .caption { margin: 5px 0; }
        .no-results { color: #888; text-align: center; margin: 20px 0; }
        .profile-handle {
            color: #777;
            font-size: 13px;
        }
        .profile-bio {
            color: #444;
            font-size: 14px;
            margin-top: 4px;
        }
        .post-meta {
            font-size: 13px;
            color: #888;
            margin-bottom: 5px;
        }
        .post-result img, .post-result video {
            margin-top: 8px;
            border-radius: 8px;
            max-width: 100%;
            max-height: 220px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }
        .post-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .post-link:visited {
            color: inherit;
        }
        @media (max-width: 700px) {
            .search-content { max-width: 100%; }
        }
        @media (max-width: 600px) {
            .search-header { padding: 16px 0 10px 0; }
            .search-bar-container { max-width: 100%; }
            .search-content { padding: 18px 4px 10px 4px; }
            .back-link, .theme-toggle { left: 8px; right: 8px; font-size: 14px; }
        }
        /* Dark mode overrides */
        body.dark-mode {
            background-color: #121212;
            color: #e0e0e0;
        }
        body.dark-mode .search-header {
            background: #1e1e1e;
            box-shadow: 0 2px 8px rgba(0,0,0,0.18);
        }
        body.dark-mode .search-content {
            background: transparent;
        }
        body.dark-mode .user-result, 
        body.dark-mode .post-result {
            background: #232323;
            border-bottom: 1px solid #222;
            color: #e0e0e0;
        }
        body.dark-mode .user-result:hover, 
        body.dark-mode .post-result:hover {
            background: #232f4b;
        }
        body.dark-mode .search-input {
            background: #232323;
            border: 1px solid #444;
            color: #e0e0e0;
        }
        body.dark-mode .search-title {
            color: #bfcaff;
        }
        body.dark-mode .profile-handle {
            color: #aaa;
        }
        body.dark-mode .profile-bio {
            color: #bbb;
        }
        body.dark-mode .post-meta {
            color: #aaa;
        }
        body.dark-mode .no-results {
            color: #aaa;
        }
    </style>
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