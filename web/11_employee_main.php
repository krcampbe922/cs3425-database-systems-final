<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
    header("Location: emp_login.php");
    exit;
}
?>
<h2>Welcome, Employee</h2>
<p>
    <a href="logout.php">Logout</a>
</p>
<ul>
    <li><a href="restock_product.php">Restock Product</a></li>
    <li><a href="change_price.php">Change Product Price</a></li>
    <li><a href="stock_history.php">View Stock History</a></li>
    <li><a href="price_history.php">View Price History</a></li>
</ul>
