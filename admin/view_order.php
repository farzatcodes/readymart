<?php
include_once 'includes/header.php';

$ordersFile  = '../orders.json';
$orders      = file_exists($ordersFile) ? json_decode(file_get_contents($ordersFile), true) : [];
$orderId     = $_GET['id'] ?? '';
$orderIndex  = null;
$order       = null;

foreach ($orders as $index => $o) {
    if ($o['id'] === $orderId) {
        $orderIndex = $index;
        $order = $o;
        break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status']) && $orderIndex !== null) {
    csrf_verify(); // Bug #16
    $orders[$orderIndex]['status'] = $_POST['status'];
    file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT));
    $order   = $orders[$orderIndex];
    $success = 'Order status updated successfully.';
}

if (!$order) {
    echo "<div class='p-4 text-red-600'>Order not found. <a href='orders.php' class='underline'>← Back</a></div>";
    include_once 'includes/footer.php';
    exit;
}

$statusColors = [
    'Pending'    => 'bg-orange-100 text-orange-700 border-orange-200',
    'Processing' => 'bg-blue-100 text-blue-700 border-blue-200',
    'Completed'  => 'bg-green-100 text-green-700 border-green-200',
    'Cancelled'  => 'bg-red-100 text-red-700 border-red-200',
];
$sc = $statusColors[$order['status'] ?? ''] ?? 'bg-gray-100 text-gray-700 border-gray-200';
?>

<!-- Back + Title -->
<div class="mb-5 flex items-center gap-3">
    <a href="orders.php"
       class="w-9 h-9 flex items-center justify-center rounded-lg bg-white border border-gray-200 text-gray-500 hover:text-gray-800 hover:border-gray-300 transition shadow-sm">
        <i class="fas fa-arrow-left text-sm"></i>
    </a>
    <div class="min-w-0">
        <h1 class="text-lg font-bold text-gray-900 leading-tight">Order #<?= htmlspecialchars($order['id']) ?></h1>
        <div class="flex items-center gap-2 mt-0.5">
            <span class="text-xs px-2 py-0.5 rounded-full border font-bold <?= $sc ?>"><?= htmlspecialchars($order['status'] ?? '') ?></span>
            <?php if(($order['source'] ?? '') === 'landing_page'): ?>
                <span class="text-xs font-bold bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full border border-blue-200">Landing Page</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if(isset($success)): ?>
