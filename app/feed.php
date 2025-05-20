
<?php
require_once '../database/database.php';
require_once 'model/user.php';
require_once 'DAO/userdao.php';
require_once 'controller/PostController.php';
require_once 'components/feed.php';
session_start();
$message = "";
if (isset($_GET['success']) && $_GET['success'] == 'post_created') {
    $message = "Post created successfully!";
} else if (isset($_GET['error'])) {
    switch($_GET['error']) {
        case 'missing_data':
            $message = "Error: Required data is missing.";
            break;
        case 'empty_post':
            $message = "Error: Post cannot be empty.";
            break;
        case 'user_not_found':
            $message = "Error: User not found.";
            break;
        case 'post_failed':
            $message = "Error: Failed to create post.";
            break;
        case 'invalid_file_type':
            $message = "Error: Only images and videos are allowed.";
            break;
        case 'file_too_large':
            $message = "Error: File size exceeds the limit (10MB).";
            break;
        case 'upload_failed':
            $message = "Error: Failed to upload media. Please try again.";
            break;
    }
}
if (!isset($_SESSION['username'])) {
    header("Location: register.php");
    exit();
}

$username = $_SESSION['username'];

$db = new Database();
$conn = $db->connect();
$postController = new PostController($conn);
$userDAO = new Userdao($conn);

$userObj = $userDAO->getUserByUsername($username);
$userId = $userObj ? $userObj->getUserId() : 0;
$posts = $postController->getFollowingPosts($userId);
$followingCount = $userDAO->getFollowingCount($userId);
$followerCount = $userDAO->getFollowerCount($userId);
$bio = $userObj ? $userObj->getBio() : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Feed</title>
    <link rel="stylesheet" href="../public/css/feed.css">
    <script defer src="../public/js/feed.js"></script>
</head>
<body>
<div class="main-container">
    <div class="profile-container">
        <div class="profile-pic"><?php echo strtolower(substr($username, 0, 1)); ?></div>
        <div class="profile-name">
            <a href="profile.php?user=<?php echo urlencode($username); ?>" class="profile-link">
                <?php echo $username; ?>
            </a>
        </div>
        <div class="profile-handle">
            <a href="profile.php?user=<?php echo urlencode($username); ?>" class="profile-link">
                @<?php echo strtolower($username); ?>
            </a>
        </div>
        <div class="profile-bio"><?php echo $bio; ?></div>
        <div class="profile-stats">
            <div><span><?php echo $followingCount; ?></span> Following</div>
            <div><span><?php echo $followerCount; ?></span> Followers</div>
        </div>
        <button onclick="location.href='profile.php'">Profile</button>
        <button onclick="location.href='logout.php'">Logout</button>
    </div>

    <div class="main-content">
        <div class="search-bar-container">
            <form action="search.php" method="get">
                <input type="text" name="query" placeholder="Search users..." class="search-input">
                <button type="submit" class="search-button">Search</button>
            </form>
        </div>

        <div class="create-post-container">
            <form action="../app/controller/create_post_controller.php" method="post" enctype="multipart/form-data">
                <textarea name="caption" maxlength="280" placeholder="What's on your mind?" required></textarea>
                <div class="media-upload">
                    <label for="media-file" class="media-label">
                        <span class="media-icon">📷</span>
                        <span>Add photo/video</span>
                    </label>
                    <input type="file" name="media" id="media-file" accept="image/*,video/*" style="display:none">
                    <div id="media-preview" class="media-preview"></div>
                </div>
                <button type="submit">Post</button>
            </form>
            <?php if (!empty($message)): ?>
                <div class="post-message"><?php echo $message; ?></div>
            <?php endif; ?>
        </div>

        <?php renderFeed($posts, $postController, $userId); ?>
    </div>
</div>

<div id="commentsModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeModal">&times;</span>
        <div id="commentsBody">
            <!-- Comments will be loaded here -->
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
</body>
</html>