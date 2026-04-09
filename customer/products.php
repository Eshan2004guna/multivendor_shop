<?php
require_once "../customer_guard.php";
require_once "../db.php";

$customer_id = $_SESSION["user_id"];
$search = trim($_GET["search"] ?? "");

if (isset($_GET["wishlist"])) {
    $product_id = intval($_GET["wishlist"]);

    $checkWish = $conn->prepare("SELECT id FROM wishlist WHERE customer_id = ? AND product_id = ?");
    $checkWish->bind_param("ii", $customer_id, $product_id);
    $checkWish->execute();
    $wishResult = $checkWish->get_result();

    if ($wishResult->num_rows == 0) {
        $insertWish = $conn->prepare("INSERT INTO wishlist (customer_id, product_id) VALUES (?, ?)");
        $insertWish->bind_param("ii", $customer_id, $product_id);
        $insertWish->execute();
        echo "<script>alert('Added to wishlist'); window.location.href='products.php';</script>";
    } else {
        echo "<script>alert('Already in wishlist'); window.location.href='products.php';</script>";
    }
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
            echo "<script>alert('This product is out of stock'); window.location.href='products.php';</script>";
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

    echo "<script>alert('Added to cart'); window.location.href='products.php';</script>";
    exit();
}

if ($search !== "") {
    $sql = "SELECT products.*, users.full_name AS seller_name
            FROM products
            JOIN users ON products.seller_id = users.id
            WHERE products.product_name LIKE ? OR products.description LIKE ?
            ORDER BY products.id DESC";
    $stmt = $conn->prepare($sql);
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT products.*, users.full_name AS seller_name
            FROM products
            JOIN users ON products.seller_id = users.id
            ORDER BY products.id DESC";
    $result = $conn->query($sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Eshan HyperMart</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-100 via-cyan-100 to-sky-200 p-6">

    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
            <h1 class="text-3xl font-extrabold text-blue-700">All Products</h1>

            <div class="flex gap-3 flex-wrap">
                <a href="wishlist.php" class="bg-pink-500 text-white px-5 py-2 rounded-xl font-bold">
                    Wishlist
                </a>
                <a href="cart.php" class="bg-purple-600 text-white px-5 py-2 rounded-xl font-bold">
                    My Cart
                </a>
                <a href="../main.php" class="bg-slate-700 text-white px-5 py-2 rounded-xl font-bold">
                    Dashboard
                </a>
            </div>
        </div>

        <form method="GET" class="bg-white rounded-2xl shadow-lg p-4 mb-8 flex flex-col md:flex-row gap-3">
            <input
                type="text"
                name="search"
                value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Search products..."
                class="flex-1 border border-blue-200 rounded-xl p-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <button type="submit" class="bg-gradient-to-r from-blue-500 to-cyan-500 text-white px-6 py-3 rounded-xl font-bold">
                Search
            </button>
            <a href="products.php" class="bg-gray-500 text-white px-6 py-3 rounded-xl font-bold text-center">
                Reset
            </a>
        </form>

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
                                Details
                            </a>

                            <a href="?wishlist=<?php echo $row["id"]; ?>" class="w-full text-center bg-pink-500 text-white py-2 rounded-xl font-semibold">
                                Add to Wishlist
                            </a>

                            <?php if ($row["stock"] > 0) { ?>
                                <a href="?add_to_cart=<?php echo $row["id"]; ?>" class="w-full text-center bg-gradient-to-r from-blue-500 to-cyan-500 text-white py-2 rounded-xl font-semibold">
                                    Add to Cart
                                </a>
                            <?php } else { ?>
                                <button class="w-full bg-gray-400 text-white py-2 rounded-xl cursor-not-allowed">Out of Stock</button>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="col-span-full bg-white rounded-2xl shadow-lg p-8 text-center">
                    <h2 class="text-2xl font-bold text-slate-700">No products found</h2>
                    <p class="text-slate-500 mt-2">Try another search word.</p>
                </div>
            <?php } ?>
        </div>
    </div>

</body>
</html>