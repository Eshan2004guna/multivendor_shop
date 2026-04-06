<?php
require_once "../seller_guard.php";
require_once "../db.php";

$seller_id = $_SESSION["user_id"];

if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);

    $check = $conn->prepare("SELECT image FROM products WHERE id = ? AND seller_id = ?");
    $check->bind_param("ii", $id, $seller_id);
    $check->execute();
    $result = $check->get_result();

    if ($row = $result->fetch_assoc()) {
        if (!empty($row["image"]) && file_exists("../uploads/" . $row["image"])) {
            unlink("../uploads/" . $row["image"]);
        }

        $delete = $conn->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
        $delete->bind_param("ii", $id, $seller_id);
        $delete->execute();

        echo "<script>alert('Product deleted'); window.location.href='manage_products.php';</script>";
        exit();
    }
}

$sql = "SELECT * FROM products WHERE seller_id = ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$products = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-yellow-100 via-orange-100 to-red-100 p-6">
    <div class="max-w-6xl mx-auto bg-white shadow-2xl rounded-3xl p-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-extrabold text-orange-600">My Products</h1>
            <a href="add_product.php" class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-5 py-2 rounded-xl font-bold">Add New</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-orange-200">
                        <th class="p-3 text-left">Image</th>
                        <th class="p-3 text-left">Name</th>
                        <th class="p-3 text-left">Price</th>
                        <th class="p-3 text-left">Stock</th>
                        <th class="p-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $products->fetch_assoc()) { ?>
                        <tr class="border-b">
                            <td class="p-3">
                                <?php if (!empty($row["image"])) { ?>
                                    <img src="../uploads/<?php echo htmlspecialchars($row["image"]); ?>" class="w-16 h-16 object-cover rounded-lg">
                                <?php } else { ?>
                                    No image
                                <?php } ?>
                            </td>
                            <td class="p-3"><?php echo htmlspecialchars($row["product_name"]); ?></td>
                            <td class="p-3">Rs. <?php echo number_format($row["price"], 2); ?></td>
                            <td class="p-3"><?php echo $row["stock"]; ?></td>
                            <td class="p-3">
                                <a href="?delete=<?php echo $row["id"]; ?>" onclick="return confirm('Delete this product?')"
                                   class="bg-red-500 text-white px-3 py-1 rounded-lg hover:bg-red-600">
                                   Delete
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4 text-center">
            <a href="../main.php" class="text-blue-600 font-semibold">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>