<div class="mb-4 bg-green-50 border-l-4 border-green-500 text-green-800 text-sm font-medium px-4 py-3 rounded-lg flex items-center gap-2">
    <i class="fas fa-check-circle flex-shrink-0"></i> <?= $success ?>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    <!-- ── Items Ordered ──────────────────────────────────────────── -->
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
            <h2 class="font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-shopping-cart text-gray-400 text-sm"></i> Items Ordered
            </h2>
        </div>

        <!-- Mobile: stacked items -->
        <div class="md:hidden divide-y divide-gray-100">
            <?php foreach($order['items'] as $item): ?>
            <div class="flex items-center gap-3 px-4 py-3">
                <?php if(!empty($item['image'])): ?>
                    <img src="<?= htmlspecialchars($item['image']) ?>"
                         class="w-12 h-12 object-cover rounded-lg border border-gray-200 flex-shrink-0"
                         onerror="this.style.display='none'">
                <?php else: ?>
                    <div class="w-12 h-12 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-box text-gray-300"></i>
                    </div>
                <?php endif; ?>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold text-gray-800 truncate"><?= htmlspecialchars($item['name']) ?></div>
                    <div class="text-xs text-gray-500 mt-0.5">Qty: <?= htmlspecialchars($item['qty']) ?> · ৳<?= number_format($item['price']) ?> each</div>
                </div>
                <div class="flex-shrink-0 font-bold text-gray-900 text-sm">৳<?= number_format($item['total']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Desktop: table -->
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-xs uppercase text-gray-500 border-b border-gray-100">
                        <th class="px-5 py-3 font-bold">Item</th>
                        <th class="px-5 py-3 font-bold">Qty</th>
                        <th class="px-5 py-3 font-bold">Price</th>
                        <th class="px-5 py-3 font-bold text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($order['items'] as $item): ?>
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                        <td class="px-5 py-3.5 text-sm font-medium text-gray-800"><?= htmlspecialchars($item['name']) ?></td>
                        <td class="px-5 py-3.5 text-sm text-gray-600"><?= htmlspecialchars($item['qty']) ?></td>
                        <td class="px-5 py-3.5 text-sm text-gray-600">৳<?= number_format($item['price']) ?></td>
                        <td class="px-5 py-3.5 text-sm font-bold text-gray-800 text-right">৳<?= number_format($item['total']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Order totals -->
        <div class="px-5 py-4 border-t border-gray-100 space-y-2 bg-gray-50">
            <div class="flex justify-between text-sm text-gray-500">
                <span>Subtotal</span>
                <span>৳<?= number_format($order['subtotal']) ?></span>
            </div>
            <div class="flex justify-between text-sm text-gray-500">
                <span>Shipping</span>
                <span>৳<?= number_format($order['shipping_cost']) ?></span>
            </div>
            <div class="flex justify-between text-base font-black text-gray-900 pt-2 border-t border-gray-200">
                <span>Grand Total</span>
                <span class="text-red-600">৳<?= number_format($order['total']) ?></span>
            </div>
        </div>
    </div>

    <!-- ── Sidebar ────────────────────────────────────────────────── -->
    <div class="space-y-5">

        <!-- Update Status -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-sync-alt text-gray-400 text-sm"></i> Update Status
                </h2>
            </div>
            <div class="px-5 py-4">
                <form method="POST" class="flex flex-col gap-3">
                    <?= csrf_field() ?>
                    <select name="status"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white">
                        <option value="Pending"    <?= $order['status']==='Pending'    ?'selected':'' ?>>Pending</option>
                        <option value="Processing" <?= $order['status']==='Processing' ?'selected':'' ?>>Processing</option>
                        <option value="Hold"       <?= $order['status']==='Hold'       ?'selected':'' ?>>Hold</option>
                        <option value="Completed"  <?= $order['status']==='Completed'  ?'selected':'' ?>>Completed</option>
                        <option value="Cancelled"  <?= $order['status']==='Cancelled'  ?'selected':'' ?>>Cancelled</option>
                    </select>
                    <button type="submit"
                            class="w-full bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-bold py-2.5 px-4 rounded-lg transition text-sm">
                        <i class="fas fa-check mr-1.5"></i> Update Status
                    </button>
                </form>
            </div>
        </div>

        <!-- Customer Details -->
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-user text-gray-400 text-sm"></i> Customer
                </h2>
            </div>
            <div class="px-5 py-4 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-bold text-sm flex-shrink-0">
                        <?= strtoupper(substr($order['customer']['name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800 text-sm"><?= htmlspecialchars($order['customer']['name']) ?></div>
                        <a href="tel:<?= bd_tel($order['customer']['phone'] ?? '') ?>"
                           class="text-sm font-bold text-blue-600 hover:underline"><?= htmlspecialchars($order['customer']['phone'] ?? '') ?></a>
                    </div>
                </div>

                <div class="pt-2 border-t border-gray-100">
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1.5">Shipping Address</div>
                    <p class="text-sm text-gray-700 bg-gray-50 rounded-lg p-3 border border-gray-100 leading-relaxed">
                        <?= nl2br(htmlspecialchars($order['customer']['address'])) ?>
                    </p>
                </div>

                <div class="flex items-center justify-between text-sm pt-1">
                    <span class="text-gray-500">Payment</span>
                    <span class="font-semibold text-gray-800"><?= htmlspecialchars($order['payment_method']) ?></span>
                </div>

                <?php if(!empty($order['customer']['comment'])): ?>
                <div class="pt-2 border-t border-gray-100">
                    <div class="text-xs font-bold text-orange-500 uppercase tracking-wide mb-1.5">Customer Note</div>
                    <p class="text-sm text-orange-700 bg-orange-50 rounded-lg p-3 border border-orange-100 leading-relaxed">
                        <?= nl2br(htmlspecialchars($order['customer']['comment'])) ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
