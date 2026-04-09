<?php
require_once "../customer_guard.php";
require_once "../db.php";

$customer_id = $_SESSION["user_id"];

if (isset($_GET["remove"])) {
    $product_id = intval($_GET["remove"]);

    $delete = $conn->prepare("DELETE FROM wishlist WHERE customer_id = ? AND product_id = ?");
    $delete->bind_param("ii", $customer_id, $product_id);
    $delete->execute();

    echo "<script>alert('Removed from wishlist'); window.location.href='wishlist.php';</script>";
    exit();
}

if (isset($_GET["add_to_cart"])) {
    $product_id = intval($_GET["add_to_cart"]);

    $stockCheck = $conn->prepare("SELECT stock FROM products WHERE id = ?");
    $stockCheck->bind_param("i", $product_id);
    $stockCheck->execute();
    $stockResult = $stockCheck->get_result();

    if ($stockRow = $stockResult->fetch_assoc()) {
        if ($stockRow["stock"] <= 0) {
            echo "<script>alert('This product is out of stock'); window.location.href='wishlist.php';</script>";
            exit();
        }
    }

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

    echo "<script>alert('Added to cart'); window.location.href='wishlist.php';</script>";
    exit();
}

$sql = "SELECT products.*, users.full_name AS seller_name
        FROM wishlist
        JOIN products ON wishlist.product_id = products.id
        JOIN users ON products.seller_id = users.id
        WHERE wishlist.customer_id = ?
        ORDER BY wishlist.id DESC";
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
    <title>My Wishlist - Eshan HyperMart</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-rose-100 via-pink-100 to-purple-100 p-6">

    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
            <h1 class="text-3xl font-extrabold text-pink-700">My Wishlist ❤️</h1>

            <div class="flex gap-3">
                <a href="products.php" class="bg-blue-600 text-white px-5 py-2 rounded-xl font-bold">
                    Products
                </a>
                <a href="../main.php" class="bg-slate-700 text-white px-5 py-2 rounded-xl font-bold">
                    Dashboard
                </a>
            </div>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if ($result->num_rows > 0) { ?>
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

                        <div class="flex flex-col gap-2">
                            <a href="product_details.php?id=<?php echo $row["id"]; ?>" class="w-full text-center bg-indigo-500 text-white py-2 rounded-xl font-semibold">
                                View Details
                            </a>

                            <?php if ($row["stock"] > 0) { ?>
                                <a href="?add_to_cart=<?php echo $row["id"]; ?>" class="w-full text-center bg-green-600 text-white py-2 rounded-xl font-semibold">
                                    Add to Cart
                                </a>
                            <?php } else { ?>
                                <button class="w-full bg-gray-400 text-white py-2 rounded-xl cursor-not-allowed">Out of Stock</button>
                            <?php } ?>

                            <a href="?remove=<?php echo $row["id"]; ?>" onclick="return confirm('Remove this item from wishlist?')" class="w-full text-center bg-red-500 text-white py-2 rounded-xl font-semibold">
                                Remove
                            </a>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="col-span-full bg-white rounded-2xl shadow-lg p-8 text-center">
                    <h2 class="text-2xl font-bold text-slate-700">Your wishlist is empty</h2>
                    <p class="text-slate-500 mt-2">Save products you like and view them later.</p>
                </div>
            <?php } ?>
        </div>
    </div>

</body>
</html>