<?php
require_once 'database.php';
require_once 'User.php';
require_once 'Userdao.php';
require_once 'Postdao.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: register.php");
    exit();
}
else{
    $username = $_SESSION['username'];
    echo "<h1>Welcome, $username!</h1>";
    echo "<h2>Feed</h2>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        h1, h2 {
            color: #333;
        }
        .post {
            background-color: #fff;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <h1>Feed</h1>
    <button onclick="window.location.href='profile.php'">Profile</button>
    <button onclick="window.location.href='logout.php'">Logout</button>
</body>
</html>