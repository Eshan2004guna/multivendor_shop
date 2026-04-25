<?php
require_once "../customer_guard.php";
require_once "../db.php";

$customer_id = $_SESSION["user_id"];

$sql = "SELECT cart.quantity, products.id AS product_id, products.seller_id,
               products.product_name, products.price, products.stock
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
    $cartItems[] = $row;
    $total += $row["price"] * $row["quantity"];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $conn->begin_transaction();

    try {
        // CREATE ORDER
        $orderSql = "INSERT INTO orders (customer_id, total_amount, status) VALUES (?, ?, 'Pending')";
        $orderStmt = $conn->prepare($orderSql);
        $orderStmt->bind_param("id", $customer_id, $total);
        $orderStmt->execute();

        $order_id = $conn->insert_id;

        foreach ($cartItems as $item) {

            // INSERT ORDER ITEMS (IMPORTANT)
            $itemSql = "INSERT INTO order_items (order_id, product_id, seller_id, quantity, price)
                        VALUES (?, ?, ?, ?, ?)";

            $itemStmt = $conn->prepare($itemSql);
            $itemStmt->bind_param(
                "iiiid",
                $order_id,
                $item["product_id"],
                $item["seller_id"], // 🔥 VERY IMPORTANT
                $item["quantity"],
                $item["price"]
            );
            $itemStmt->execute();

            // UPDATE STOCK
            $newStock = $item["stock"] - $item["quantity"];
            $updateStock = $conn->prepare("UPDATE products SET stock=? WHERE id=?");
            $updateStock->bind_param("ii", $newStock, $item["product_id"]);
            $updateStock->execute();
        }

        // CLEAR CART
        $clear = $conn->prepare("DELETE FROM cart WHERE customer_id=?");
        $clear->bind_param("i", $customer_id);
        $clear->execute();

        $conn->commit();

        echo "<script>alert('Order placed successfully'); window.location.href='products.php';</script>";

    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Checkout failed');</script>";
    }
}
?>