<?php
session_start();
require_once "db.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $confirm_password = trim($_POST["confirm_password"] ?? "");
    $role = trim($_POST["role"] ?? "");

    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        $message = "Please fill in all fields.";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "error";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $messageType = "error";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
        $messageType = "error";
    } elseif (!in_array($role, ["seller", "customer"])) {
        $message = "Invalid role selected.";
        $messageType = "error";
    } else {
        $checkSql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "This email is already registered.";
            $messageType = "error";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $insertSql = "INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt2 = $conn->prepare($insertSql);
            $stmt2->bind_param("ssss", $full_name, $email, $hashedPassword, $role);

            if ($stmt2->execute()) {
                $message = "Signup successful! You can login now.";
                $messageType = "success";
            } else {
                $message = "Signup failed. Please try again.";
                $messageType = "error";
            }

            $stmt2->close();
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Eshan HyperMart</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-pink-500 via-purple-500 to-indigo-600 flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-white/95 backdrop-blur-md rounded-3xl shadow-2xl p-8 border border-white/40">
        <div class="text-center mb-6">
            <h1 class="text-4xl font-extrabold bg-gradient-to-r from-pink-600 via-purple-600 to-blue-600 bg-clip-text text-transparent">
                ESHAN HYPERMART
            </h1>
            <p class="text-sm text-slate-600 mt-2">Everything You Need, One Place.</p>
            <h2 class="text-2xl font-bold text-slate-800 mt-4">Create Account</h2>
        </div>

        <?php if (!empty($message)) { ?>
            <div class="mb-4 px-4 py-3 rounded-xl text-sm font-medium <?php echo $messageType === 'success'
                ? 'bg-green-100 text-green-700 border border-green-300'
                : 'bg-red-100 text-red-700 border border-red-300'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php } ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Full Name</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                    placeholder="Enter your full name"
                    class="w-full border border-pink-200 rounded-xl p-3 focus:outline-none focus:ring-2 focus:ring-pink-500" required>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    placeholder="Enter your email"
                    class="w-full border border-purple-200 rounded-xl p-3 focus:outline-none focus:ring-2 focus:ring-purple-500" required>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Role</label>
                <select name="role"
                    class="w-full border border-blue-200 rounded-xl p-3 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <option value="">Select Role</option>
                    <option value="customer" <?php echo (($_POST['role'] ?? '') === 'customer') ? 'selected' : ''; ?>>Customer</option>
                    <option value="seller" <?php echo (($_POST['role'] ?? '') === 'seller') ? 'selected' : ''; ?>>Seller</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Password</label>
                <input type="password" name="password" placeholder="Enter password"
                    class="w-full border border-yellow-200 rounded-xl p-3 focus:outline-none focus:ring-2 focus:ring-yellow-500" required>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Confirm Password</label>
                <input type="password" name="confirm_password" placeholder="Confirm password"
                    class="w-full border border-green-200 rounded-xl p-3 focus:outline-none focus:ring-2 focus:ring-green-500" required>
            </div>

            <button type="submit"
                class="w-full bg-gradient-to-r from-pink-500 via-purple-500 to-indigo-500 text-white py-3 rounded-xl font-bold hover:scale-[1.02] transition duration-200 shadow-lg">
                Sign Up
            </button>
        </form>

        <p class="text-center text-sm text-slate-600 mt-6">
            Already have an account?
            <a href="login.php" class="text-purple-600 font-bold hover:underline">Login</a>
        </p>
    </div>

</body>
</html>