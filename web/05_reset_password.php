<?php
session_start();
require_once("db.php");
$pdo = connectDB();
if (!isset($_SESSION['employee_id'])) {
    die("Unauthorized");
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_password = $_POST['new_password'];
    $hashed_password = hash('sha256', $new_password);
    $employee_id = $_SESSION['employee_id'];
    try {
        $stmt = $pdo->prepare("
            UPDATE employee
            SET password_hash = ?, is_password_temp = 0
            WHERE employee_id = ?
        ");
        $stmt->execute([$hashed_password, $employee_id]);
        header("Location: employee_main.php");
        exit;
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit;
    }
}
?>
<h2>Reset Password</h2>
<form method="POST">
    New Password: <input type="password" name="new_password" required><br><br>
    <button type="submit">Reset Password</button>
</form>
