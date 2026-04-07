<?php
require_once "../admin_guard.php";
require_once "../db.php";

$userCount = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()["total"];
$productCount = $conn->query("SELECT COUNT(*) AS total FROM products")->fetch_assoc()["total"];
$orderCount = $conn->query("SELECT COUNT(*) AS total FROM orders")->fetch_assoc()["total"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-100 via-blue-100 to-purple-100 p-6">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-4xl font-extrabold mb-6 text-indigo-700">Admin Dashboard</h1>

        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-3xl shadow-xl border-l-8 border-pink-500">
                <h2 class="text-xl font-semibold">Total Users</h2>
                <p class="text-3xl font-extrabold mt-2"><?php echo $userCount; ?></p>
            </div>

            <div class="bg-white p-6 rounded-3xl shadow-xl border-l-8 border-green-500">
                <h2 class="text-xl font-semibold">Total Products</h2>
                <p class="text-3xl font-extrabold mt-2"><?php echo $productCount; ?></p>
            </div>

            <div class="bg-white p-6 rounded-3xl shadow-xl border-l-8 border-blue-500">
                <h2 class="text-xl font-semibold">Total Orders</h2>
                <p class="text-3xl font-extrabold mt-2"><?php echo $orderCount; ?></p>
            </div>
        </div>

        <div class="mt-6 flex gap-4 flex-wrap">
            <a href="manage_users.php" class="bg-pink-500 text-white px-4 py-2 rounded-xl font-bold">Manage Users</a>
            <a href="manage_products.php" class="bg-green-600 text-white px-4 py-2 rounded-xl font-bold">Manage Products</a>
            <a href="../main.php" class="bg-slate-700 text-white px-4 py-2 rounded-xl font-bold">Back</a>
        </div>
    </div>
</body>
</html>