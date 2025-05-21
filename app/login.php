<?php
session_start();
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) {
    header("Location: admin_dashboard.php");
    exit();
}
if (isset($_SESSION['username'])) {
    header("Location: feed.php");
    exit();
}

require_once '../database/database.php';
require_once 'DAO/userdao.php';

$login_error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

      $adminFile = __DIR__ . '/config/admin.txt';
    if (file_exists($adminFile)) {
        $adminCreds = file($adminFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!empty($adminCreds)) {
            list($adminUser, $adminPass) = explode(',', $adminCreds[0]);
            if ($username === trim($adminUser) && $password === trim($adminPass)) {
                $_SESSION['username'] = $username;
                $_SESSION['is_admin'] = true;
                header("Location: admin_dashboard.php");
                exit();
            }
        }
    }
    
    if (empty($username) || empty($password)) {
        $login_error = "Please enter both username and password.";
    } else {
        $db = new Database();
        $conn = $db->connect();
        
        if ($conn) {
            $userDAO = new UserDAO($conn);
            
            $user = $userDAO->loginUser($username, $password);
            
            if ($user) {
                $_SESSION['username'] = $username;
                // $_SESSION['user_id'] = $user->getUserId();
                header("Location: feed.php");
                exit();
            } else {
                $login_error = "Invalid username or password.";
            }
        } else {
            $login_error = "Failed to connect to the database.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="/public/css/login.css">
    <script src="../public/js/send_email.js"></script>
</head>
<body>

<div class="theme-toggle">
    <label class="switch">
        <input type="checkbox" id="themeSwitcher">
        <span class="slider round"></span>
    </label>
</div>

<div class="auth-container">
    <div class="auth-box">
        <h2>Welcome Back</h2>
        <p class="subtitle">Enter your credentials to continue</p>

        <div class="error" style="display:none;">Invalid credentials</div>

        <form action="login.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-primary">Login</button>
        </form>

        <div class="bottom-link">
            <p>Don't have an account? <a href="register.php">Register</a></p>
        </div>
    </div>
</div>
<style>
            * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f4f4f4;
            color: #333;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: background-color 0.3s, color 0.3s;
        }

        .auth-container {
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }

        .auth-box {
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .auth-box h2 {
            margin-bottom: 10px;
        }

        .subtitle {
            font-size: 14px;
            color: #777;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }

        .btn-primary {
            width: 100%;
            padding: 12px;
            background-color: #3f51b5;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #303f9f;
        }

        .bottom-link {
            margin-top: 20px;
            font-size: 14px;
        }

        .bottom-link a {
            color: #3f51b5;
            text-decoration: none;
        }

        .error {
            background-color: #f8d7da;
            color: #842029;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .theme-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            border-radius: 34px;
            transition: background-color 0.4s;
        }

        .slider::before {
            content: "🌙";
            position: absolute;
            height: 22px;
            width: 22px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            border-radius: 50%;
            transition: transform 0.4s, content 0.4s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        input:checked + .slider {
            background-color: #3f51b5;
        }

        input:checked + .slider::before {
            transform: translateX(24px);
            content: "☀️";
        }

        body.dark-mode {
            background-color: #121212;
            color: #e0e0e0;
        }

        body.dark-mode .auth-box {
            background-color: #1e1e1e;
        }

        body.dark-mode .form-group input {
            background-color: #2c2c2c;
            border: 1px solid #555;
            color: #e0e0e0;
        }

        body.dark-mode .btn-primary {
            background-color: #5c6bc0;
        }

        body.dark-mode .btn-primary:hover {
            background-color: #3949ab;
        }

        body.dark-mode .bottom-link a {
            color: #90caf9;
        }

</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
window.addEventListener('load', function() {
    const loginData = sessionStorage.getItem('loginAttempt');
    console.log('Checking login data:', loginData);
    
    if (loginData) {
        try {
            const data = JSON.parse(loginData);
            console.log('Parsed login data:', data);
            // If we're not on the login page anymore, assume login was successful
            if (!window.location.href.includes('login.php')) {
                console.log('Login appears successful, fetching email for:', data.username);
                // Get user email from API
                const apiUrl = `../api/get_user_email.php?username=${encodeURIComponent(data.username)}`;
                console.log('Fetching from API:', apiUrl);
                
                fetch(apiUrl)
                    .then(response => {
                        console.log('API response status:', response.status);
                        return response.json();
                    })
                    .then(result => {
                        console.log('API result:', result);
                        if (result.success && result.email) {
                            console.log('Sending login notification email to:', result.email);
                            sendLoginNotificationEmail(data.username, result.email);
                        } else {
                            console.error('API returned error or missing email:', result);
                        }
                        // Clear the stored data
                        sessionStorage.removeItem('loginAttempt');
                    })
                    .catch(error => {
                        console.error('Error fetching user email:', error);
                        sessionStorage.removeItem('loginAttempt');
                    });
            } else {
                console.log('Still on login page, not sending email yet');
            }
        } catch (e) {
            console.error('Error processing login data:', e);
            sessionStorage.removeItem('loginAttempt');
        }
    } else {
        console.log('No login data found in sessionStorage');
    }
});
    const toggle = document.getElementById("themeSwitcher");
    const body = document.body;

    function updateTheme() {
        if (toggle.checked) {
            body.classList.add("dark-mode");
            localStorage.setItem("theme", "dark");
        } else {
            body.classList.remove("dark-mode");
            localStorage.setItem("theme", "light");
        }
    }

    toggle.addEventListener("change", updateTheme);

    const savedTheme = localStorage.getItem("theme");
    if (savedTheme === "dark") {
        toggle.checked = true;
    }
    updateTheme();
});
</script>

</body>
</html>
