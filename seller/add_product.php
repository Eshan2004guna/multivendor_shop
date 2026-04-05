<?php
require_once "../seller_guard.php";
require_once "../db.php";

$seller_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = trim($_POST["product_name"]);
    $description = trim($_POST["description"]);
    $price = floatval($_POST["price"]);
    $stock = intval($_POST["stock"]);
    $imageName = "";

    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $folder = "../uploads/";
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }

        $imageName = time() . "_" . basename($_FILES["image"]["name"]);
        $targetFile = $folder . $imageName;
        move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile);
    }

    $sql = "INSERT INTO products (seller_id, product_name, description, price, stock, image)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issdis", $seller_id, $product_name, $description, $price, $stock, $imageName);

    if ($stmt->execute()) {
        echo "<script>alert('Product added successfully'); window.location.href='manage_products.php';</script>";
        exit();
    } else {
        echo "<script>alert('Failed to add product');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-green-200 via-emerald-100 to-teal-200 p-6">
    <div class="max-w-2xl mx-auto bg-white/90 backdrop-blur-md shadow-2xl rounded-3xl p-8">
        <h1 class="text-3xl font-extrabold text-center text-emerald-700 mb-6">Add New Product</h1>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <input type="text" name="product_name" placeholder="Product Name" required class="w-full border rounded-xl p-3">
            <textarea name="description" placeholder="Description" class="w-full border rounded-xl p-3 h-32"></textarea>
            <input type="number" step="0.01" name="price" placeholder="Price" required class="w-full border rounded-xl p-3">
            <input type="number" name="stock" placeholder="Stock" required class="w-full border rounded-xl p-3">
            <input type="file" name="image" accept="image/*" class="w-full border rounded-xl p-3">

            <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white py-3 rounded-xl font-bold hover:scale-[1.02] transition">
                Add Product
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="../main.php" class="text-blue-600 font-semibold">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>