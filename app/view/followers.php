<?php
session_start();
require_once '../../database/database.php';
require_once '../DAO/userdao.php';
require_once '../model/user.php';

if (!isset($_SESSION['username'])) {
    header("Location: register.php");
    exit();
}

$loggedInUsername = $_SESSION['username'];
$profileUsername = isset($_GET['username']) ? $_GET['username'] : $loggedInUsername;

$db = new database();
$conn = $db->connect();
$user = null;
$followers = [];
$error_message = '';

if ($conn) {
    $userDAO = new userdao($conn);
    $user = $userDAO->getUserByUsername($profileUsername);
    
    if ($user) {
        $followers = $userDAO->getFollowers($user->getUserId());
    } else {
        $error_message = "User not found.";
    }
} else {
    $error_message = "Failed to connect to the database.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Followers of <?php echo htmlspecialchars($profileUsername); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f5f8fa; color: #14171a; }
        .container { max-width: 600px; margin: 20px auto; background-color: #fff; border: 1px solid #e1e8ed; border-radius: 8px; }
        .header { padding: 15px 20px; border-bottom: 1px solid #e1e8ed; }
        .header h2 { margin: 0; }
        .back-link { margin-top: 10px; display: inline-block; color: #1da1f2; text-decoration: none; }
        .user-list { list-style: none; padding: 0; margin: 0; }
        .user-item { padding: 15px 20px; border-bottom: 1px solid #e1e8ed; display: flex; align-items: center; }
        .user-item:last-child { border-bottom: none; }
        .user-avatar { width: 50px; height: 50px; border-radius: 50%; background-color: #ccc; margin-right: 15px; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #fff; }
        .user-info { flex: 1; }
        .username { font-weight: bold; text-decoration: none; color: #14171a; }
        .username:hover { color: #1da1f2; }
        .error-message { color: red; text-align: center; padding: 20px; }
        .empty-message { text-align: center; padding: 30px; color: #657786; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Followers of <?php echo htmlspecialchars($profileUsername); ?></h2>
            <a href="../profile.php?username=<?php echo htmlspecialchars($profileUsername); ?>" class="back-link">Back to Profile</a>
        </div>
        
        <?php if ($error_message): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php elseif (empty($followers)): ?>
            <p class="empty-message">No followers yet.</p>
        <?php else: ?>
            <ul class="user-list">
                <?php foreach ($followers as $follower): ?>
                    <li class="user-item">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr(htmlspecialchars($follower->getUsername()), 0, 1)); ?>
                        </div>
                        <div class="user-info">
                            <a href="../profile.php?username=<?php echo htmlspecialchars($follower->getUsername()); ?>" class="username">
                                <?php echo htmlspecialchars($follower->getUsername()); ?>
                            </a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>