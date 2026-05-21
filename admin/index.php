<?php
$ordersFile   = '../orders.json';
$productsFile = '../products.json';
$landingFile  = '../landing_pages.json';

$orders       = file_exists($ordersFile)   ? json_decode(file_get_contents($ordersFile),   true) ?? [] : [];
$products     = file_exists($productsFile) ? json_decode(file_get_contents($productsFile), true) ?? [] : [];
$landingPages = file_exists($landingFile)  ? json_decode(file_get_contents($landingFile),  true) ?? [] : [];

$totalOrders      = count($orders);
$totalRevenue     = 0;
$pendingOrders    = 0;
$processingOrders = 0;
$completedOrders  = 0;
$cancelledOrders  = 0;
$holdOrders       = 0;
$landingOrders    = 0;
$todayStr         = date('Y-m-d');
$todayOrders      = 0;

foreach ($orders as $order) {
    $status = $order['status'] ?? '';
    if ($status === 'Pending')    $pendingOrders++;
    if ($status === 'Processing') $processingOrders++;
    if ($status === 'Completed')  { $completedOrders++; $totalRevenue += (int)($order['total'] ?? 0); }
    if ($status === 'Cancelled')  $cancelledOrders++;
    if ($status === 'Hold')       $holdOrders++;
    if (($order['source'] ?? '') === 'landing_page') $landingOrders++;
    if (str_starts_with($order['date'] ?? '', $todayStr)) $todayOrders++;
}

$recentOrders = array_slice(array_reverse($orders), 0, 8);

include 'includes/header.php';
?>

