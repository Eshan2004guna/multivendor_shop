<?php
require_once "../seller_guard.php";
require_once "../db.php";

$seller_id = $_SESSION["user_id"];

$sql = "SELECT order_items.*, orders.status, orders.created_at, users.full_name AS customer_name
        FROM order_items
        JOIN orders ON order_items.order_id = orders.id
        JOIN users ON orders.customer_id = users.id
        WHERE order_items.seller_id = ?
        ORDER BY orders.id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Orders</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-green-100 via-emerald-100 to-teal-100 p-6">
    <div class="max-w-7xl mx-auto bg-white rounded-3xl shadow-2xl p-8">
        <h1 class="text-3xl font-extrabold text-emerald-700 mb-6">My Product Orders</h1>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-emerald-200">
                        <th class="p-3 text-left">Order ID</th>
                        <th class="p-3 text-left">Customer</th>
                        <th class="p-3 text-left">Product ID</th>
                        <th class="p-3 text-left">Quantity</th>
                        <th class="p-3 text-left">Price</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr class="border-b">
                            <td class="p-3"><?php echo $row["order_id"]; ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($row["customer_name"]); ?></td>
                            <td class="p-3"><?php echo $row["product_id"]; ?></td>
                            <td class="p-3"><?php echo $row["quantity"]; ?></td>
                            <td class="p-3">Rs. <?php echo number_format($row["price"], 2); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($row["status"]); ?></td>
                            <td class="p-3"><?php echo $row["created_at"]; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <a href="../main.php" class="text-blue-600 font-semibold">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>