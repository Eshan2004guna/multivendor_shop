<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$full_name = $_SESSION["full_name"] ?? "User";
$role = $_SESSION["role"] ?? "";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Dashboard - Eshan HyperMart</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-orange-100 via-pink-100 to-purple-100 min-h-screen">

    <nav class="bg-gradient-to-r from-pink-500 via-purple-500 to-indigo-500 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-extrabold">ESHAN HYPERMART</h1>
                <p class="text-sm text-white/90">Everything You Need, One Place.</p>
            </div>

            <div class="flex items-center gap-4">
                <span class="hidden md:inline text-sm font-medium">
                    Logged in as: <strong><?php echo htmlspecialchars($full_name); ?></strong>
                </span>
                <a href="logout.php" class="bg-red-500 px-4 py-2 rounded-xl font-semibold hover:bg-red-600 transition">
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-6 py-10">
        <div class="bg-white/90 backdrop-blur-md rounded-3xl shadow-xl p-8 mb-8 border border-white/40">
            <h2 class="text-4xl font-extrabold text-slate-800 mb-2">
                Welcome, <?php echo htmlspecialchars($full_name); ?> 👋
            </h2>
            <p class="text-lg text-slate-600">
                Your role:
                <span class="font-bold text-purple-600">
                    <?php echo ucfirst(htmlspecialchars($role)); ?>
                </span>
            </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">

            <?php if ($role === "seller") { ?>
                <a href="seller/add_product.php" class="rounded-3xl p-6 text-white shadow-xl bg-gradient-to-r from-green-400 to-emerald-600 hover:scale-105 transition">
                    <h3 class="text-2xl font-bold mb-2">Add Product</h3>
                    <p>Add a new product to your colorful store.</p>
                </a>

                <a href="seller/manage_products.php" class="rounded-3xl p-6 text-white shadow-xl bg-gradient-to-r from-yellow-400 to-orange-500 hover:scale-105 transition">
                    <h3 class="text-2xl font-bold mb-2">Manage Products</h3>
                    <p>View and manage your product list.</p>
                </a>
            <?php } ?>

            <?php if ($role === "customer") { ?>
                <a href="customer/products.php" class="rounded-3xl p-6 text-white shadow-xl bg-gradient-to-r from-blue-500 to-cyan-500 hover:scale-105 transition">
                    <h3 class="text-2xl font-bold mb-2">View Products</h3>
                    <p>Browse products from different sellers.</p>
                </a>

                <a href="customer/cart.php" class="rounded-3xl p-6 text-white shadow-xl bg-gradient-to-r from-purple-500 to-pink-500 hover:scale-105 transition">
                    <h3 class="text-2xl font-bold mb-2">My Cart</h3>
                    <p>See the items added to your cart.</p>
                </a>

                <a href="customer/checkout.php" class="rounded-3xl p-6 text-white shadow-xl bg-gradient-to-r from-emerald-500 to-green-600 hover:scale-105 transition">
                    <h3 class="text-2xl font-bold mb-2">Checkout</h3>
                    <p>Place your order quickly and easily.</p>
                </a>
            <?php } ?>

            <?php if ($role === "admin") { ?>
                <a href="admin/dashboard.php" class="rounded-3xl p-6 text-white shadow-xl bg-gradient-to-r from-indigo-500 to-blue-700 hover:scale-105 transition">
                    <h3 class="text-2xl font-bold mb-2">Admin Dashboard</h3>
                    <p>View total users, products, and orders.</p>
                </a>

                <a href="admin/manage_users.php" class="rounded-3xl p-6 text-white shadow-xl bg-gradient-to-r from-pink-500 to-rose-600 hover:scale-105 transition">
                    <h3 class="text-2xl font-bold mb-2">Manage Users</h3>
                    <p>Handle all registered users in the system.</p>
                </a>

                <a href="admin/manage_products.php" class="rounded-3xl p-6 text-white shadow-xl bg-gradient-to-r from-slate-600 to-slate-800 hover:scale-105 transition">
                    <h3 class="text-2xl font-bold mb-2">Manage Products</h3>
                    <p>Manage all available products.</p>
                </a>
            <?php } ?>

        </div>
    </div>

</body>
</html>