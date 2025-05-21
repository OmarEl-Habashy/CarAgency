<?php
session_start();
require_once '../database/database.php';
require_once 'model/user.php';
require_once 'DAO/userdao.php';

if(!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get current user data
$db = new Database();
$conn = $db->connect();
$userDAO = new UserDAO($conn);
$currentUser = $userDAO->getUserByUsername($_SESSION['username']);
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_SESSION['username'];
    $new_username = $_POST['username'];
    $new_bio = $_POST['bio'];

    if (empty($new_username)) {
        $error_message = "Username cannot be empty.";
    } else {
        if ($conn) {
            try {
                $user = $userDAO->getUserByUsername($username);
                if ($user) {
                    // Update user information
                    $user->setUsername($new_username);
                    $user->setBio($new_bio);
                    
                    if ($userDAO->updateUser($user)) {
                        // Update session with new username if changed
                        if ($new_username !== $username) {
                            $_SESSION['username'] = $new_username;
                        }
                        header("Location: profile.php");
                        exit();
                    } else {
                        $error_message = "Failed to update profile.";
                    }
                } else {
                    $error_message = "User not found.";
                }
            } catch (InvalidArgumentException $e) {
                $error_message = $e->getMessage();
            }
        } else {
            $error_message = "Failed to connect to the database.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="../public/css/editprofile.css">
</head>
<body>
    <div class="container">
        <a href="profile.php" class="back-to-profile">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                <path d="M20 11H7.414l4.293-4.293a1 1 0 00-1.414-1.414l-6 6a1 1 0 000 1.414l6 6a1 1 0 001.414-1.414L7.414 13H20a1 1 0 100-2z"></path>
            </svg>
            Back to Profile
        </a>
        
        <h2>Edit Profile</h2>
        
        <?php if (!empty($error_message)): ?>
            <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <form action="edit_profile.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" 
                       value="<?php echo htmlspecialchars($currentUser->getUsername()); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="bio">Bio</label>
                <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($currentUser->getBio() ?? ''); ?></textarea>
            </div>
            
            <div class="button-container">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" class="btn btn-secondary" onclick="location.href='profile.php'">Cancel</button>
            </div>
        </form>
    </div>
</body>
</html>