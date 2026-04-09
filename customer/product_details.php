<?php
require_once "../customer_guard.php";
require_once "../db.php";

$customer_id = $_SESSION["user_id"];

if (!isset($_GET["id"])) {
    header("Location: products.php");
    exit();
}

$product_id = intval($_GET["id"]);

if (isset($_GET["wishlist"])) {
    $checkWish = $conn->prepare("SELECT id FROM wishlist WHERE customer_id = ? AND product_id = ?");
    $checkWish->bind_param("ii", $customer_id, $product_id);
    $checkWish->execute();
    $wishResult = $checkWish->get_result();

    if ($wishResult->num_rows == 0) {
        $insertWish = $conn->prepare("INSERT INTO wishlist (customer_id, product_id) VALUES (?, ?)");
        $insertWish->bind_param("ii", $customer_id, $product_id);
        $insertWish->execute();
        echo "<script>alert('Added to wishlist'); window.location.href='product_details.php?id=$product_id';</script>";
    } else {
        echo "<script>alert('Already in wishlist'); window.location.href='product_details.php?id=$product_id';</script>";
    }
    exit();
}

if (isset($_GET["add_to_cart"])) {
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

    echo "<script>alert('Added to cart'); window.location.href='product_details.php?id=$product_id';</script>";
    exit();
}

$sql = "SELECT products.*, users.full_name AS seller_name, users.email AS seller_email
        FROM products
        JOIN users ON products.seller_id = users.id
        WHERE products.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Product not found'); window.location.href='products.php';</script>";
    exit();
}

$product = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details - Eshan HyperMart</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-100 via-blue-100 to-cyan-100 p-6">

    <div class="max-w-6xl mx-auto bg-white rounded-3xl shadow-2xl p-8">
        <div class="grid md:grid-cols-2 gap-8 items-start">
            <div>
                <?php if (!empty($product["image"])) { ?>
                    <img src="../uploads/<?php echo htmlspecialchars($product["image"]); ?>" class="w-full h-[420px] object-cover rounded-3xl shadow-lg">
                <?php } else { ?>
                    <div class="w-full h-[420px] bg-slate-200 rounded-3xl flex items-center justify-center text-slate-600">
                        No Image
                    </div>
                <?php } ?>
            </div>

            <div>
                <h1 class="text-4xl font-extrabold text-slate-800 mb-4">
                    <?php echo htmlspecialchars($product["product_name"]); ?>
                </h1>

                <p class="text-slate-600 text-lg mb-4">
                    <?php echo nl2br(htmlspecialchars($product["description"])); ?>
                </p>

                <p class="text-3xl font-bold text-green-700 mb-3">
                    Rs. <?php echo number_format($product["price"], 2); ?>
                </p>

                <p class="text-slate-700 mb-2">
                    <span class="font-bold">Stock:</span> <?php echo $product["stock"]; ?>
                </p>

                <p class="text-slate-700 mb-2">
                    <span class="font-bold">Seller:</span> <?php echo htmlspecialchars($product["seller_name"]); ?>
                </p>

                <p class="text-slate-700 mb-6">
                    <span class="font-bold">Seller Email:</span> <?php echo htmlspecialchars($product["seller_email"]); ?>
                </p>

                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="?id=<?php echo $product["id"]; ?>&wishlist=<?php echo $product["id"]; ?>" class="bg-pink-500 text-white px-6 py-3 rounded-xl font-bold text-center">
                        Add to Wishlist
                    </a>

                    <?php if ($product["stock"] > 0) { ?>
                        <a href="?id=<?php echo $product["id"]; ?>&add_to_cart=<?php echo $product["id"]; ?>" class="bg-gradient-to-r from-blue-500 to-cyan-500 text-white px-6 py-3 rounded-xl font-bold text-center">
                            Add to Cart
                        </a>
                    <?php } else { ?>
                        <button class="bg-gray-400 text-white px-6 py-3 rounded-xl font-bold cursor-not-allowed">
                            Out of Stock
                        </button>
                    <?php } ?>

                    <a href="products.php" class="bg-slate-700 text-white px-6 py-3 rounded-xl font-bold text-center">
                        Back
                    </a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>