<?php
session_start();
require 'db.php';
$pdo = connectDB();
if (!isset($_SESSION["customer_id"])) {
    header("Location: login.php");
    exit;
}
if (isset($_POST["change"])) {
    $new = hash('sha256', $_POST["new_password"]);
    $stmt = $pdo->prepare("
        UPDATE customer
        SET password_hash = ?
        WHERE customer_id = ?
    ");
    $stmt->execute([$new, $_SESSION["customer_id"]]);
    echo "Password updated.";
}
?>
<form method="POST">
    New Password: <input name="new_password" type="password" required><br>
    <button type="submit" name="change">Change Password</button>
</form>
<hr>
<a href="main.php">← Back to Store</a> |
<a href="cust_orders.php">My Orders</a> |
<a href="logout.php">Logout</a>
