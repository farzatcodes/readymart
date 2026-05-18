<?php
$ordersFile  = '../orders.json';
$productsFile = '../products.json';
$landingFile = '../landing_pages.json';

$orders      = file_exists($ordersFile)   ? json_decode(file_get_contents($ordersFile),   true) ?? [] : [];
$products    = file_exists($productsFile) ? json_decode(file_get_contents($productsFile), true) ?? [] : [];
$landingPages = file_exists($landingFile) ? json_decode(file_get_contents($landingFile),  true) ?? [] : [];

$totalOrders      = count($orders);
$totalRevenue     = 0;
$pendingOrders    = 0;
$processingOrders = 0;
$completedOrders  = 0;
$cancelledOrders  = 0;
$landingOrders    = 0;

// Today
$todayStr = date('Y-m-d');
$todayOrders = 0;

foreach ($orders as $order) {
    $status = $order['status'] ?? '';
    if ($status === 'Pending')    $pendingOrders++;
    if ($status === 'Processing') $processingOrders++;
    if ($status === 'Completed')  { $completedOrders++; $totalRevenue += (int)($order['total'] ?? 0); }
    if ($status === 'Cancelled')  $cancelledOrders++;
    if (($order['source'] ?? '') === 'landing_page') $landingOrders++;
    if (str_starts_with($order['date'] ?? '', $todayStr)) $todayOrders++;
}

$recentOrders = array_slice(array_reverse($orders), 0, 8);

include 'includes/header.php';
?>

<!-- Page Header -->
<div class="bg-gradient-to-r from-gray-900 to-gray-700 -mx-4 md:-mx-6 -mt-4 md:-mt-6 px-5 py-6 mb-6 text-white">
    <div class="flex flex-col gap-4">
        <div>
            <h1 class="text-xl font-bold">Dashboard</h1>
            <p class="text-gray-300 text-xs mt-1"><?php echo date('l, F j, Y'); ?></p>
        </div>
        <div class="flex gap-2">
            <a href="add_product.php" class="flex-1 md:flex-none bg-white text-gray-900 hover:bg-gray-100 active:bg-gray-200 text-sm font-bold px-4 py-2.5 rounded-lg flex items-center justify-center gap-2 transition">
                <i class="fas fa-plus"></i> <span>Add Product</span>
            </a>
            <a href="manage_landing.php" class="flex-1 md:flex-none bg-red-600 hover:bg-red-700 active:bg-red-800 text-white text-sm font-bold px-4 py-2.5 rounded-lg flex items-center justify-center gap-2 transition">
                <i class="fas fa-rocket"></i> <span>New Landing</span>
            </a>
        </div>
    </div>
</div>

<!-- Primary Stats Row -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                <i class="fas fa-shopping-bag text-sm"></i>
            </div>
            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">All Time</span>
        </div>
        <p class="text-2xl font-black text-gray-900"><?php echo $totalOrders; ?></p>
        <p class="text-sm text-gray-500 font-medium mt-0.5">Total Orders</p>
        <?php if($landingOrders > 0): ?>
            <p class="text-xs text-blue-500 mt-2 font-bold"><i class="fas fa-rocket mr-1"></i><?php echo $landingOrders; ?> from landing pages</p>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center">
                <i class="fas fa-clock text-sm"></i>
            </div>
            <span class="text-xs font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full">Action Needed</span>
        </div>
        <p class="text-2xl font-black text-gray-900"><?php echo $pendingOrders; ?></p>
        <p class="text-sm text-gray-500 font-medium mt-0.5">Pending Orders</p>
        <?php if($processingOrders > 0): ?>
            <p class="text-xs text-blue-500 mt-2 font-bold"><i class="fas fa-spinner mr-1"></i><?php echo $processingOrders; ?> processing</p>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-green-100 text-green-600 flex items-center justify-center">
                <i class="fas fa-taka-sign text-sm"></i>
            </div>
            <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">Completed</span>
        </div>
        <p class="text-2xl font-black text-gray-900">৳<?php echo number_format($totalRevenue); ?></p>
        <p class="text-sm text-gray-500 font-medium mt-0.5">Total Revenue</p>
        <?php if($completedOrders > 0): ?>
            <p class="text-xs text-green-500 mt-2 font-bold"><i class="fas fa-check mr-1"></i><?php echo $completedOrders; ?> completed orders</p>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                <i class="fas fa-box text-sm"></i>
            </div>
            <span class="text-xs font-bold text-purple-600 bg-purple-50 px-2 py-0.5 rounded-full">Catalog</span>
        </div>
        <p class="text-2xl font-black text-gray-900"><?php echo count($products); ?></p>
        <p class="text-sm text-gray-500 font-medium mt-0.5">Total Products</p>
        <?php if(count($landingPages) > 0): ?>
            <p class="text-xs text-purple-500 mt-2 font-bold"><i class="fas fa-rocket mr-1"></i><?php echo count($landingPages); ?> landing pages live</p>
        <?php endif; ?>
    </div>

