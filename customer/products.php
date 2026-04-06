<?php
require_once "../customer_guard.php";
require_once "../db.php";

$customer_id = $_SESSION["user_id"];

if (isset($_GET["add_to_cart"])) {
    $product_id = intval($_GET["add_to_cart"]);

    $check = $conn->prepare("SELECT id, quantity FROM cart WHERE customer_id = ? AND product_id = ?");
    $check->bind_param("ii", $customer_id, $product_id);
    $check->execute();
    $cartResult = $check->get_result();

    if ($row = $cartResult->fetch_assoc()) {
        $newQty = $row["quantity"] + 1;
        $update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $update->bind_param("ii", $newQty, $row["id"]);
        $update->execute();
    } else {
        $insert = $conn->prepare("INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, 1)");
        $insert->bind_param("ii", $customer_id, $product_id);
        $insert->execute();
    }

    echo "<script>alert('Added to cart'); window.location.href='products.php';</script>";
    exit();
}

$sql = "SELECT products.*, users.full_name AS seller_name 
        FROM products 
        JOIN users ON products.seller_id = users.id
        ORDER BY products.id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-100 via-cyan-100 to-sky-200 p-6">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-extrabold text-blue-700">All Products</h1>
            <a href="cart.php" class="bg-gradient-to-r from-purple-500 to-pink-500 text-white px-5 py-2 rounded-xl font-bold">My Cart</a>
        </div>

        <div class="grid md:grid-cols-3 gap-6">
            <?php while ($row = $result->fetch_assoc()) { ?>
                <div class="bg-white rounded-3xl shadow-xl p-4 hover:scale-[1.02] transition">
                    <?php if (!empty($row["image"])) { ?>
                        <img src="../uploads/<?php echo htmlspecialchars($row["image"]); ?>" class="w-full h-56 object-cover rounded-xl mb-4">
                    <?php } else { ?>
                        <div class="w-full h-56 bg-slate-200 rounded-xl mb-4 flex items-center justify-center">No Image</div>
                    <?php } ?>

                    <h2 class="text-xl font-bold"><?php echo htmlspecialchars($row["product_name"]); ?></h2>
                    <p class="text-slate-600 mt-2"><?php echo htmlspecialchars($row["description"]); ?></p>
                    <p class="mt-2 font-semibold text-green-700">Rs. <?php echo number_format($row["price"], 2); ?></p>
                    <p class="text-sm text-slate-500">Stock: <?php echo $row["stock"]; ?></p>
                    <p class="text-sm text-slate-500 mb-4">Seller: <?php echo htmlspecialchars($row["seller_name"]); ?></p>

                    <?php if ($row["stock"] > 0) { ?>
                        <a href="?add_to_cart=<?php echo $row["id"]; ?>" class="block text-center bg-gradient-to-r from-blue-500 to-cyan-500 text-white py-2 rounded-xl font-semibold">
                            Add to Cart
                        </a>
                    <?php } else { ?>
                        <button class="w-full bg-gray-400 text-white py-2 rounded-xl cursor-not-allowed">Out of Stock</button>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>

        <div class="mt-6 text-center">
            <a href="../main.php" class="text-blue-600 font-semibold">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>