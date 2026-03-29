<?php
session_start();
require_once "db.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if (empty($email) || empty($password)) {
        $message = "Please enter both email and password.";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "error";
    } else {
        $sql = "SELECT id, full_name, email, password, role FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                if (password_verify($password, $row["password"])) {
                    $_SESSION["user_id"] = $row["id"];
                    $_SESSION["full_name"] = $row["full_name"];
                    $_SESSION["email"] = $row["email"];
                    $_SESSION["role"] = $row["role"];

                    header("Location: main.php");
                    exit();
                } else {
                    $message = "Incorrect password.";
                    $messageType = "error";
                }
            } else {
                $message = "No account found with this email.";
                $messageType = "error";
            }

            $stmt->close();
        } else {
            $message = "Something went wrong. Please try again.";
            $messageType = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Eshan HyperMart</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-cyan-500 via-blue-500 to-purple-600 flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-white/95 backdrop-blur-md rounded-3xl shadow-2xl p-8 border border-white/40">
        <div class="text-center mb-6">
            <h1 class="text-4xl font-extrabold bg-gradient-to-r from-cyan-600 via-blue-600 to-purple-600 bg-clip-text text-transparent">
                ESHAN HYPERMART
            </h1>
            <p class="text-sm text-slate-600 mt-2">Everything You Need, One Place.</p>
            <h2 class="text-2xl font-bold text-slate-800 mt-4">Welcome Back</h2>
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
                <label class="block text-sm font-semibold text-slate-700 mb-1">Email Address</label>
                <input
                    type="email"
                    name="email"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    placeholder="Enter your email"
                    class="w-full border border-cyan-200 rounded-xl p-3 focus:outline-none focus:ring-2 focus:ring-cyan-500"
                    required
                >
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Password</label>
                <input
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    class="w-full border border-purple-200 rounded-xl p-3 focus:outline-none focus:ring-2 focus:ring-purple-500"
                    required
                >
            </div>

            <button
                type="submit"
                class="w-full bg-gradient-to-r from-cyan-500 via-blue-500 to-purple-500 text-white py-3 rounded-xl font-bold hover:scale-[1.02] transition duration-200 shadow-lg"
            >
                Login
            </button>
        </form>

        <p class="text-center text-sm text-slate-600 mt-6">
            Don’t have an account?
            <a href="signup.php" class="text-blue-600 font-bold hover:underline">Sign Up</a>
        </p>
    </div>

</body>
</html>