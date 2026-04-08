<?php
require_once "../admin_guard.php";
require_once "../db.php";

if (isset($_POST["update_status"])) {
    $order_id = intval($_POST["order_id"]);
    $status = trim($_POST["status"]);

    if (in_array($status, ["Pending", "Completed", "Cancelled"])) {
        $update = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $update->bind_param("si", $status, $order_id);
        $update->execute();
    }

    echo "<script>alert('Order status updated'); window.location.href='manage_orders.php';</script>";
    exit();
}

$sql = "SELECT orders.*, users.full_name AS customer_name
        FROM orders
        JOIN users ON orders.customer_id = users.id
        ORDER BY orders.id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-teal-100 via-emerald-100 to-green-100 p-6">
    <div class="max-w-7xl mx-auto bg-white shadow-2xl rounded-3xl p-8">
        <h1 class="text-3xl font-extrabold text-teal-700 mb-6">Manage Orders</h1>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-teal-200">
                        <th class="p-3 text-left">Order ID</th>
                        <th class="p-3 text-left">Customer</th>
                        <th class="p-3 text-left">Total</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Date</th>
                        <th class="p-3 text-left">Update</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr class="border-b">
                            <td class="p-3"><?php echo $row["id"]; ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($row["customer_name"]); ?></td>
                            <td class="p-3">Rs. <?php echo number_format($row["total_amount"], 2); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($row["status"]); ?></td>
                            <td class="p-3"><?php echo $row["created_at"]; ?></td>
                            <td class="p-3">
                                <form method="POST" class="flex gap-2">
                                    <input type="hidden" name="order_id" value="<?php echo $row["id"]; ?>">
                                    <select name="status" class="border rounded-lg px-2 py-1">
                                        <option value="Pending" <?php if ($row["status"] == "Pending") echo "selected"; ?>>Pending</option>
                                        <option value="Completed" <?php if ($row["status"] == "Completed") echo "selected"; ?>>Completed</option>
                                        <option value="Cancelled" <?php if ($row["status"] == "Cancelled") echo "selected"; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_status" class="bg-blue-500 text-white px-3 py-1 rounded-lg">Save</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <a href="dashboard.php" class="text-blue-600 font-semibold">← Back to Admin Dashboard</a>
        </div>
    </div>
</body>
</html>