<?php
session_start(); 
require_once 'database.php'; 
require_once 'userdao.php'; 
require_once 'User.php'; // Add this line to include User class

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

        // Create a User object first
        $user = new User(null, $username, $email, password_hash($password, PASSWORD_DEFAULT), $bio);
        
        // Pass the User object to insertUser
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
<html>
    <head>
        <title>Register</title>
    </head>
    <body>
        <h1>Register</h1>
        <?php
        if ($registration_error) {
            echo "<h2>" . htmlspecialchars($registration_error) . "</h2>";
        }
        ?>
        <form action="register.php" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"><br><br>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"><br><br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br><br>
            <input type="submit" value="Register">
        </form>
    </body>
</html>