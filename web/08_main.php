<?php
session_start();
require 'db.php';
$pdo = connectDB();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
$firstname = $_SESSION['firstname'];
$customer_id = $_SESSION['customer_id'];

/* =========================
   GET OR CREATE CART
========================= */
$stmt = $pdo->prepare("SELECT cart_id FROM shoppingcart WHERE customer_id = ?");
$stmt->execute([$customer_id]);
$cart_id = $stmt->fetchColumn();
if (!$cart_id) {
    $stmt = $pdo->prepare("INSERT INTO shoppingcart (customer_id) VALUES (?)");
    $stmt->execute([$customer_id]);
    $cart_id = $pdo->lastInsertId();
}

/* =========================
   ADD TO CART
========================= */
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = max(1, (int)$_POST['quantity']);
    $stmt = $pdo->prepare("
        SELECT quantity FROM cartitem
        WHERE cart_id = ? AND product_id = ?
    ");
    $stmt->execute([$cart_id, $product_id]);
    $existing = $stmt->fetchColumn();
    if ($existing !== false) {
        $stmt = $pdo->prepare("
            UPDATE cartitem
            SET quantity = quantity + ?
            WHERE cart_id = ? AND product_id = ?
        ");
        $stmt->execute([$quantity, $cart_id, $product_id]);
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO cartitem (cart_id, product_id, quantity)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$cart_id, $product_id, $quantity]);
    }
    header("Location: main.php");
    exit;
}

