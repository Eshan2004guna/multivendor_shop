<?php
session_start();
include 'db.php';

$message = "";
$error = "";

/* =========================
   SESSION
========================= */
$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_name'] ?? "Guest";
$user_role = $_SESSION['user_role'] ?? "customer";

/* =========================
   SEARCH + FILTER
========================= */
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$brand_filter = isset($_GET['brand']) ? trim($_GET['brand']) : "";

/* =========================
   ADD TO CART
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {

    if (!$user_id) {
        header("Location: login.php");
        exit();
    }

    $product_id = (int)$_POST['product_id'];

    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if ($product) {

        if ($product['stock'] <= 0) {
            $error = "Out of stock";
        } else {

            $check = $conn->prepare("SELECT * FROM cart_items WHERE user_id=? AND product_id=?");
            $check->bind_param("ii", $user_id, $product_id);
            $check->execute();
            $cart = $check->get_result()->fetch_assoc();

            if ($cart) {
                $newQty = $cart['quantity'] + 1;

                $update = $conn->prepare("UPDATE cart_items SET quantity=? WHERE id=?");
                $update->bind_param("ii", $newQty, $cart['id']);
                $update->execute();

                $message = "Cart updated";
            } else {
                $insert = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity, price) VALUES (?, ?, 1, ?)");
                $insert->bind_param("iid", $user_id, $product_id, $product['price']);
                $insert->execute();

                $message = "Added to cart";
            }
        }
    }
}

/* =========================
   ADD TO WISHLIST (FIXED)
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_wishlist'])) {

    if (!$user_id) {
        header("Location: login.php");
        exit();
    }

    $product_id = (int)$_POST['product_id'];

    $check = $conn->prepare("SELECT id FROM wishlist WHERE user_id=? AND product_id=?");
    $check->bind_param("ii", $user_id, $product_id);
    $check->execute();
    $exists = $check->get_result()->num_rows;

    if ($exists > 0) {
        $message = "Already in wishlist ❤️";
    } else {
        $insert = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $insert->bind_param("ii", $user_id, $product_id);

        if ($insert->execute()) {
            $message = "Added to wishlist ❤️";
        } else {
            $error = "Wishlist error: " . $conn->error;
        }
    }
}

/* =========================
   FETCH BRANDS
========================= */
$brands = [];
$bq = $conn->query("SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL AND brand != ''");
while ($b = $bq->fetch_assoc()) {
    $brands[] = $b['brand'];
}

/* =========================
   PRODUCT QUERY (SEARCH FIXED)
========================= */
$sql = "SELECT p.*, u.name AS seller_name
        FROM products p
        LEFT JOIN users u ON p.seller_id = u.id
        WHERE 1=1";

$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND (p.name LIKE ? OR p.brand LIKE ? OR p.description LIKE ?)";
    $s = "%$search%";
    $params[] = $s;
    $params[] = $s;
    $params[] = $s;
    $types .= "sss";
}

if ($brand_filter !== "") {
    $sql .= " AND p.brand = ?";
    $params[] = $brand_filter;
    $types .= "s";
}

$sql .= " ORDER BY p.id DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>MultiVendor Shop</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<!-- NAV -->
<nav class="bg-indigo-700 text-white p-4 flex justify-between">
    <h1 class="font-bold text-xl">MultiVendor Shop</h1>

    <div class="space-x-4">
        <a href="main.php">Home</a>
        <a href="cart.php">Cart</a>
        <a href="wishlist.php">Wishlist</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<!-- SEARCH -->
<div class="max-w-6xl mx-auto mt-6 bg-white p-4 rounded shadow">
    <form method="GET" class="flex gap-2">

        <input type="text" name="search"
               value="<?php echo htmlspecialchars($search); ?>"
               placeholder="Search products..."
               class="w-full border p-2 rounded">

        <select name="brand" class="border p-2 rounded">
            <option value="">All Brands</option>
            <?php foreach ($brands as $b): ?>
                <option value="<?php echo $b; ?>" <?php if ($brand_filter==$b) echo 'selected'; ?>>
                    <?php echo $b; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button class="bg-indigo-600 text-white px-4 rounded">Search</button>
    </form>
</div>

<!-- MESSAGE -->
<div class="max-w-6xl mx-auto mt-4">
    <?php if ($message): ?>
        <div class="bg-green-200 p-2 rounded"><?php echo $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="bg-red-200 p-2 rounded"><?php echo $error; ?></div>
    <?php endif; ?>
</div>

<!-- PRODUCTS -->
<div class="max-w-6xl mx-auto mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">

<?php while ($row = $result->fetch_assoc()): ?>
    <?php $img = !empty($row['image']) ? "uploads/".$row['image'] : "https://via.placeholder.com/300"; ?>

    <div class="bg-white p-4 rounded shadow">

        <img src="<?php echo $img; ?>" class="h-40 w-full object-cover rounded">

        <h2 class="font-bold mt-2"><?php echo $row['name']; ?></h2>
        <p>Rs. <?php echo $row['price']; ?></p>
        <p class="text-sm text-gray-500">Seller: <?php echo $row['seller_name']; ?></p>

        <a href="product_details.php?id=<?php echo $row['id']; ?>"
           class="block bg-blue-600 text-white text-center p-2 mt-2 rounded">
           View
        </a>

        <!-- CART -->
        <form method="POST">
            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
            <button name="add_to_cart"
                    class="w-full bg-green-500 text-white p-2 mt-2 rounded">
                Add Cart
            </button>
        </form>

        <!-- WISHLIST -->
        <form method="POST">
            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
            <button name="add_to_wishlist"
                    class="w-full bg-pink-500 text-white p-2 mt-2 rounded">
                Wishlist
            </button>
        </form>

    </div>
<?php endwhile; ?>

</div>

</body>
</html>