</div>

<!-- Order Status Breakdown + Quick Actions -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    <!-- Status Breakdown -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-chart-pie text-gray-400 text-sm"></i> Order Status Breakdown
        </h3>
        <?php
        $statusData = [
            ['label' => 'Pending',    'count' => $pendingOrders,    'color' => 'bg-orange-400', 'text' => 'text-orange-700', 'bg' => 'bg-orange-50'],
            ['label' => 'Processing', 'count' => $processingOrders, 'color' => 'bg-blue-400',   'text' => 'text-blue-700',   'bg' => 'bg-blue-50'],
            ['label' => 'Completed',  'count' => $completedOrders,  'color' => 'bg-green-400',  'text' => 'text-green-700',  'bg' => 'bg-green-50'],
            ['label' => 'Cancelled',  'count' => $cancelledOrders,  'color' => 'bg-red-400',    'text' => 'text-red-700',    'bg' => 'bg-red-50'],
        ];
        foreach ($statusData as $s):
            $pct = $totalOrders > 0 ? round(($s['count'] / $totalOrders) * 100) : 0;
        ?>
        <div class="mb-3">
            <div class="flex justify-between items-center mb-1">
                <span class="text-xs font-bold text-gray-600"><?= $s['label'] ?></span>
                <span class="text-xs font-bold <?= $s['text'] ?>"><?= $s['count'] ?> (<?= $pct ?>%)</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2">
                <div class="<?= $s['color'] ?> h-2 rounded-full transition-all" style="width: <?= $pct ?>%"></div>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if($todayOrders > 0): ?>
        <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between">
            <span class="text-xs text-gray-500 font-medium">Today's orders</span>
            <span class="text-sm font-black text-gray-900"><?= $todayOrders ?></span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-bolt text-gray-400 text-sm"></i> Quick Actions
        </h3>
        <div class="space-y-2">
            <a href="orders.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 border border-gray-100 transition group">
                <div class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center group-hover:bg-orange-200 transition">
                    <i class="fas fa-box-open text-xs"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800">View All Orders</p>
                    <p class="text-xs text-gray-500"><?= $pendingOrders ?> pending review</p>
                </div>
                <i class="fas fa-chevron-right ml-auto text-xs text-gray-400"></i>
            </a>
            <a href="add_product.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 border border-gray-100 transition group">
                <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center group-hover:bg-purple-200 transition">
                    <i class="fas fa-plus text-xs"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800">Add New Product</p>
                    <p class="text-xs text-gray-500"><?= count($products) ?> products in catalog</p>
                </div>
                <i class="fas fa-chevron-right ml-auto text-xs text-gray-400"></i>
            </a>
            <a href="manage_landing.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 border border-gray-100 transition group">
                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center group-hover:bg-blue-200 transition">
                    <i class="fas fa-rocket text-xs"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800">Create Landing Page</p>
                    <p class="text-xs text-gray-500"><?= count($landingPages) ?> pages deployed</p>
                </div>
                <i class="fas fa-chevron-right ml-auto text-xs text-gray-400"></i>
            </a>
            <a href="settings.php" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 border border-gray-100 transition group">
                <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-600 flex items-center justify-center group-hover:bg-gray-200 transition">
                    <i class="fas fa-cog text-xs"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800">Pixel & Settings</p>
                    <p class="text-xs text-gray-500">Tracking codes &amp; config</p>
                </div>
                <i class="fas fa-chevron-right ml-auto text-xs text-gray-400"></i>
            </a>
        </div>
    </div>

    <!-- Live Landing Pages -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-rocket text-gray-400 text-sm"></i> Live Landing Pages
        </h3>
        <?php if(empty($landingPages)): ?>
            <div class="text-center py-6 text-gray-400">
                <i class="fas fa-rocket text-3xl mb-2 block text-gray-200"></i>
                <p class="text-sm">No landing pages yet.</p>
                <a href="manage_landing.php" class="text-sm text-blue-600 hover:underline mt-1 inline-block">Create one →</a>
            </div>
        <?php else: ?>
            <div class="space-y-2">
            <?php foreach(array_slice($landingPages, 0, 5) as $lp): ?>
                <div class="flex items-center justify-between p-2.5 rounded-lg bg-gray-50 border border-gray-100">
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-gray-800 truncate"><?= htmlspecialchars($lp['page_title']) ?></p>
                        <p class="text-xs text-gray-400 font-mono">/landing/<?= htmlspecialchars($lp['slug']) ?></p>
                    </div>
                    <a href="/landing/<?= htmlspecialchars($lp['slug']) ?>/" target="_blank" class="ml-2 flex-shrink-0 text-xs text-blue-600 hover:text-blue-800 font-bold">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
            <?php endforeach; ?>
            </div>
            <?php if(count($landingPages) > 5): ?>
                <a href="landing_pages.php" class="block text-center text-xs text-gray-500 hover:text-gray-700 mt-3">View all <?= count($landingPages) ?> pages →</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>

