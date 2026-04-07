<?php
require_once "../admin_guard.php";
require_once "../db.php";

if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);

    $imgQuery = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $imgQuery->bind_param("i", $id);
    $imgQuery->execute();
    $imgResult = $imgQuery->get_result();

    if ($imgRow = $imgResult->fetch_assoc()) {
        if (!empty($imgRow["image"]) && file_exists("../uploads/" . $imgRow["image"])) {
            unlink("../uploads/" . $imgRow["image"]);
        }
    }

    $delete = $conn->prepare("DELETE FROM products WHERE id = ?");
    $delete->bind_param("i", $id);
    $delete->execute();

    echo "<script>alert('Product deleted'); window.location.href='manage_products.php';</script>";
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
    <title>Manage Products</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-100 via-gray-100 to-zinc-200 p-6">
    <div class="max-w-7xl mx-auto bg-white p-8 rounded-3xl shadow-2xl">
        <h1 class="text-3xl font-extrabold mb-6 text-slate-700">Manage All Products</h1>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-slate-200">
                        <th class="p-3 text-left">Image</th>
                        <th class="p-3 text-left">Name</th>
                        <th class="p-3 text-left">Seller</th>
                        <th class="p-3 text-left">Price</th>
                        <th class="p-3 text-left">Stock</th>
                        <th class="p-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr class="border-b">
                            <td class="p-3">
                                <?php if (!empty($row["image"])) { ?>
                                    <img src="../uploads/<?php echo htmlspecialchars($row["image"]); ?>" class="w-16 h-16 object-cover rounded-lg">
                                <?php } else { ?>
                                    No image
                                <?php } ?>
                            </td>
                            <td class="p-3"><?php echo htmlspecialchars($row["product_name"]); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($row["seller_name"]); ?></td>
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

        <div class="mt-4">
            <a href="dashboard.php" class="text-blue-600 font-semibold">← Back to Admin Dashboard</a>
        </div>
    </div>
</body>
</html>