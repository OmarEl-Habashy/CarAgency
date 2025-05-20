<?php
session_start();
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit();
}

require_once '../database/database.php';
$db = new Database();
$conn = $db->connect();

// Handle delete user
if (isset($_POST['delete_user'])) {
    $userId = intval($_POST['delete_user']);
    $stmt = $conn->prepare("DELETE FROM Users WHERE UserID = ?");
    $stmt->execute([$userId]);
}

// Handle delete post
if (isset($_POST['delete_post'])) {
    $postId = intval($_POST['delete_post']);
    $stmt = $conn->prepare("DELETE FROM Posts WHERE PostID = ?");
    $stmt->execute([$postId]);
}

// Fetch all users
$users = $conn->query("SELECT UserID, Username, Email FROM Users")->fetchAll(PDO::FETCH_ASSOC);
// Fetch all posts
$posts = $conn->query("SELECT PostID, UserID, Caption FROM Posts")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        h2 { margin-top: 40px; }
        table { border-collapse: collapse; width: 80%; margin-bottom: 30px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f4f4f4; }
        form { display: inline; }
        .logout { float: right; }
    </style>
</head>
<body>
    <h1>Admin Dashboard</h1>
    <a href="logout.php" class="logout">Logout</a>

    <h2>Users</h2>
    <table>
        <tr>
            <th>UserID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Action</th>
        </tr>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['UserID']) ?></td>
            <td><?= htmlspecialchars($user['Username']) ?></td>
            <td><?= htmlspecialchars($user['Email']) ?></td>
            <td>
                <form method="post" onsubmit="return confirm('Delete this user?');">
                    <button type="submit" name="delete_user" value="<?= $user['UserID'] ?>">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Posts</h2>
    <table>
        <tr>
            <th>PostID</th>
            <th>UserID</th>
            <th>Caption</th>
            <th>Action</th>
        </tr>
        <?php foreach ($posts as $post): ?>
        <tr>
            <td><?= htmlspecialchars($post['PostID']) ?></td>
            <td><?= htmlspecialchars($post['UserID']) ?></td>
            <td><?= htmlspecialchars($post['Caption']) ?></td>
            <td>
                <form method="post" onsubmit="return confirm('Delete this post?');">
                    <button type="submit" name="delete_post" value="<?= $post['PostID'] ?>">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>