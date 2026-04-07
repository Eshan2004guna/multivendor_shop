<?php
require_once "../customer_guard.php";
require_once "../db.php";

$customer_id = $_SESSION["user_id"];

$sql = "SELECT cart.quantity, products.id AS product_id, products.seller_id, products.product_name, products.price, products.stock
        FROM cart
        JOIN products ON cart.product_id = products.id
        WHERE cart.customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$cartItems = [];
$total = 0;

while ($row = $result->fetch_assoc()) {
    if ($row["quantity"] > $row["stock"]) {
        echo "<script>alert('Not enough stock for " . addslashes($row["product_name"]) . "'); window.location.href='cart.php';</script>";
        exit();
    }
    $cartItems[] = $row;
    $total += $row["price"] * $row["quantity"];
}

if (empty($cartItems)) {
    echo "<script>alert('Cart is empty'); window.location.href='products.php';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn->begin_transaction();

    try {
        $orderSql = "INSERT INTO orders (customer_id, total_amount, status) VALUES (?, ?, 'Pending')";
        $orderStmt = $conn->prepare($orderSql);
        $orderStmt->bind_param("id", $customer_id, $total);
        $orderStmt->execute();

        $order_id = $conn->insert_id;

        foreach ($cartItems as $item) {
            $itemSql = "INSERT INTO order_items (order_id, product_id, seller_id, quantity, price)
                        VALUES (?, ?, ?, ?, ?)";
            $itemStmt = $conn->prepare($itemSql);
            $itemStmt->bind_param("iiiid", $order_id, $item["product_id"], $item["seller_id"], $item["quantity"], $item["price"]);
            $itemStmt->execute();

            $newStock = $item["stock"] - $item["quantity"];
            $stockSql = "UPDATE products SET stock = ? WHERE id = ?";
            $stockStmt = $conn->prepare($stockSql);
            $stockStmt->bind_param("ii", $newStock, $item["product_id"]);
            $stockStmt->execute();
        }

        $clearCart = $conn->prepare("DELETE FROM cart WHERE customer_id = ?");
        $clearCart->bind_param("i", $customer_id);
        $clearCart->execute();

        $conn->commit();

        echo "<script>alert('Order placed successfully'); window.location.href='products.php';</script>";
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Checkout failed');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-100 via-green-100 to-lime-100 p-6">
    <div class="max-w-3xl mx-auto bg-white shadow-2xl rounded-3xl p-8">
        <h1 class="text-3xl font-extrabold mb-6 text-green-700">Checkout</h1>

        <div class="space-y-4">
            <?php foreach ($cartItems as $item) { ?>
                <div class="border rounded-2xl p-4 bg-green-50">
                    <h2 class="font-semibold"><?php echo htmlspecialchars($item["product_name"]); ?></h2>
                    <p>Price: Rs. <?php echo number_format($item["price"], 2); ?></p>
                    <p>Quantity: <?php echo $item["quantity"]; ?></p>
                </div>
            <?php } ?>
        </div>

        <h2 class="text-2xl font-extrabold mt-6">Total: Rs. <?php echo number_format($total, 2); ?></h2>

        <form method="POST" class="mt-6">
            <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white py-3 rounded-xl font-bold">
                Confirm Order
            </button>
        </form>

        <div class="mt-4">
            <a href="cart.php" class="text-blue-600 font-semibold">← Back to Cart</a>
        </div>
    </div>
</body>
</html>