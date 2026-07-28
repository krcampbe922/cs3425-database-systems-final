<?php
session_start();
require_once("db.php");
if (!isset($_SESSION['employee_id'])) {
    header("Location: emp_login.php");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $product_id = $_POST['product_id'];
    $new_price = (float) $_POST['new_price'];
    try {
        $dbh = connectDB();
        // Get current price
        $stmt = $dbh->prepare("
            SELECT price
            FROM product
            WHERE product_id = ?
        ");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) {
            echo "Product not found.";
            exit;
        }
        $old_price = $product['price'];
        // Update price
        $update = $dbh->prepare("
            UPDATE product
            SET price = ?
            WHERE product_id = ?
        ");
        $update->execute([$new_price, $product_id]);
        // Log change (matches your schema)
        $log = $dbh->prepare("
            INSERT INTO product_history
            (action_time, action_type, old_price, new_price, product_id)
            VALUES (NOW(), 'PRICE_UPDATE', ?, ?, ?)
        ");
        $log->execute([$old_price, $new_price, $product_id]);
        echo "Price successfully updated.";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
<h2>Change Product Price</h2>
<form method="POST">
    Product ID: <input type="number" name="product_id" required><br>
    New Price: <input type="number" step="0.01" name="new_price" required><br>
    <input type="submit" value="Change Price">
</form>
<p><a href="employee_main.php">Back to Dashboard</a></p>
