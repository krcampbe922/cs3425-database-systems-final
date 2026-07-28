<?php
session_start();
require 'db.php';
$pdo = connectDB();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
$username = $_SESSION['username'];
$firstname = $_SESSION['firstname'];
// Get customer_id
$stmt = $pdo->prepare("SELECT customer_id FROM customer WHERE username = ?");
$stmt->execute([$username]);
$customer_id = $stmt->fetchColumn();
?>
<h2><?= htmlspecialchars($firstname) ?>'s Previous Orders</h2>
<p><a href="main.php">← Back to Shopping</a></p>
<?php
$stmt = $pdo->prepare("
    SELECT *
    FROM purchase
    WHERE customer_id = ?
    ORDER BY order_date DESC
");
$stmt->execute([$customer_id]);
$orders = $stmt->fetchAll();
if (count($orders) === 0): ?>
    <p>You haven't placed any orders yet.</p>
<?php else: ?>
    <?php foreach ($orders as $order): ?>
        <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
            <strong>Order ID:</strong> <?= $order['order_id'] ?><br>
            <strong>Date:</strong> <?= $order['order_date'] ?><br>
            <strong>Status:</strong> <?= $order['order_status'] ?><br>
            <strong>Total:</strong> $<?= $order['total_dollars'] ?><br><br>
            <table border="1" width="100%">
                <tr>
                    <th>Product ID</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
                <?php
                $stmtItems = $pdo->prepare("
                    SELECT
                        oi.product_id,
                        p.name,
                        oi.quantity,
                        oi.price_at_order_time
                    FROM orderitem oi
                    JOIN product p ON oi.product_id = p.product_id
                    WHERE oi.order_id = ?
                ");
                $stmtItems->execute([$order['order_id']]);
                $items = $stmtItems->fetchAll();
                foreach ($items as $item):
                    $subtotal = $item['quantity'] * $item['price_at_order_time'];
                ?>
                    <tr>
                        <td><?= $item['product_id'] ?></td>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td>$<?= number_format($item['price_at_order_time'], 2) ?></td>
                        <td>$<?= number_format($subtotal, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
