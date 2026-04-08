<?php
require_once "../customer_guard.php";
require_once "../db.php";

$customer_id = $_SESSION["user_id"];

$sql = "SELECT * FROM orders WHERE customer_id = ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-100 to-pink-100 p-6">
    <div class="max-w-5xl mx-auto bg-white shadow-2xl rounded-3xl p-8">
        <h1 class="text-3xl font-extrabold text-indigo-700 mb-6">My Orders</h1>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-indigo-200">
                        <th class="p-3 text-left">Order ID</th>
                        <th class="p-3 text-left">Total Amount</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr class="border-b">
                            <td class="p-3"><?php echo $row["id"]; ?></td>
                            <td class="p-3">Rs. <?php echo number_format($row["total_amount"], 2); ?></td>
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