<?php
include_once 'includes/header.php';

$ordersFile = '../orders.json';
$orders     = file_exists($ordersFile) ? json_decode(file_get_contents($ordersFile), true) ?? [] : [];

// Build customer map keyed by phone
$customerMap = [];
foreach ($orders as $order) {
    $phone   = trim($order['customer']['phone'] ?? '');
    $name    = trim($order['customer']['name']  ?? '');
    $address = trim($order['customer']['address'] ?? '');
    $total   = (int)($order['total'] ?? 0);
    $date    = $order['date'] ?? '';
    $status  = $order['status'] ?? '';

    if ($phone === '') continue;

    if (!isset($customerMap[$phone])) {
        $customerMap[$phone] = [
            'phone'       => $phone,
            'name'        => $name,
            'address'     => $address,
            'orders'      => 0,
            'spent'       => 0,
            'last_order'  => $date,
            'last_status' => $status,
        ];
    }
    $customerMap[$phone]['orders']++;
    $customerMap[$phone]['spent'] += $total;
    if ($date > $customerMap[$phone]['last_order']) {
        $customerMap[$phone]['last_order']  = $date;
        $customerMap[$phone]['last_status'] = $status;
        $customerMap[$phone]['name']        = $name ?: $customerMap[$phone]['name'];
        $customerMap[$phone]['address']     = $address ?: $customerMap[$phone]['address'];
    }
}

// Sort by last order date desc
uasort($customerMap, fn($a,$b) => strcmp($b['last_order'], $a['last_order']));

$totalCustomers   = count($customerMap);
$repeatCustomers  = count(array_filter($customerMap, fn($c) => $c['orders'] > 1));
?>

<div class="mb-5">
    <h1 class="text-xl font-bold text-gray-800">Customers</h1>
    <p class="text-gray-500 text-sm mt-1"><?= $totalCustomers ?> unique customers from orders</p>
</div>

<!-- Stats row -->
<div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 text-center">
        <div class="text-2xl font-black text-teal-600"><?= $totalCustomers ?></div>
        <div class="text-xs font-bold text-gray-500 uppercase tracking-wide mt-1">Total Customers</div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 text-center">
        <div class="text-2xl font-black text-purple-600"><?= $repeatCustomers ?></div>
        <div class="text-xs font-bold text-gray-500 uppercase tracking-wide mt-1">Repeat Buyers</div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 text-center col-span-2 md:col-span-1">
        <div class="text-2xl font-black text-blue-600"><?= count($orders) ?></div>
        <div class="text-xs font-bold text-gray-500 uppercase tracking-wide mt-1">Total Orders</div>
    </div>
</div>

<?php if (empty($customerMap)): ?>
<div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400">
    <i class="fas fa-users text-5xl mb-3 block text-gray-200"></i>
    No customers yet — orders will populate this list.
</div>
<?php else: ?>