</div>

<!-- Recent Orders -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-200 flex justify-between items-center">
        <div>
            <h3 class="font-bold text-gray-800">Recent Orders</h3>
            <p class="text-xs text-gray-500 mt-0.5">Last <?= count($recentOrders) ?> orders</p>
        </div>
        <a href="orders.php" class="text-sm text-red-600 hover:text-red-800 font-bold flex items-center gap-1">
            View All <i class="fas fa-arrow-right text-xs"></i>
        </a>
    </div>

    <?php if(empty($recentOrders)): ?>
    <div class="px-5 py-12 text-center text-gray-400">
        <i class="fas fa-inbox text-4xl block mb-2 text-gray-200"></i>
        No orders yet.
    </div>
    <?php else: ?>

    <!-- Desktop table -->
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b border-gray-100 text-xs uppercase text-gray-500 bg-gray-50">
                    <th class="px-5 py-3 font-bold">Order</th>
                    <th class="px-5 py-3 font-bold">Customer</th>
                    <th class="px-5 py-3 font-bold">Date</th>
                    <th class="px-5 py-3 font-bold">Total</th>
                    <th class="px-5 py-3 font-bold">Status</th>
                    <th class="px-5 py-3 font-bold text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($recentOrders as $order):
                    $statusMap = [
                        'Pending'    => 'bg-orange-100 text-orange-700',
                        'Processing' => 'bg-blue-100 text-blue-700',
                        'Completed'  => 'bg-green-100 text-green-700',
                        'Cancelled'  => 'bg-red-100 text-red-700',
                    ];
                    $sc = $statusMap[$order['status'] ?? ''] ?? 'bg-gray-100 text-gray-700';
                ?>
                <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                    <td class="px-5 py-3.5">
                        <div class="font-bold text-gray-900 text-sm">#<?= htmlspecialchars($order['id']) ?></div>
                        <?php if(($order['source'] ?? '') === 'landing_page'): ?>
                            <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded-full border border-blue-100">Landing</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="text-sm font-medium text-gray-800"><?= htmlspecialchars($order['customer']['name'] ?? '—') ?></div>
                        <div class="text-xs text-gray-400"><?= htmlspecialchars($order['customer']['phone'] ?? '') ?></div>
                    </td>
                    <td class="px-5 py-3.5 text-sm text-gray-500"><?= htmlspecialchars($order['date'] ?? '') ?></td>
                    <td class="px-5 py-3.5 font-bold text-gray-900">৳<?= number_format($order['total'] ?? 0) ?></td>
                    <td class="px-5 py-3.5">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold <?= $sc ?>"><?= htmlspecialchars($order['status'] ?? '') ?></span>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <a href="view_order.php?id=<?= htmlspecialchars($order['id']) ?>" class="text-xs font-bold text-gray-500 hover:text-red-600 transition">
                            Details <i class="fas fa-chevron-right text-[10px]"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Mobile card list -->
    <div class="md:hidden divide-y divide-gray-100">
        <?php foreach($recentOrders as $order):
            $statusMap = [
                'Pending'    => 'bg-orange-100 text-orange-700',
                'Processing' => 'bg-blue-100 text-blue-700',
                'Completed'  => 'bg-green-100 text-green-700',
                'Cancelled'  => 'bg-red-100 text-red-700',
            ];
            $sc = $statusMap[$order['status'] ?? ''] ?? 'bg-gray-100 text-gray-700';
        ?>
        <a href="view_order.php?id=<?= htmlspecialchars($order['id']) ?>"
           class="flex items-center gap-3 px-4 py-3.5 hover:bg-gray-50 active:bg-gray-100 transition">
            <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0 text-gray-400">
                <i class="fas fa-box text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-gray-900 text-sm">#<?= htmlspecialchars($order['id']) ?></span>
                    <?php if(($order['source'] ?? '') === 'landing_page'): ?>
                        <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded-full">LP</span>
                    <?php endif; ?>
                </div>
                <div class="text-xs text-gray-500 truncate mt-0.5"><?= htmlspecialchars($order['customer']['name'] ?? '—') ?> · <?= htmlspecialchars($order['date'] ?? '') ?></div>
            </div>
            <div class="flex-shrink-0 text-right">
                <div class="font-bold text-gray-900 text-sm">৳<?= number_format($order['total'] ?? 0) ?></div>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full <?= $sc ?>"><?= htmlspecialchars($order['status'] ?? '') ?></span>
            </div>
            <i class="fas fa-chevron-right text-gray-300 text-xs flex-shrink-0"></i>
        </a>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
