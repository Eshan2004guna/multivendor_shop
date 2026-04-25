<?php
require_once "../seller_guard.php";
require_once "../db.php";

$seller_id = $_SESSION["user_id"];

// UPDATE STATUS
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $order_id = intval($_POST["order_id"]);
    $status = $_POST["status"];

    $update = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
    $update->bind_param("si", $status, $order_id);
    $update->execute();

    echo "<script>alert('Order updated'); window.location.href='orders.php';</script>";
    exit();
}

// FETCH ORDERS
$sql = "SELECT 
            order_items.*, 
            orders.id AS order_id,
            orders.status,
            orders.created_at,
            users.full_name AS customer_name,
            products.product_name
        FROM order_items
        JOIN orders ON order_items.order_id = orders.id
        JOIN users ON orders.customer_id = users.id
        JOIN products ON order_items.product_id = products.id
        WHERE order_items.seller_id = ?
        ORDER BY orders.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Seller Orders</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6 bg-gray-100">

<h1 class="text-3xl font-bold mb-6">My Orders</h1>

<?php if ($result->num_rows > 0) { ?>
    <?php while ($row = $result->fetch_assoc()) { ?>
        <div class="bg-white p-5 mb-4 rounded shadow">

            <h2 class="font-bold text-lg">Order #<?php echo $row["order_id"]; ?></h2>

            <p><strong>Customer:</strong> <?php echo htmlspecialchars($row["customer_name"]); ?></p>
            <p><strong>Product:</strong> <?php echo htmlspecialchars($row["product_name"]); ?></p>
            <p><strong>Quantity:</strong> <?php echo $row["quantity"]; ?></p>
            <p><strong>Price:</strong> Rs. <?php echo number_format($row["price"], 2); ?></p>
            <p><strong>Date:</strong> <?php echo $row["created_at"]; ?></p>

            <form method="POST" class="mt-3 flex gap-3">
                <input type="hidden" name="order_id" value="<?php echo $row["order_id"]; ?>">

                <select name="status" class="border p-2 rounded">
                    <option <?php if($row["status"]=="Pending") echo "selected"; ?>>Pending</option>
                    <option <?php if($row["status"]=="Processing") echo "selected"; ?>>Processing</option>
                    <option <?php if($row["status"]=="Shipped") echo "selected"; ?>>Shipped</option>
                    <option <?php if($row["status"]=="Delivered") echo "selected"; ?>>Delivered</option>
                </select>

                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">
                    Update
                </button>
            </form>

        </div>
    <?php } ?>
<?php } else { ?>
    <p>No orders found.</p>
<?php } ?>

</body>
</html>