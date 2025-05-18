<?php

session_start();
require_once 'database.php';
require_once 'userdao.php';
require_once 'User.php';

$registration_error = null;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $db = new database();
    $conn = $db->connect();
    if ($conn) {
        $userDAO = new userdao($conn);
        $bio = "This is a default bio.";
        $user = new User(null, $username, $email, password_hash($password, PASSWORD_DEFAULT), $bio);
        if ($userDAO->insertUser($user)) {
            $_SESSION['username'] = $username;
            header("Location: feed.php");
            exit();
        } else {
            $registration_error = "Error during registration. Please try again.";
        }
    } else {
        $registration_error = "Failed to connect to the database.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
   <link rel="stylesheet" href="/webprojectcs1/Pay-Per-View/public/css/login.css">
</head>
<body>

<div class="theme-toggle">
    <label class="switch">
        <input type="checkbox" id="themeSwitcher">
        <span class="slider"></span>
    </label>
</div>

<div class="auth-container">
    <div class="auth-box">
        <h2>Create Account</h2>
        <p class="subtitle">Sign up to get started</p>

        <?php if ($registration_error): ?>
            <div class="error"><?php echo htmlspecialchars($registration_error); ?></div>
        <?php endif; ?>

        <form action="register.php" method="post" autocomplete="off">
            <div class="form-group">
                <label for="username">USERNAME</label>
                <input type="text" id="username" name="username" required maxlength="32"
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="email">EMAIL</label>
                <input type="email" id="email" name="email" required maxlength="64"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="password">PASSWORD</label>
                <input type="password" id="password" name="password" required minlength="6" maxlength="64">
            </div>
            <button type="submit" class="btn-primary">Register</button>
        </form>

        <div class="bottom-link">
            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
</div>

<script>
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
</script>

</body>
</html>