<?php
require_once "../customer_guard.php";
require_once "../db.php";

$customer_id = $_SESSION["user_id"];

if (isset($_GET["remove"])) {
    $cart_id = intval($_GET["remove"]);
    $delete = $conn->prepare("DELETE FROM cart WHERE id = ? AND customer_id = ?");
    $delete->bind_param("ii", $cart_id, $customer_id);
    $delete->execute();

    echo "<script>alert('Item removed'); window.location.href='cart.php';</script>";
    exit();
}

$sql = "SELECT cart.id AS cart_id, cart.quantity, products.*
        FROM cart
        JOIN products ON cart.product_id = products.id
        WHERE cart.customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-purple-100 via-pink-100 to-rose-100 p-6">
    <div class="max-w-5xl mx-auto bg-white rounded-3xl shadow-2xl p-8">
        <h1 class="text-3xl font-extrabold mb-6 text-purple-700">My Cart</h1>

        <div class="space-y-4">
            <?php while ($row = $result->fetch_assoc()) {
                $subTotal = $row["price"] * $row["quantity"];
                $total += $subTotal;
            ?>
                <div class="flex flex-col md:flex-row md:items-center justify-between border rounded-2xl p-4 bg-purple-50">
                    <div>
                        <h2 class="text-xl font-semibold"><?php echo htmlspecialchars($row["product_name"]); ?></h2>
                        <p>Price: Rs. <?php echo number_format($row["price"], 2); ?></p>
                        <p>Quantity: <?php echo $row["quantity"]; ?></p>
                        <p class="font-bold">Subtotal: Rs. <?php echo number_format($subTotal, 2); ?></p>
                    </div>

                    <div class="mt-3 md:mt-0">
                        <a href="?remove=<?php echo $row["cart_id"]; ?>" onclick="return confirm('Remove this item?')"
                           class="bg-red-500 text-white px-4 py-2 rounded-xl hover:bg-red-600">
                            Remove
                        </a>
                    </div>
                </div>
            <?php } ?>
        </div>

        <div class="mt-6 border-t pt-4">
            <h2 class="text-2xl font-extrabold">Total: Rs. <?php echo number_format($total, 2); ?></h2>
            <?php if ($total > 0) { ?>
                <a href="checkout.php" class="inline-block mt-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-3 rounded-xl font-bold">
                    Checkout
                </a>
            <?php } ?>
        </div>

        <div class="mt-4">
            <a href="products.php" class="text-blue-600 font-semibold">← Continue Shopping</a>
        </div>
    </div>
</body>
</html>