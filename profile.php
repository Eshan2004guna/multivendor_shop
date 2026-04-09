<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$message = "";
$messageType = "";

$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $new_password = trim($_POST["new_password"] ?? "");
    $confirm_password = trim($_POST["confirm_password"] ?? "");

    if (empty($full_name) || empty($email)) {
        $message = "Full name and email are required.";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "error";
    } else {
        $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkEmail->bind_param("si", $email, $user_id);
        $checkEmail->execute();
        $emailResult = $checkEmail->get_result();

        if ($emailResult->num_rows > 0) {
            $message = "This email is already used by another account.";
            $messageType = "error";
        } else {
            if (!empty($new_password)) {
                if (strlen($new_password) < 6) {
                    $message = "Password must be at least 6 characters.";
                    $messageType = "error";
                } elseif ($new_password !== $confirm_password) {
                    $message = "Passwords do not match.";
                    $messageType = "error";
                } else {
                    $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                    $update = $conn->prepare("UPDATE users SET full_name = ?, email = ?, password = ? WHERE id = ?");
                    $update->bind_param("sssi", $full_name, $email, $hashedPassword, $user_id);

                    if ($update->execute()) {
                        $_SESSION["full_name"] = $full_name;
                        $_SESSION["email"] = $email;
                        $message = "Profile and password updated successfully.";
                        $messageType = "success";
                    } else {
                        $message = "Failed to update profile.";
                        $messageType = "error";
                    }
                }
            } else {
                $update = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
                $update->bind_param("ssi", $full_name, $email, $user_id);

                if ($update->execute()) {
                    $_SESSION["full_name"] = $full_name;
                    $_SESSION["email"] = $email;
                    $message = "Profile updated successfully.";
                    $messageType = "success";
                } else {
                    $message = "Failed to update profile.";
                    $messageType = "error";
                }
            }
        }
    }

    $refresh = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $refresh->bind_param("i", $user_id);
    $refresh->execute();
    $user = $refresh->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Eshan HyperMart</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-pink-100 via-purple-100 to-indigo-100 p-6">

    <div class="max-w-2xl mx-auto bg-white rounded-3xl shadow-2xl p-8">
        <h1 class="text-3xl font-extrabold text-purple-700 mb-6 text-center">My Profile</h1>

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
                <input
                    type="text"
                    name="full_name"
                    value="<?php echo htmlspecialchars($user["full_name"]); ?>"
                    class="w-full border rounded-xl p-3"
                    required
                >
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
                <input
                    type="email"
                    name="email"
                    value="<?php echo htmlspecialchars($user["email"]); ?>"
                    class="w-full border rounded-xl p-3"
                    required
                >
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Role</label>
                <input
                    type="text"
                    value="<?php echo ucfirst(htmlspecialchars($user["role"])); ?>"
                    class="w-full border rounded-xl p-3 bg-slate-100"
                    readonly
                >
            </div>

            <hr class="my-4">

            <h2 class="text-xl font-bold text-slate-700">Change Password</h2>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">New Password</label>
                <input
                    type="password"
                    name="new_password"
                    placeholder="Leave blank if you don't want to change"
                    class="w-full border rounded-xl p-3"
                >
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Confirm New Password</label>
                <input
                    type="password"
                    name="confirm_password"
                    class="w-full border rounded-xl p-3"
                >
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-pink-500 via-purple-500 to-indigo-500 text-white py-3 rounded-xl font-bold">
                Update Profile
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="main.php" class="text-blue-600 font-semibold">← Back to Dashboard</a>
        </div>
    </div>

</body>
</html>