<!-- ═══ MOBILE DASHBOARD ══════════════════════════════════════════════ -->
<div class="md:hidden">

    <!-- Revenue banner -->
    <div class="bg-gray-900 -mx-4 -mt-4 px-5 pt-5 pb-6 mb-4">
        <p class="text-gray-400 text-xs font-semibold uppercase tracking-widest mb-1">Total Revenue</p>
        <p class="text-white text-3xl font-black">৳<?= number_format($totalRevenue) ?></p>
        <p class="text-gray-400 text-xs mt-1"><?= $completedOrders ?> completed orders &nbsp;·&nbsp; <?= date('d M Y') ?></p>
    </div>

    <!-- Status tile grid -->
    <div class="grid grid-cols-2 gap-3 mb-5">
        <?php
        $tiles = [
            ['label'=>'ALL ORDERS',  'count'=>$totalOrders,      'active'=>true,  'href'=>'orders.php'],
            ['label'=>'TODAY',       'count'=>$todayOrders,       'active'=>false, 'href'=>'orders.php?date_from='.date('Y-m-d').'&date_to='.date('Y-m-d')],
            ['label'=>'PENDING',     'count'=>$pendingOrders,     'active'=>false, 'href'=>'orders.php?status=Pending'],
            ['label'=>'ON HOLD',     'count'=>$holdOrders,        'active'=>false, 'href'=>'orders.php?status=Hold'],
            ['label'=>'PROCESSING',  'count'=>$processingOrders,  'active'=>false, 'href'=>'orders.php?status=Processing'],
            ['label'=>'COMPLETED',   'count'=>$completedOrders,   'active'=>false, 'href'=>'orders.php?status=Completed'],
            ['label'=>'CANCELLED',   'count'=>$cancelledOrders,   'active'=>false, 'href'=>'orders.php?status=Cancelled'],
        ];
        foreach ($tiles as $tile):
        ?>
        <a href="<?= $tile['href'] ?>"
           class="<?= $tile['active'] ? 'bg-teal-50 border-teal-200' : 'bg-white border-gray-100' ?> rounded-xl border shadow-sm p-4 text-center active:scale-95 transition-transform block">
            <div class="text-3xl font-black text-teal-600 leading-none mb-1.5"><?= $tile['count'] ?></div>
            <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wide"><?= $tile['label'] ?></div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Products + Landing pages row -->
    <div class="grid grid-cols-2 gap-3 mb-5">
        <a href="products.php"
           class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center active:scale-95 transition-transform block">
            <div class="text-3xl font-black text-purple-600 leading-none mb-1.5"><?= count($products) ?></div>
            <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wide">PRODUCTS</div>
        </a>
        <a href="landing_pages.php"
           class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 text-center active:scale-95 transition-transform block">
            <div class="text-3xl font-black text-blue-600 leading-none mb-1.5"><?= count($landingPages) ?></div>
            <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wide">LANDING PAGES</div>
        </a>
    </div>

    <!-- Recent Orders mobile -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-4">
        <div class="flex items-center justify-between px-4 py-3.5 border-b border-gray-100">
            <div>
                <span class="font-bold text-gray-900 text-sm">Recent Orders</span>
                <span class="text-xs text-gray-400 ml-2">Last <?= count($recentOrders) ?></span>
            </div>
            <a href="orders.php" class="text-xs font-bold text-red-600 flex items-center gap-1">
                View All <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>
        <?php if(empty($recentOrders)): ?>
        <div class="px-4 py-10 text-center text-gray-400 text-sm">
            <i class="fas fa-inbox text-3xl block mb-2 text-gray-200"></i> No orders yet.
        </div>
        <?php else: ?>
        <div class="divide-y divide-gray-100">
        <?php foreach($recentOrders as $order):
            $statusColors = [
                'Pending'    => 'bg-orange-100 text-orange-700',
                'Processing' => 'bg-blue-100 text-blue-700',
                'Completed'  => 'bg-green-100 text-green-700',
                'Cancelled'  => 'bg-red-100 text-red-700',
            ];
            $sc = $statusColors[$order['status'] ?? ''] ?? 'bg-gray-100 text-gray-700';
            $items = $order['items'] ?? [];
            $firstImg = $items[0]['image'] ?? '';
            $phone = $order['customer']['phone'] ?? '';
            $telHref = bd_tel($phone);
        ?>
        <div class="flex items-center gap-3 px-4 py-3 active:bg-gray-50 transition cursor-pointer"
             onclick="location.href='view_order.php?id=<?= htmlspecialchars($order['id']) ?>'">
            <?php if($firstImg): ?>
                <img src="<?= htmlspecialchars($firstImg) ?>"
                     class="w-11 h-11 object-cover rounded-lg border border-gray-200 flex-shrink-0"
                     onerror="this.className='w-11 h-11 bg-gray-100 rounded-lg border border-gray-200 flex-shrink-0'">
            <?php else: ?>
                <div class="w-11 h-11 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-box text-gray-300"></i>
                </div>
            <?php endif; ?>
            <div class="flex-1 min-w-0">
                <div class="font-bold text-gray-900 text-sm">#<?= htmlspecialchars($order['id']) ?></div>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="text-xs text-gray-600 truncate max-w-[100px]"><?= htmlspecialchars($order['customer']['name'] ?? '—') ?></span>
                    <?php if($telHref): ?>
                    <a href="tel:<?= $telHref ?>" onclick="event.stopPropagation()"
                       class="text-xs font-bold text-blue-600 flex-shrink-0">
                        <i class="fas fa-phone-alt text-[10px] mr-0.5"></i><?= htmlspecialchars($phone) ?>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex-shrink-0 text-right">
                <div class="font-bold text-gray-900 text-sm">৳<?= number_format($order['total'] ?? 0) ?></div>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full <?= $sc ?>"><?= $order['status'] ?? '' ?></span>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div><!-- /.md:hidden -->

<!-- ═══ DESKTOP DASHBOARD ════════════════════════════════════════════ -->
<div class="hidden md:block">

<!-- Page Header -->
<div class="bg-gradient-to-r from-gray-900 to-gray-700 -mx-6 -mt-6 px-6 py-8 mb-8 text-white">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold">Dashboard</h1>
            <p class="text-gray-300 text-sm mt-1"><?php echo date('l, F j, Y'); ?> — Store overview</p>
        </div>
        <div class="flex gap-3">
            <a href="add_product.php" class="bg-white text-gray-900 hover:bg-gray-100 text-sm font-bold px-4 py-2 rounded-lg flex items-center gap-2 transition">
                <i class="fas fa-plus"></i> Add Product
            </a>
            <a href="manage_landing.php" class="bg-red-600 hover:bg-red-700 text-white text-sm font-bold px-4 py-2 rounded-lg flex items-center gap-2 transition">
                <i class="fas fa-rocket"></i> New Landing Page
            </a>
        </div>
    </div>
</div>

