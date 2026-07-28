<?php
session_start();
require 'db.php';
$pdo = connectDB();
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $username = $_POST["username"];
    $email = $_POST["email"];
    $address = $_POST["address"];
    $password = hash('sha256', $_POST["password"]);
    try {
        $stmt = $pdo->prepare("
            INSERT INTO customer
            (username, password_hash, first_name, last_name, email, shipping_address)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $username,
            $password,
            $first_name,
            $last_name,
            $email,
            $address
        ]);
        header("Location: login.php?registered=1");
        exit;
    } catch (PDOException $e) {
        echo "<p style='color:red;'>Registration failed: " . $e->getMessage() . "</p>";
    }
}
?>
<h2>Register</h2>
<form method="POST">
    Username: <input name="username" required><br>
    First Name: <input name="first_name" required><br>
    Last Name: <input name="last_name" required><br>
    Email: <input name="email" type="email" required><br>
    Shipping Address: <input name="address" required><br>
    Password: <input name="password" type="password" required><br>
    <button type="submit" name="register">Register</button>
</form>
<p>Already have an account? <a href="login.php">Back to login</a></p>
