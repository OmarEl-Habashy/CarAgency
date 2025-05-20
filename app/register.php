<?php

session_start();
if (isset($_SESSION['username'])) {
    header("Location: feed.php");
    exit();
}
require_once '../database/database.php';
require_once 'DAO/userdao.php';
require_once 'model/user.php';

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
    <link rel="stylesheet" href="../public/css/login.css">
    <script src="../public/js/send_email.js"></script></head>
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
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.querySelector('form');
    console.log('Registration script loaded');
    
    // When form is submitted, save data before the redirect happens
    registerForm.addEventListener('submit', function(e) {
        console.log('Form submission detected');
        
        // Get form values
        const usernameEl = document.getElementById('username');
        const emailEl = document.getElementById('email');
        
        if (!usernameEl || !emailEl) {
            console.error('Form fields not found:', {
                username: usernameEl,
                email: emailEl
            });
            return; // Let form submit normally
        }
        
        const username = usernameEl.value;
        const email = emailEl.value;
        
        if (!username || !email) {
            console.warn('Username or email is empty, not saving data');
            return; // Let form submit normally
        }
        
        console.log('Saving registration data:', { username, email });
        
        // Save registration data to localStorage (more persistent than sessionStorage)
        localStorage.setItem('pendingRegistration', JSON.stringify({
            username: username,
            email: email,
            timestamp: new Date().toISOString()
        }));
    });
    
    // Check if we were redirected after registration
    // This handles the post-redirect scenario
    function checkForSuccessfulRegistration() {
        console.log('Checking for registration completion...');
        
        // Use localStorage instead of sessionStorage for better persistence
        const registrationData = localStorage.getItem('pendingRegistration');
        console.log('Found registration data:', registrationData);
        
        if (registrationData) {
            try {
                const data = JSON.parse(registrationData);
                
                // If we're on the feed page, registration was successful
                if (window.location.href.includes('feed.php')) {
                    console.log('Success! On feed page after registration');
                    console.log('Sending welcome email to:', data.email);
                    
                    // Send the welcome email
                    sendWelcomeEmail(data.username, data.email);
                    
                    // Clear the stored data after sending email
                    localStorage.removeItem('pendingRegistration');
                    console.log('Registration data cleared');
                } else if (window.location.href.includes('register.php')) {
                    // If we're still on the register page, either:
                    // 1. The user just loaded the page, or
                    // 2. Registration failed
                    console.log('Still on register page, not sending email yet');
                } else {
                    // We're on some other page
                    console.log('On unexpected page:', window.location.href);
                    localStorage.removeItem('pendingRegistration');
                }
            } catch (e) {
                console.error('Error processing registration data:', e);
                localStorage.removeItem('pendingRegistration');
            }
        } else {
            console.log('No pending registration found');
        }
    }
    
    // Run the check when the page loads
    checkForSuccessfulRegistration();
    
    // Theme switcher code
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