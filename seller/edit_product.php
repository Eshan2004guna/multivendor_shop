<?php
require_once "../seller_guard.php";
require_once "../db.php";

$seller_id = $_SESSION["user_id"];

if (!isset($_GET["id"])) {
    header("Location: manage_products.php");
    exit();
}

$product_id = intval($_GET["id"]);

$sql = "SELECT * FROM products WHERE id = ? AND seller_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $product_id, $seller_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Product not found'); window.location.href='manage_products.php';</script>";
    exit();
}

$product = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = trim($_POST["product_name"]);
    $description = trim($_POST["description"]);
    $price = floatval($_POST["price"]);
    $stock = intval($_POST["stock"]);
    $imageName = $product["image"];

    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $folder = "../uploads/";
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        if (!empty($product["image"]) && file_exists("../uploads/" . $product["image"])) {
            unlink("../uploads/" . $product["image"]);
        }

        $imageName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $folder . $imageName;
        move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile);
    }

    $updateSql = "UPDATE products SET product_name = ?, description = ?, price = ?, stock = ?, image = ? WHERE id = ? AND seller_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("ssdisii", $product_name, $description, $price, $stock, $imageName, $product_id, $seller_id);

    if ($updateStmt->execute()) {
        echo "<script>alert('Product updated successfully'); window.location.href='manage_products.php';</script>";
        exit();
    } else {
        echo "<script>alert('Failed to update product');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-orange-100 via-yellow-100 to-red-100 p-6">
    <div class="max-w-2xl mx-auto bg-white shadow-2xl rounded-3xl p-8">
        <h1 class="text-3xl font-extrabold text-orange-600 mb-6 text-center">Edit Product</h1>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="text" name="product_name" value="<?php echo htmlspecialchars($product["product_name"]); ?>" required class="w-full border rounded-xl p-3">
            <textarea name="description" class="w-full border rounded-xl p-3 h-32"><?php echo htmlspecialchars($product["description"]); ?></textarea>
            <input type="number" step="0.01" name="price" value="<?php echo $product["price"]; ?>" required class="w-full border rounded-xl p-3">
            <input type="number" name="stock" value="<?php echo $product["stock"]; ?>" required class="w-full border rounded-xl p-3">

            <?php if (!empty($product["image"])) { ?>
                <img src="../uploads/<?php echo htmlspecialchars($product["image"]); ?>" class="w-32 h-32 object-cover rounded-xl">
            <?php } ?>

            <input type="file" name="image" accept="image/*" class="w-full border rounded-xl p-3">

            <button type="submit" class="w-full bg-gradient-to-r from-orange-500 to-red-500 text-white py-3 rounded-xl font-bold">
                Update Product
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="manage_products.php" class="text-blue-600 font-semibold">← Back to Products</a>
        </div>
    </div>
</body>
</html>