/* =========================
   UPDATE CART QUANTITY (FIXED)
========================= */
if (isset($_POST['update_item'])) {
    $product_id = $_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    if ($quantity <= 0) {
        $stmt = $pdo->prepare("
            DELETE FROM cartitem
            WHERE cart_id = ? AND product_id = ?
        ");
        $stmt->execute([$cart_id, $product_id]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE cartitem
            SET quantity = ?
            WHERE cart_id = ? AND product_id = ?
        ");
        $stmt->execute([$quantity, $cart_id, $product_id]);
    }
    header("Location: main.php");
    exit;
}

/* =========================
   REMOVE ITEM
========================= */
if (isset($_POST['remove_item'])) {
    $product_id = $_POST['product_id'];
    $stmt = $pdo->prepare("
        DELETE FROM cartitem
        WHERE cart_id = ? AND product_id = ?
    ");
    $stmt->execute([$cart_id, $product_id]);
    header("Location: main.php");
    exit;
}

/* =========================
   CHECKOUT
========================= */
if (isset($_POST['checkout'])) {
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("
            SELECT ci.product_id, ci.quantity, p.stock_quantity, p.price
            FROM cartitem ci
            JOIN product p ON ci.product_id = p.product_id
            WHERE ci.cart_id = ?
        ");
        $stmt->execute([$cart_id]);
        $items = $stmt->fetchAll();
        foreach ($items as $item) {
            if ($item['quantity'] > $item['stock_quantity']) {
                throw new Exception("Insufficient quantity for '{$item['name']}'. We only have {$item['stock_quantity']} in stock. Please remove items or adjust quantities and try again. ");
            }
        }
        $stmt = $pdo->prepare("
            INSERT INTO purchase (customer_id, order_date, order_status, total_dollars)
            VALUES (?, NOW(), 'PLACED', 0)
        ");
        $stmt->execute([$customer_id]);
        $order_id = $pdo->lastInsertId();
        $total = 0;
        foreach ($items as $item) {
            $stmt = $pdo->prepare("
                INSERT INTO orderitem (order_id, product_id, quantity, price_at_order_time)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $order_id,
                $item['product_id'],
                $item['quantity'],
                $item['price']
            ]);
            $stmt = $pdo->prepare("
                UPDATE product
                SET stock_quantity = stock_quantity - ?
                WHERE product_id = ?
            ");
            $stmt->execute([$item['quantity'], $item['product_id']]);
            $total += $item['price'] * $item['quantity'];
        }
        $stmt = $pdo->prepare("
            UPDATE purchase
            SET total_dollars = ?
            WHERE order_id = ?
        ");
        $stmt->execute([$total, $order_id]);
        $stmt = $pdo->prepare("DELETE FROM cartitem WHERE cart_id = ?");
        $stmt->execute([$cart_id]);
        $pdo->commit();
        header("Location: main.php?success=1");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
$error_message = $e->getMessage(); 
        echo "<p style='color:red;'>Checkout failed: {$e->getMessage()}</p>";
    }
}
?>

<!-- =========================
     HEADER
========================= -->
<h2>Welcome, <?= htmlspecialchars($firstname) ?>!</h2>
# add success message on the page after checkout
<?php if (isset($error_message)): ?>
 <div style="background: #ffeeee; color: #cc0000; padding: 10px; border: 1px solid #cc0000; margin-bottom: 20px;"> 
<strong>Checkout Aborted:</strong> <?= htmlspecialchars($error_message) ?> 
</div> 
<?php endif; ?> 
<?php if (isset($_GET['success_id'])): ?> 
<div style="background: #eeffee; color: #008800; padding: 10px; border: 1px solid #008800; margin-bottom: 20px;"> 
Success! Your order has been placed. <strong>Order Number: #<?= htmlspecialchars($_GET['success_id']) ?></strong> 
</div> 
<?php endif; ?> 
<a href="change_password.php">Change Password</a> |
<a href="logout.php">Logout</a> |
<a href="cust_orders.php">View Orders</a>
<hr>

<!-- =========================
     BROWSE PRODUCTS
========================= -->
<h3>Browse Products</h3>
<form method="POST">
    <select name="category_id" onchange="this.form.submit()">
        <option value="all">View All</option>
        <?php
        $categories = $pdo->query("SELECT * FROM category")->fetchAll();
        foreach ($categories as $cat) {
            $selected = (isset($_POST['category_id']) && $_POST['category_id'] == $cat['category_id']) ? "selected" : "";
            echo "<option value='{$cat['category_id']}' $selected>{$cat['name']}</option>";
        }
        ?>
    </select>
</form>
<?php
$category = $_POST['category_id'] ?? 'all';
if ($category == 'all') {
    $stmt = $pdo->query("
        SELECT product_id, name, description, price, stock_quantity
        FROM product
    ");
} else {
    $stmt = $pdo->prepare("
        SELECT product_id, name, description, price, stock_quantity
        FROM product
        WHERE category_id = ?
    ");
    $stmt->execute([$category]);
}
$products = $stmt->fetchAll();
?>
<table border="1">
<tr>
    <th>Name</th>
    <th>Description</th>
    <th>Price</th>
    <th>Stock</th>
    <th>Quantity</th>
    <th>Action</th>
</tr>
<?php foreach ($products as $p): ?>
<tr>
<form method="POST">
    <td><?= htmlspecialchars($p['name']) ?></td>
    <td><?= htmlspecialchars($p['description']) ?></td>
    <td>$<?= number_format($p['price'], 2) ?></td>
    <td><?= $p['stock_quantity'] ?></td>
    <td>
        <input type="number" name="quantity" value="1" min="1" max="<?= $p['stock_quantity'] ?>">
    </td>
    <td>
        <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
        <button type="submit" name="add_to_cart">Add</button>
    </td>
</form>
</tr>
<?php endforeach; ?>
</table>
<hr>

<!-- =========================
     CART
========================= -->
<h3>Your Cart</h3>
<table border="1">
<tr>
    <th>Product</th>
    <th>Quantity</th>
    <th>Price</th>
    <th>Subtotal</th>
    <th>Action</th>
</tr>
<?php
$stmt = $pdo->prepare("
    SELECT ci.product_id, ci.quantity, p.name, p.price
    FROM cartitem ci
    JOIN product p ON ci.product_id = p.product_id
    WHERE ci.cart_id = ?
");
$stmt->execute([$cart_id]);
$cart = $stmt->fetchAll();
$total = 0;
foreach ($cart as $item):
    $subtotal = $item['quantity'] * $item['price'];
    $total += $subtotal;
?>
<tr>
    <td><?= htmlspecialchars($item['name']) ?></td>
    <td>
        <form method="POST">
            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
            <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="0" style="width:60px;">
            <button type="submit" name="update_item">Update</button>
        </form>
    </td>
    <td>$<?= number_format($item['price'], 2) ?></td>
    <td>$<?= number_format($subtotal, 2) ?></td>
    <td>
        <form method="POST">
            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
            <button type="submit" name="remove_item">Remove</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
<tr>
    <td colspan="3" align="right"><strong>Total:</strong></td>
    <td colspan="2"><strong>$<?= number_format($total, 2) ?></strong></td>
</tr>
</table>
<br>
<form method="POST">
    <button type="submit" name="checkout">Checkout</button>
</form>
