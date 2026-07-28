<?php
require_once("db.php");
$pdo = connectDB();
// Get categories for dropdown
$categories = $pdo->query("SELECT * FROM category")->fetchAll();
// Determine selection
$selected_category = $_GET['category_id'] ?? '';
?>
<h2>Welcome to IKEA</h2>
<p>
    <a href="login.php">Customer Login</a> |
    <a href="emp_login.php">Employee Login</a>
</p>
<hr>
<h3>Browse Products</h3>
<form method="GET">
    <select name="category_id" onchange="this.form.submit()">
        <option value="">-- View All Categories --</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat['category_id'] ?>"
                <?= ($selected_category == $cat['category_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>
<br>
<?php
// If a category is selected, show only that category
if ($selected_category !== '') {
    $stmt = $pdo->prepare("
        SELECT product_id, name, description, price, stock_quantity
        FROM product
        WHERE category_id = ?
    ");
    $stmt->execute([$selected_category]);
    $products = $stmt->fetchAll();
    echo "<table border='1' width='100%'>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Stock</th>
            </tr>";
    foreach ($products as $p) {
        echo "<tr>
                <td>" . htmlspecialchars($p['name']) . "</td>
                <td>" . htmlspecialchars($p['description']) . "</td>
                <td>$" . number_format($p['price'], 2) . "</td>
                <td>{$p['stock_quantity']}</td>
              </tr>";
    }
    echo "</table>";
} else {
    // View All
    foreach ($categories as $cat) {
        echo "<h4>" . htmlspecialchars($cat['name']) . "</h4>";
        $stmt = $pdo->prepare("
            SELECT product_id, name, description, price, stock_quantity
            FROM product
            WHERE category_id = ?
        ");
        $stmt->execute([$cat['category_id']]);
        $products = $stmt->fetchAll();
        echo "<table border='1' width='100%'>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Stock</th>
                </tr>";
        foreach ($products as $p) {
            echo "<tr>
                    <td>" . htmlspecialchars($p['name']) . "</td>
                    <td>" . htmlspecialchars($p['description']) . "</td>
                    <td>$" . number_format($p['price'], 2) . "</td>
                    <td>{$p['stock_quantity']}</td>
                  </tr>";
        }
        echo "</table><br>";
    }
}
?>
