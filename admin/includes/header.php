<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/functions.php';

$currentPage = basename($_SERVER['PHP_SELF']);

function navActive($pages) {
    global $currentPage;
    return in_array($currentPage, (array)$pages)
        ? 'bg-gray-800 border-l-4 border-red-500 text-white'
        : 'text-gray-400 hover:bg-gray-800 hover:text-white';
}
function btmActive($pages) {
    global $currentPage;
    return in_array($currentPage, (array)$pages) ? 'text-red-500' : 'text-gray-400';
}
function pageTitle() {
    global $currentPage;
    $map = [
        'index.php'        => 'Dashboard',
        'orders.php'       => 'Orders',
        'view_order.php'   => 'Order Details',
        'products.php'     => 'Products',
        'add_product.php'  => 'Add Product',
        'edit_product.php' => 'Edit Product',
        'landing_pages.php'=> 'Landing Pages',
        'manage_landing.php'=>'Landing Editor',
        'customers.php'    => 'Customers',
        'employees.php'    => 'Employees',
        'pixel.php'        => 'Pixel & Tracking',
        'settings.php'     => 'Settings',
    ];
    return $map[$currentPage] ?? 'Admin';
}

$navItems = [
    ['href'=>'index.php',        'icon'=>'fa-tachometer-alt', 'label'=>'Dashboard',     'pages'=>['index.php']],
    ['href'=>'orders.php',       'icon'=>'fa-box-open',       'label'=>'Orders',         'pages'=>['orders.php','view_order.php']],
    ['href'=>'customers.php',    'icon'=>'fa-users',          'label'=>'Customers',      'pages'=>['customers.php']],
    ['href'=>'products.php',     'icon'=>'fa-tags',           'label'=>'Products',       'pages'=>['products.php','add_product.php','edit_product.php']],
    ['href'=>'landing_pages.php','icon'=>'fa-rocket',         'label'=>'Landing Pages',  'pages'=>['landing_pages.php','manage_landing.php']],
    ['href'=>'employees.php',    'icon'=>'fa-user-tie',       'label'=>'Employees',      'pages'=>['employees.php']],
    ['href'=>'pixel.php',        'icon'=>'fa-code',           'label'=>'Pixel',          'pages'=>['pixel.php']],
    ['href'=>'settings.php',     'icon'=>'fa-cog',            'label'=>'Settings',       'pages'=>['settings.php']],
];
$btmItems = [
    ['href'=>'index.php',        'icon'=>'fa-tachometer-alt', 'label'=>'Home',      'pages'=>['index.php']],
    ['href'=>'orders.php',       'icon'=>'fa-box-open',       'label'=>'Orders',    'pages'=>['orders.php','view_order.php']],
    ['href'=>'customers.php',    'icon'=>'fa-users',          'label'=>'Customers', 'pages'=>['customers.php']],
    ['href'=>'landing_pages.php','icon'=>'fa-rocket',         'label'=>'Landing',   'pages'=>['landing_pages.php','manage_landing.php']],
    ['href'=>'settings.php',     'icon'=>'fa-cog',            'label'=>'Settings',  'pages'=>['settings.php']],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= pageTitle() ?> — ReadyMart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { -webkit-tap-highlight-color: transparent; }
        .sidebar-drawer { transition: transform 0.28s cubic-bezier(.4,0,.2,1); }
        .overlay-bg     { transition: opacity 0.28s ease; }
    </style>
</head>
<body class="bg-gray-100 flex min-h-screen">

<!-- ═══ DESKTOP SIDEBAR ═══════════════════════════════════════════════ -->
<aside class="w-64 bg-gray-900 text-white flex-shrink-0 hidden md:flex flex-col fixed inset-y-0 left-0 z-40">
    <div class="p-4 bg-gray-950 border-b border-gray-800">
        <h1 class="text-xl font-bold flex items-center gap-2">
            <i class="fas fa-shopping-cart text-red-500"></i> ReadyMart Admin
        </h1>
    </div>
    <nav class="flex-1 py-4 overflow-y-auto">
        <ul class="space-y-0.5">
            <?php foreach($navItems as $item): ?>
            <li>
                <a href="<?= $item['href'] ?>" class="flex items-center gap-3 px-4 py-2.5 transition-colors text-sm <?= navActive($item['pages']) ?>">
                    <i class="fas <?= $item['icon'] ?> w-5 text-center text-sm"></i>
                    <?= $item['label'] ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <div class="p-4 bg-gray-950 border-t border-gray-800">
        <a href="logout.php" class="flex items-center gap-3 px-4 py-2 text-gray-400 hover:text-white transition-colors rounded-lg hover:bg-gray-800 text-sm">
            <i class="fas fa-sign-out-alt w-5 text-center"></i> Logout
        </a>
    </div>
</aside>

<!-- ═══ MOBILE DRAWER ═════════════════════════════════════════════════ -->
<div id="sidebarOverlay" class="overlay-bg fixed inset-0 bg-black/60 z-50 hidden opacity-0 md:hidden"></div>
<aside id="sidebar" class="sidebar-drawer fixed top-0 left-0 h-full w-72 bg-gray-900 text-white z-50 flex flex-col -translate-x-full md:hidden">
    <div class="flex items-center justify-between p-4 bg-gray-950 border-b border-gray-800">
        <h1 class="text-lg font-bold flex items-center gap-2">
            <i class="fas fa-shopping-cart text-red-500"></i> ReadyMart
        </h1>
        <button id="closeSidebar" class="text-gray-400 hover:text-white w-9 h-9 flex items-center justify-center rounded-lg hover:bg-gray-800">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <nav class="flex-1 py-3 overflow-y-auto">
        <ul class="space-y-0.5">
            <?php foreach($navItems as $item): ?>
            <li>
                <a href="<?= $item['href'] ?>" onclick="closeSidebarFn()"
                   class="flex items-center gap-3 px-5 py-3 transition-colors text-sm <?= navActive($item['pages']) ?>">
                    <i class="fas <?= $item['icon'] ?> w-5 text-center"></i>
                    <?= $item['label'] ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <div class="p-4 bg-gray-950 border-t border-gray-800">
        <a href="logout.php" class="flex items-center gap-3 px-4 py-3 text-gray-400 hover:text-white transition-colors rounded-lg hover:bg-gray-800 text-sm">
            <i class="fas fa-sign-out-alt w-5 text-center"></i> Logout
        </a>
    </div>
</aside>

<!-- ═══ MAIN CONTENT ══════════════════════════════════════════════════ -->
<div class="flex-1 flex flex-col min-w-0 md:ml-64">

    <!-- Mobile Top Header -->
    <header class="md:hidden fixed top-0 left-0 right-0 bg-gray-900 text-white z-40">
        <div class="flex items-center justify-between px-4 h-14">
            <button id="openSidebar" class="w-10 h-10 flex items-center justify-center rounded-lg hover:bg-gray-800 transition">
                <i class="fas fa-bars text-lg"></i>
            </button>
            <span class="text-base font-bold">
                <i class="fas fa-shopping-cart text-red-500 mr-1.5 text-sm"></i><?= pageTitle() ?>
            </span>
            <a href="../index.php" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-lg hover:bg-gray-800 text-gray-300 hover:text-white transition">
                <i class="fas fa-external-link-alt text-sm"></i>
            </a>
        </div>
    </header>

    <!-- Desktop Topbar -->
    <header class="bg-white shadow-sm px-6 py-4 hidden md:flex justify-between items-center z-10">
        <h2 class="text-xl font-semibold text-gray-800"><?= pageTitle() ?></h2>
        <div class="flex items-center gap-4">
            <a href="../index.php" target="_blank" class="text-sm text-blue-600 hover:underline">
                <i class="fas fa-external-link-alt mr-1"></i>View Store
            </a>
            <span class="text-gray-600 text-sm"><i class="fas fa-user-circle mr-1"></i>Admin</span>
        </div>
    </header>

    <!-- Page Content -->
    <main class="flex-1 p-4 md:p-6 overflow-y-auto mt-14 md:mt-0 mb-16 md:mb-0">
