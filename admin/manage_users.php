<?php
require_once "../admin_guard.php";
require_once "../db.php";

if (isset($_GET["delete"])) {
    $id = intval($_GET["delete"]);

    if ($id != $_SESSION["user_id"]) {
        $delete = $conn->prepare("DELETE FROM users WHERE id = ?");
        $delete->bind_param("i", $id);
        $delete->execute();
    }

    echo "<script>alert('User deleted'); window.location.href='manage_users.php';</script>";
    exit();
}

$result = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-pink-100 via-rose-100 to-red-100 p-6">
    <div class="max-w-6xl mx-auto bg-white p-8 rounded-3xl shadow-2xl">
        <h1 class="text-3xl font-extrabold mb-6 text-pink-700">Manage Users</h1>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-pink-200">
                        <th class="p-3 text-left">ID</th>
                        <th class="p-3 text-left">Name</th>
                        <th class="p-3 text-left">Email</th>
                        <th class="p-3 text-left">Role</th>
                        <th class="p-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr class="border-b">
                            <td class="p-3"><?php echo $row["id"]; ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($row["full_name"]); ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($row["email"]); ?></td>
                            <td class="p-3"><?php echo ucfirst(htmlspecialchars($row["role"])); ?></td>
                            <td class="p-3">
                                <?php if ($row["id"] != $_SESSION["user_id"]) { ?>
                                    <a href="?delete=<?php echo $row["id"]; ?>" onclick="return confirm('Delete this user?')"
                                       class="bg-red-500 text-white px-3 py-1 rounded-lg hover:bg-red-600">
                                       Delete
                                    </a>
                                <?php } else { ?>
                                    <span class="text-gray-500">Current Admin</span>
                                <?php } ?>
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