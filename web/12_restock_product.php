<?php
session_start();
require_once("db.php");
if (!isset($_SESSION['employee_id'])) {
    header("Location: emp_login.php");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $product_id = $_POST['product_id'];
    $quantity = (int) $_POST['quantity'];
    try {
        $dbh = connectDB();
        // Get current stock
        $stmt = $dbh->prepare("
            SELECT actual_stock
            FROM product
            WHERE product_id = ?
        ");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$product) {
            echo "Product not found.";
            exit;
        }
        $old_stock = $product['actual_stock'];
        $new_stock = $old_stock + $quantity;
        // Update stock
        $update = $dbh->prepare("
            UPDATE product
            SET actual_stock = ?
            WHERE product_id = ?
        ");
        $update->execute([$new_stock, $product_id]);
        // Log history (matches your schema)
        $log = $dbh->prepare("
            INSERT INTO product_history
            (action_time, action_type, old_stock, new_stock, product_id)
            VALUES (NOW(), 'RESTOCK', ?, ?, ?)
        ");
        $log->execute([$old_stock, $new_stock, $product_id]);
        echo "Stock successfully updated.";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
<h2>Restock Product</h2>
<form method="POST">
    Product ID: <input type="number" name="product_id" required><br>
    Quantity to Add: <input type="number" name="quantity" required><br>
    <input type="submit" value="Restock">
</form>
<p><a href="employee_main.php">Back to Dashboard</a></p>
