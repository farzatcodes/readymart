<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT id, password FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $username;
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - ReadyMart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md border border-gray-200">
        <div class="text-center mb-8">
            <div class="text-3xl font-extrabold text-[#cc0000] tracking-tighter flex items-center justify-center gap-2 mb-2">
                <i class="fas fa-shopping-bag"></i>
                <span>Ready<span class="text-gray-800">Mart</span></span>
            </div>
            <h2 class="text-gray-600 font-semibold uppercase tracking-wider text-sm">Admin Portal</h2>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded mb-4 text-sm border border-red-200">
                <i class="fas fa-exclamation-circle mr-1"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php" class="space-y-5">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user text-gray-400"></i>
                    </div>
                    <input type="text" name="username" class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#cc0000] focus:ring-1 focus:ring-[#cc0000]" placeholder="Enter admin username" required>
                </div>
            </div>

            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input type="password" name="password" class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-[#cc0000] focus:ring-1 focus:ring-[#cc0000]" placeholder="Enter password" required>
                </div>
            </div>

            <button type="submit" class="w-full bg-[#cc0000] text-white font-bold py-3 px-4 rounded-lg hover:bg-red-800 transition-colors shadow-sm">
                Secure Login
            </button>
        </form>
    </div>

</body>
</html>