<!-- ── DESKTOP TABLE ──────────────────────────────────────────────── -->
<div class="hidden md:block bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs uppercase text-gray-500">
                    <th class="px-5 py-3 font-bold">Customer</th>
                    <th class="px-5 py-3 font-bold">Phone</th>
                    <th class="px-5 py-3 font-bold">Address</th>
                    <th class="px-5 py-3 font-bold">Orders</th>
                    <th class="px-5 py-3 font-bold">Total Spent</th>
                    <th class="px-5 py-3 font-bold">Last Order</th>
                    <th class="px-5 py-3 font-bold">Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($customerMap as $c):
                $statusColors = [
                    'Pending'    => 'bg-orange-100 text-orange-700',
                    'Processing' => 'bg-blue-100 text-blue-700',
                    'Completed'  => 'bg-green-100 text-green-700',
                    'Cancelled'  => 'bg-red-100 text-red-700',
                ];
                $sc = $statusColors[$c['last_status']] ?? 'bg-gray-100 text-gray-600';
            ?>
            <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-bold text-sm flex-shrink-0">
                            <?= strtoupper(mb_substr($c['name'] ?: '?', 0, 1)) ?>
                        </div>
                        <span class="font-semibold text-gray-800 text-sm"><?= htmlspecialchars($c['name'] ?: '—') ?></span>
                    </div>
                </td>
                <td class="px-5 py-3.5">
                    <a href="tel:<?= bd_tel($c['phone']) ?>" class="text-sm font-bold text-blue-600 hover:underline">
                        <i class="fas fa-phone-alt text-xs mr-1"></i><?= htmlspecialchars($c['phone']) ?>
                    </a>
                </td>
                <td class="px-5 py-3.5 text-sm text-gray-500 max-w-[200px]">
                    <div class="truncate" title="<?= htmlspecialchars($c['address']) ?>"><?= htmlspecialchars($c['address'] ?: '—') ?></div>
                </td>
                <td class="px-5 py-3.5">
                    <span class="font-bold text-gray-900"><?= $c['orders'] ?></span>
                    <?php if($c['orders'] > 1): ?>
                        <span class="ml-1 text-xs font-bold text-purple-600 bg-purple-50 px-1.5 py-0.5 rounded-full">Repeat</span>
                    <?php endif; ?>
                </td>
                <td class="px-5 py-3.5 font-bold text-gray-900">৳<?= number_format($c['spent']) ?></td>
                <td class="px-5 py-3.5 text-xs text-gray-500"><?= htmlspecialchars(substr($c['last_order'], 0, 16)) ?></td>
                <td class="px-5 py-3.5">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold <?= $sc ?>"><?= htmlspecialchars($c['last_status']) ?></span>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ── MOBILE CARD LIST ───────────────────────────────────────────── -->
<div class="md:hidden space-y-3">
<?php foreach ($customerMap as $c):
    $statusColors = [
        'Pending'    => 'bg-orange-100 text-orange-700',
        'Processing' => 'bg-blue-100 text-blue-700',
        'Completed'  => 'bg-green-100 text-green-700',
        'Cancelled'  => 'bg-red-100 text-red-700',
    ];
    $sc = $statusColors[$c['last_status']] ?? 'bg-gray-100 text-gray-600';
?>
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="flex items-center gap-3 px-4 py-3.5">
        <!-- Avatar -->
        <div class="w-11 h-11 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-black text-base flex-shrink-0">
            <?= strtoupper(mb_substr($c['name'] ?: '?', 0, 1)) ?>
        </div>
        <!-- Info -->
        <div class="flex-1 min-w-0">
            <div class="font-bold text-gray-900 text-sm"><?= htmlspecialchars($c['name'] ?: '—') ?></div>
            <a href="tel:<?= bd_tel($c['phone']) ?>"
               class="text-sm font-black text-blue-600 block mt-0.5">
                <i class="fas fa-phone-alt text-xs mr-1"></i><?= htmlspecialchars($c['phone']) ?>
            </a>
            <?php if ($c['address']): ?>
            <div class="text-xs text-gray-400 mt-0.5 line-clamp-1"><?= htmlspecialchars($c['address']) ?></div>
            <?php endif; ?>
        </div>
        <!-- Stats -->
        <div class="flex-shrink-0 text-right">
            <div class="font-black text-gray-900 text-sm">৳<?= number_format($c['spent']) ?></div>
            <div class="text-xs text-gray-400"><?= $c['orders'] ?> order<?= $c['orders']!==1?'s':'' ?></div>
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full <?= $sc ?> mt-0.5 inline-block"><?= $c['last_status'] ?></span>
        </div>
    </div>
    <?php if($c['orders'] > 1): ?>
    <div class="border-t border-gray-100 px-4 py-2 bg-purple-50">
        <span class="text-xs font-bold text-purple-600"><i class="fas fa-star text-[10px] mr-1"></i>Repeat customer — <?= $c['orders'] ?> orders</span>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
</div>

<?php endif; ?>

<?php include_once 'includes/footer.php'; ?>