<!-- Primary Stats Row -->
<div class="grid grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center"><i class="fas fa-shopping-bag text-sm"></i></div>
            <span class="text-xs font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">All Time</span>
        </div>
        <p class="text-2xl font-black text-gray-900"><?= $totalOrders ?></p>
        <p class="text-sm text-gray-500 font-medium mt-0.5">Total Orders</p>
        <?php if($landingOrders > 0): ?><p class="text-xs text-blue-500 mt-2 font-bold"><i class="fas fa-rocket mr-1"></i><?= $landingOrders ?> from landing</p><?php endif; ?>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center"><i class="fas fa-clock text-sm"></i></div>
            <span class="text-xs font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full">Action Needed</span>
        </div>
        <p class="text-2xl font-black text-gray-900"><?= $pendingOrders ?></p>
        <p class="text-sm text-gray-500 font-medium mt-0.5">Pending Orders</p>
        <?php if($processingOrders > 0): ?><p class="text-xs text-blue-500 mt-2 font-bold"><i class="fas fa-spinner mr-1"></i><?= $processingOrders ?> processing</p><?php endif; ?>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-green-100 text-green-600 flex items-center justify-center"><i class="fas fa-taka-sign text-sm"></i></div>
            <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">Completed</span>
        </div>
        <p class="text-2xl font-black text-gray-900">৳<?= number_format($totalRevenue) ?></p>
        <p class="text-sm text-gray-500 font-medium mt-0.5">Total Revenue</p>
        <?php if($completedOrders > 0): ?><p class="text-xs text-green-500 mt-2 font-bold"><i class="fas fa-check mr-1"></i><?= $completedOrders ?> completed</p><?php endif; ?>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center"><i class="fas fa-box text-sm"></i></div>
            <span class="text-xs font-bold text-purple-600 bg-purple-50 px-2 py-0.5 rounded-full">Catalog</span>
        </div>
        <p class="text-2xl font-black text-gray-900"><?= count($products) ?></p>
        <p class="text-sm text-gray-500 font-medium mt-0.5">Total Products</p>
        <?php if(count($landingPages) > 0): ?><p class="text-xs text-purple-500 mt-2 font-bold"><i class="fas fa-rocket mr-1"></i><?= count($landingPages) ?> landing pages</p><?php endif; ?>
    </div>
</div>

