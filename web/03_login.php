<?php
session_start();
require 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $user = authenticate($username, $password);
    if ($user) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['firstname'] = $user['first_name'];
        $_SESSION['customer_id'] = $user['customer_id'];


        header("Location: main.php");
        exit;
    } else {
        echo "<p style='color:red;'>Invalid username or password.</p>";
    }
}
?>
<h2>Login</h2>
<form method="POST">
    Username: <input type="text" name="username" required><br>
    Password: <input type="password" name="password" required><br>
    <input type="submit" value="Login">
</form>
<p>New user? <a href="register.php">Click here to register</a>.</p>
