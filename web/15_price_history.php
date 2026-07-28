<?php
session_start();
require_once("db.php");
if (!isset($_SESSION['employee_id'])) {
    header("Location: emp_login.php");
    exit;
}
$dbh = connectDB();
$stmt = $dbh->query("
    SELECT product_id, old_price, new_price, action_type, action_time
    FROM product_history
    WHERE old_price IS NOT NULL
      AND new_price IS NOT NULL
    ORDER BY action_time DESC
");
echo "<h2>Price Change History</h2>";
echo "<table border='1'>
<tr>
    <th>Product ID</th>
    <th>Old Price</th>
    <th>New Price</th>
    <th>Change (%)</th>
    <th>Action Type</th>
    <th>Timestamp</th>
</tr>";
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $percent = ($row['old_price'] > 0)
        ? (($row['new_price'] - $row['old_price']) / $row['old_price']) * 100
        : 0;
    echo "<tr>
        <td>{$row['product_id']}</td>
        <td>$" . number_format($row['old_price'], 2) . "</td>
        <td>$" . number_format($row['new_price'], 2) . "</td>
        <td>" . round($percent, 2) . "%</td>
        <td>{$row['action_type']}</td>
        <td>{$row['action_time']}</td>
    </tr>";
}
echo "</table>";
?>
<p><a href="employee_main.php">Back to Dashboard</a></p>