<!-- Status Breakdown + Quick Actions + Landing Pages -->
<div class="grid grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2"><i class="fas fa-chart-pie text-gray-400 text-sm"></i> Order Status</h3>
        <?php
        $statusData = [
            ['label'=>'Pending',    'count'=>$pendingOrders,    'color'=>'bg-orange-400', 'text'=>'text-orange-700'],
            ['label'=>'Processing', 'count'=>$processingOrders, 'color'=>'bg-blue-400',   'text'=>'text-blue-700'],
            ['label'=>'Hold',       'count'=>$holdOrders,       'color'=>'bg-yellow-400', 'text'=>'text-yellow-700'],
            ['label'=>'Completed',  'count'=>$completedOrders,  'color'=>'bg-green-400',  'text'=>'text-green-700'],
            ['label'=>'Cancelled',  'count'=>$cancelledOrders,  'color'=>'bg-red-400',    'text'=>'text-red-700'],
        ];
        foreach ($statusData as $s):
            $pct = $totalOrders > 0 ? round(($s['count']/$totalOrders)*100) : 0;
        ?>
        <div class="mb-3">
            <div class="flex justify-between items-center mb-1">
                <span class="text-xs font-bold text-gray-600"><?= $s['label'] ?></span>
                <span class="text-xs font-bold <?= $s['text'] ?>"><?= $s['count'] ?> (<?= $pct ?>%)</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2">
                <div class="<?= $s['color'] ?> h-2 rounded-full" style="width:<?= $pct ?>%"></div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if($todayOrders > 0): ?>
        <div class="mt-4 pt-3 border-t border-gray-100 flex justify-between items-center">
            <span class="text-xs text-gray-500">Today</span>
            <span class="text-sm font-black text-gray-900"><?= $todayOrders ?></span>
        </div>
        <?php endif; ?>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2"><i class="fas fa-bolt text-gray-400 text-sm"></i> Quick Actions</h3>
        <div class="space-y-2">
            <?php $qas = [
                ['href'=>'orders.php',       'icon'=>'fa-box-open',  'bg'=>'bg-orange-100','text'=>'text-orange-600','title'=>'View All Orders',       'sub'=>$pendingOrders.' pending'],
                ['href'=>'add_product.php',  'icon'=>'fa-plus',      'bg'=>'bg-purple-100','text'=>'text-purple-600','title'=>'Add New Product',        'sub'=>count($products).' in catalog'],
                ['href'=>'manage_landing.php','icon'=>'fa-rocket',   'bg'=>'bg-blue-100',  'text'=>'text-blue-600',  'title'=>'Create Landing Page',    'sub'=>count($landingPages).' deployed'],
                ['href'=>'settings.php',     'icon'=>'fa-cog',       'bg'=>'bg-gray-100',  'text'=>'text-gray-600',  'title'=>'Pixel & Settings',       'sub'=>'Tracking & config'],
            ];
            foreach($qas as $qa): ?>
            <a href="<?= $qa['href'] ?>" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 border border-gray-100 transition group">
                <div class="w-8 h-8 rounded-lg <?= $qa['bg'].' '.$qa['text'] ?> flex items-center justify-center">
                    <i class="fas <?= $qa['icon'] ?> text-xs"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-gray-800"><?= $qa['title'] ?></p>
                    <p class="text-xs text-gray-500"><?= $qa['sub'] ?></p>
                </div>
                <i class="fas fa-chevron-right ml-auto text-xs text-gray-400"></i>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
        <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2"><i class="fas fa-rocket text-gray-400 text-sm"></i> Live Landing Pages</h3>
        <?php if(empty($landingPages)): ?>
            <div class="text-center py-6 text-gray-400">
                <i class="fas fa-rocket text-3xl mb-2 block text-gray-200"></i>
                <p class="text-sm">No landing pages yet.</p>
                <a href="manage_landing.php" class="text-sm text-blue-600 hover:underline mt-1 inline-block">Create one →</a>
            </div>
        <?php else: ?>
            <div class="space-y-2">
            <?php foreach(array_slice($landingPages,0,5) as $lp): ?>
                <div class="flex items-center justify-between p-2.5 rounded-lg bg-gray-50 border border-gray-100">
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-gray-800 truncate"><?= htmlspecialchars($lp['page_title']) ?></p>
                        <p class="text-xs text-gray-400 font-mono">/landing/<?= htmlspecialchars($lp['slug']) ?></p>
                    </div>
                    <a href="/landing/<?= htmlspecialchars($lp['slug']) ?>/" target="_blank"
                       class="ml-2 flex-shrink-0 text-xs text-blue-600 hover:text-blue-800 font-bold">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
            <?php endforeach; ?>
            </div>
            <?php if(count($landingPages)>5): ?>
                <a href="landing_pages.php" class="block text-center text-xs text-gray-500 hover:text-gray-700 mt-3">View all <?= count($landingPages) ?> →</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Orders desktop table -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-200 flex justify-between items-center">
        <div>
            <h3 class="font-bold text-gray-800">Recent Orders</h3>
            <p class="text-xs text-gray-500 mt-0.5">Last <?= count($recentOrders) ?> orders placed</p>
        </div>
        <a href="orders.php" class="text-sm text-red-600 hover:text-red-800 font-bold flex items-center gap-1">
            View All <i class="fas fa-arrow-right text-xs"></i>
        </a>
    </div>
    <?php if(empty($recentOrders)): ?>
    <div class="px-5 py-12 text-center text-gray-400">
        <i class="fas fa-inbox text-4xl block mb-2 text-gray-200"></i> No orders yet.
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
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
                    <?php if(($order['source'] ?? '')==='landing_page'): ?>
                        <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded-full border border-blue-100">Landing</span>
                    <?php endif; ?>
                </td>
                <td class="px-5 py-3.5">
                    <div class="text-sm font-medium text-gray-800"><?= htmlspecialchars($order['customer']['name'] ?? '—') ?></div>
                    <?php $p = $order['customer']['phone'] ?? ''; ?>
                    <a href="tel:<?= bd_tel($p) ?>"
                       class="text-xs text-blue-600 hover:underline"><?= htmlspecialchars($p) ?></a>
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
    <?php endif; ?>
</div>

</div><!-- /.hidden.md:block desktop -->

<?php include 'includes/footer.php'; ?>
