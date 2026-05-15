<?php
$ordersFile = '../orders.json';
$productsFile = '../products.json';

$orders = file_exists($ordersFile) ? json_decode(file_get_contents($ordersFile), true) ?? [] : [];
$products = file_exists($productsFile) ? json_decode(file_get_contents($productsFile), true) ?? [] : [];

$totalOrders = count($orders);
$totalRevenue = 0;
$pendingOrders = 0;

foreach ($orders as $order) {
    if ($order['status'] === 'pending') {
        $pendingOrders++;
    }
    if ($order['status'] === 'completed') {
        $totalRevenue += $order['total_amount'];
    }
}

include 'includes/header.php';
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xl">
            <i class="fas fa-shopping-bag"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">Total Orders</p>
            <h3 class="text-2xl font-bold text-gray-800"><?php echo $totalOrders; ?></h3>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-xl">
            <i class="fas fa-clock"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">Pending Orders</p>
            <h3 class="text-2xl font-bold text-gray-800"><?php echo $pendingOrders; ?></h3>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xl">
            <i class="fas fa-money-bill-wave"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">Total Revenue</p>
            <h3 class="text-2xl font-bold text-gray-800">৳<?php echo number_format($totalRevenue, 2); ?></h3>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-xl">
            <i class="fas fa-box"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">Total Products</p>
            <h3 class="text-2xl font-bold text-gray-800"><?php echo count($products); ?></h3>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="p-6 border-b border-gray-200 flex justify-between items-center bg-gray-50">
        <h3 class="text-lg font-bold text-gray-800">Recent Orders</h3>
        <a href="orders.php" class="text-sm text-[#c8102e] hover:underline font-medium">View All</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-200 text-gray-600 text-sm uppercase bg-white">
                    <th class="p-4">Order ID</th>
                    <th class="p-4">Customer</th>
                    <th class="p-4">Date</th>
                    <th class="p-4">Total</th>
                    <th class="p-4">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $recentOrders = array_slice(array_reverse($orders), 0, 5);
                if (empty($recentOrders)): ?>
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-500">No orders placed yet.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentOrders as $order): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="p-4 font-medium text-gray-900">#<?php echo $order['id']; ?></td>
                            <td class="p-4 text-gray-600"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td class="p-4 text-gray-500 text-sm"><?php echo date('M d, Y', strtotime($order['date'])); ?></td>
                            <td class="p-4 font-bold text-gray-800">৳<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td class="p-4">
                                <?php 
                                    $statusColors = [
                                        'pending' => 'bg-orange-100 text-orange-800',
                                        'processing' => 'bg-blue-100 text-blue-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800'
                                    ];
                                    $color = $statusColors[$order['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase <?php echo $color; ?>">
                                    <?php echo $order['status']; ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>