<?php
session_start();
require_once("db.php");
$pdo = connectDB();
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $hashed_password = hash('sha256', $password);
    try {
        // Get employee by username
        $stmt = $pdo->prepare("
            SELECT *
            FROM employee
            WHERE username = ?
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        // Check if user exists
        if (!$user) {
            echo "User not found.";
            exit;
        }
        // Validate password
        if ($user['password_hash'] !== $hashed_password) {
            echo "Invalid password.";
            exit;
        }
        $_SESSION['employee_id'] = $user['employee_id'];
        $_SESSION['employee_username'] = $user['username'];
        if ($user['is_password_temp'] == 1) {
            header("Location: reset_password.php");
            exit;
        }
        // Normal login
        header("Location: employee_main.php");
        exit;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit;
    }
}
?>
<h2>Employee Login</h2>
<form method="POST">
    Username: <input type="text" name="username" required><br><br>
    Password: <input type="password" name="password" required><br><br>
    <button type="submit">Login</button>
</form>
