<?php
session_start();
require_once("db.php");
if (!isset($_SESSION['employee_id'])) {
    header("Location: emp_login.php");
    exit;
}
$dbh = connectDB();
$stmt = $dbh->query("
    SELECT product_id, old_stock, new_stock, action_type, action_time
    FROM product_history
    WHERE old_stock IS NOT NULL
      AND new_stock IS NOT NULL
    ORDER BY action_time DESC
");
echo "<h2>Stock Change History</h2>";
echo "<table border='1'>
<tr>
    <th>Product ID</th>
    <th>Old Stock</th>
    <th>New Stock</th>
    <th>Change</th>
    <th>Action Type</th>
    <th>Timestamp</th>
</tr>";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $change = $row['new_stock'] - $row['old_stock'];
    echo "<tr>
        <td>{$row['product_id']}</td>
        <td>{$row['old_stock']}</td>
        <td>{$row['new_stock']}</td>
        <td>{$change}</td>
        <td>{$row['action_type']}</td>
        <td>{$row['action_time']}</td>
    </tr>";
}
echo "</table>";
?>
<p><a href="employee_main.php">Back to Dashboard</a></p>
