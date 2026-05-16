<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReadyMart Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-white flex-shrink-0 hidden md:flex flex-col">
        <div class="p-4 bg-gray-950 border-b border-gray-800">
            <h1 class="text-xl font-bold flex items-center gap-2">
                <i class="fas fa-shopping-cart text-red-500"></i> ReadyMart Admin
            </h1>
        </div>
        <nav class="flex-1 py-4">
            <ul class="space-y-1">
                <li>
                    <a href="index.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 transition-colors <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'bg-gray-800 border-l-4 border-red-500' : ''; ?>">
                        <i class="fas fa-tachometer-alt w-5 text-center"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="orders.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 transition-colors <?php echo in_array(basename($_SERVER['PHP_SELF']), ['orders.php', 'view_order.php']) ? 'bg-gray-800 border-l-4 border-red-500' : ''; ?>">
                        <i class="fas fa-box-open w-5 text-center"></i> Orders
                    </a>
                </li>
                <li>
                    <a href="products.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 transition-colors <?php echo in_array(basename($_SERVER['PHP_SELF']), ['products.php', 'add_product.php', 'edit_product.php']) ? 'bg-gray-800 border-l-4 border-red-500' : ''; ?>">
                        <i class="fas fa-tags w-5 text-center"></i> Products
                    </a>
                </li>
                <!-- NEW: Landing Page Generator -->
                <li>
                    <a href="landing_pages.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 transition-colors <?php echo in_array(basename($_SERVER['PHP_SELF']), ['landing_pages.php', 'manage_landing.php']) ? 'bg-gray-800 border-l-4 border-red-500' : ''; ?>">
                        <i class="fas fa-rocket w-5 text-center text-blue-400"></i> Landing Pages
                    </a>
                </li>
                <li>
                    <a href="settings.php" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-800 transition-colors <?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'bg-gray-800 border-l-4 border-red-500' : ''; ?>">
                        <i class="fas fa-cog w-5 text-center text-gray-400"></i> Settings
                    </a>
                </li>
            </ul>
        </nav>
        <div class="p-4 bg-gray-950">
            <a href="logout.php" class="flex items-center gap-3 px-4 py-2 text-gray-400 hover:text-white transition-colors">
                <i class="fas fa-sign-out-alt w-5 text-center"></i> Logout
            </a>
        </div>
    </aside>

    <!-- Mobile Header -->
    <div class="md:hidden fixed top-0 left-0 right-0 bg-gray-900 text-white p-4 flex justify-between items-center z-50">
        <h1 class="text-lg font-bold"><i class="fas fa-shopping-cart text-red-500"></i> ReadyMart</h1>
        <button id="mobileMenuBtn" class="text-white focus:outline-none">
            <i class="fas fa-bars text-xl"></i>
        </button>
    </div>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col pt-16 md:pt-0 min-w-0">
        <!-- Topbar -->
        <header class="bg-white shadow-sm px-6 py-4 hidden md:flex justify-between items-center z-10 relative">
            <h2 class="text-xl font-semibold text-gray-800">
                <?php 
                    $page = basename($_SERVER['PHP_SELF']);
                    if($page == 'index.php') echo 'Dashboard Overview';
                    elseif($page == 'products.php') echo 'Product Management';
                    elseif($page == 'add_product.php') echo 'Add New Product';
                    elseif($page == 'edit_product.php') echo 'Edit Product';
                    elseif($page == 'orders.php') echo 'Order Management';
                    elseif($page == 'view_order.php') echo 'Order Details';
                    elseif($page == 'landing_pages.php') echo 'Landing Pages';
                    elseif($page == 'manage_landing.php') echo 'Landing Page Editor';
                    elseif($page == 'settings.php') echo 'Settings';
                    else echo 'Admin Panel';
                ?>
            </h2>
            <div class="flex items-center gap-4">
                <a href="../index.php" target="_blank" class="text-sm text-blue-600 hover:underline"><i class="fas fa-external-link-alt"></i> View Store</a>
                <span class="text-gray-600"><i class="fas fa-user-circle"></i> Admin</span>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 p-4 md:p-6 overflow-y-auto">