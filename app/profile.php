<?php
session_start();
require_once '../database/database.php';
require_once 'DAO/userdao.php';
require_once 'model/user.php';

if (!isset($_SESSION['username'])) {
    header("Location: register.php");
    exit();
}

$loggedInUsername = filter_var($_SESSION['username'], FILTER_SANITIZE_STRING);
$profileUsername = isset($_GET['username']) ? filter_var($_GET['username'], FILTER_SANITIZE_STRING) : $loggedInUsername;

$db = new Database();
$conn = $db->connect();
$user = null;
$userDAO = null;
$followerCount = 0;
$followingCount = 0;
$profile_error = '';
$isOwnProfile = ($loggedInUsername === $profileUsername);
$isFollowing = false;

if ($conn) {
    $userDAO = new UserDAO($conn);
    $user = $userDAO->getUserByUsername($profileUsername);
    
    if ($user) {
        $followerCount = $userDAO->getFollowerCount($user->getUserId());
        $followingCount = $userDAO->getFollowingCount($user->getUserId());
        
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
            $result = $userDAO->followUser($loggedInUserId, $profileUserId);
            echo json_encode(['success' => $result, 'isFollowing' => true]);
            exit;
        } elseif ($_POST['action'] == 'unfollow') {
            $result = $userDAO->unfollowUser($loggedInUserId, $profileUserId);
            echo json_encode(['success' => $result, 'isFollowing' => false]);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile: <?php echo htmlspecialchars($profileUsername); ?></title>
    <link rel="stylesheet" href="../public/css/profile.css">
</head>
<body>
    <div class="theme-toggle">
        <label class="switch">
            <input type="checkbox" id="themeSwitch">
            <span class="slider"></span>
        </label>
    </div>
    <div class="container">
        <?php if ($profile_error): ?>
            <p class="error-message"><?php echo htmlspecialchars($profile_error); ?></p>
        <?php elseif ($user): ?>
            <div class="profile-header">
                <h1><?php echo htmlspecialchars($user->getUsername()); ?></h1>
                <div class="username-handle">@<?php echo htmlspecialchars(strtolower($user->getUsername())); ?></div>
            </div>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; padding: 0 20px;">
                <?php if ($user->getProfilePicture()): ?>
                    <img src="<?php echo htmlspecialchars($user->getProfilePicture()); ?>" class="profile-avatar" alt="Profile Picture">
                <?php else: ?>
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr(htmlspecialchars($user->getUsername()), 0, 1)); ?>
                    </div>
                <?php endif; ?>
                <div class="follow-button-container">
                    <?php if (!$isOwnProfile): ?>
                        <button class="follow-button <?php echo $isFollowing ? 'following' : ''; ?>" data-user-id="<?php echo $user->getUserId(); ?>">
                            <?php echo $isFollowing ? 'Following' : 'Follow'; ?>
                        </button>
                    <?php else: ?>
                        <a href="edit_profile.php" class="edit-profile-button">Edit Profile</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="profile-info">
                <div class="display-name"><?php echo htmlspecialchars($user->getUsername()); ?></div>
                <p class="bio"><?php echo htmlspecialchars($user->getBio() ?: 'No bio yet.'); ?></p>
                <p class="joined">Joined: <?php echo date("M Y", strtotime($user->getCreatedAt())); ?></p>
            </div>
            <div class="profile-stats">
                <div><a href="view/following.php?username=<?php echo htmlspecialchars($user->getUsername()); ?>"><span><?php echo $followingCount; ?></span> Following</a></div>
                <div><a href="view/followers.php?username=<?php echo htmlspecialchars($user->getUsername()); ?>"><span><?php echo $followerCount; ?></span> Followers</a></div>
            </div>
            <div class="posts-section">
                <h3>Posts</h3>
                <p style="text-align:center; color: #777;">Posts are not available in this version.</p>
            </div>
        <?php else: ?>
            <p class="error-message">User profile could not be loaded.</p>
        <?php endif; ?>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Theme toggle logic
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

            // Follow/Unfollow logic
            const followButton = document.querySelector('.follow-button');
            if (followButton) {
                followButton.addEventListener('click', function() {
                    const userId = this.getAttribute('data-user-id');
                    const action = this.classList.contains('following') ? 'unfollow' : 'follow';
                    fetch('Profile.php?username=<?php echo htmlspecialchars($profileUsername); ?>', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                        body: 'action=' + action
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            followButton.classList.toggle('following', data.isFollowing);
                            followButton.textContent = data.isFollowing ? 'Following' : 'Follow';
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });
            }
        });
    </script>
</body>
</html>
?>