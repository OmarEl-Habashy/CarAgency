<?php
session_start();
require_once 'database.php'; // For Database class
require_once 'userdao.php';  // For UserDAO class
require_once 'user.php';     // For User class
require_once 'postdao.php';
if (!isset($_SESSION['username'])) {
    header("Location: register.php"); // Redirect to registration if not logged in
    exit();
}

$loggedInUsername = $_SESSION['username'];
$profileUsername = isset($_GET['username']) ? $_GET['username'] : $loggedInUsername; // View own or other's profile

$db = new database();
$conn = $db->connect();
$user = null;
$userDAO = null;
$followerCount = 0;
$followingCount = 0;
$profile_error = '';
$isOwnProfile = ($loggedInUsername === $profileUsername);
$isFollowing = false; // For follow/unfollow button

if ($conn) {
    $userDAO = new userdao($conn);
    $user = $userDAO->getUserByUsername($profileUsername);
    $posts = [];
    if ($user) {
        $followerCount = $userDAO->getFollowerCount($user->getUserId());
        $followingCount = $userDAO->getFollowingCount($user->getUserId());
        $postDAO = new postdao($conn);
        $posts = $postDAO->getPostsByUserId($user->getUserId());
    
        // If viewing someone else's profile, check if the logged-in user is following them
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


// Handle follow/unfollow actions (basic example)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $user && !$isOwnProfile) {
    $loggedInUser = $userDAO->getUserByUsername($loggedInUsername); // Re-fetch or ensure $loggedInUser is available
    if ($loggedInUser) {
        $loggedInUserId = $loggedInUser->getUserId();
        $profileUserId = $user->getUserId();

        if ($_POST['action'] == 'follow') {
            $userDAO->followUser($loggedInUserId, $profileUserId);
            $isFollowing = true; // Update status
        } elseif ($_POST['action'] == 'unfollow') {
            $userDAO->unfollowUser($loggedInUserId, $profileUserId);
            $isFollowing = false; // Update status
        }
        // Refresh counts after follow/unfollow
        $followerCount = $userDAO->getFollowerCount($profileUserId);
        // No need to redirect for this simple example, page will refresh with updated state
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
        .profile-avatar { width: 100px; height: 100px; border-radius: 50%; border: 3px solid white; margin-top: -50px; margin-left: 20px; background-color: #ccc; display: flex; align-items: center; justify-content: center; font-size: 30px; color: #fff; } /* Simple placeholder */
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
    </style>
</head>
<body>

    <div class="container">
        <?php if ($profile_error): ?>
            <p class="error-message"><?php echo htmlspecialchars($profile_error); ?></p>
        <?php elseif ($user): ?>
            <div class="profile-header">
                <!-- Basic Cover Photo Area -->
            </div>
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
                        <!-- Link to an edit profile page (not implemented here) -->
                        <a href="edit_profile.php" class="edit-profile-button">Edit Profile</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="profile-info">
                <div class="display-name"><?php echo htmlspecialchars($user->getUsername()); ?></div>
                <!-- <div class="username-handle">@<?php echo htmlspecialchars($user->getUsername()); ?></div> -->
                <p class="bio"><?php echo htmlspecialchars($user->getBio() ?: 'No bio yet.'); ?></p>
                <p style="font-size: 0.9em; color: #657786;">Joined: <?php echo date("M Y", strtotime($user->getCreatedAt())); ?></p>
            </div>

            <div class="profile-stats">
                <div><span><?php echo $followingCount; ?></span> Following</div>
                <div><span><?php echo $followerCount; ?></span> Followers</div>
            </div>

            <!-- Placeholder for Tweets/Posts -->
<div style="padding: 20px; border-top: 1px solid #e1e8ed;">
    <h3>Posts</h3>
    <?php if (empty($posts)): ?>
        <p style="text-align:center; color: #657786;">No posts yet.</p>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <div class="post" style="margin-bottom: 20px; padding: 15px; border: 1px solid #e1e8ed; border-radius: 5px;">
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

        <?php else: ?>
            <p class="error-message">User profile could not be loaded.</p>
        <?php endif; ?>
    </div>

</body>
</html>