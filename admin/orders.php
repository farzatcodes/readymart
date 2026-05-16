<?php
$jsonFile = '../orders.json';
$orders = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) ?? [] : [];
include 'includes/header.php';
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Customer Orders</h1>
    <p class="text-gray-500 text-sm mt-1">Manage and update the status of your online store orders.</p>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 text-sm uppercase">
                    <th class="p-4">Order ID</th>
                    <th class="p-4">Customer Details</th>
                    <th class="p-4">Date</th>
                    <th class="p-4">Total Amount</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="6" class="p-12 text-center text-gray-500">
                            <i class="fas fa-box-open text-4xl mb-3 text-gray-300 block"></i>
                            No orders have been placed yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach (array_reverse($orders) as $order): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="p-4">
                                <div class="font-bold text-gray-900">#<?php echo htmlspecialchars($order['id']); ?></div>
                                <?php if(($order['source'] ?? '') === 'landing_page'): ?>
                                    <span class="text-[10px] font-bold bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded-full border border-blue-200">Landing Page</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4">
                                <div class="font-medium text-gray-800"><?php echo htmlspecialchars($order['customer']['name'] ?? ''); ?></div>
                                <div class="text-xs text-gray-500 mt-0.5"><i class="fas fa-phone-alt mr-1"></i><?php echo htmlspecialchars($order['customer']['phone'] ?? ''); ?></div>
                            </td>
                            <td class="p-4 text-gray-600 text-sm">
                                <?php echo htmlspecialchars($order['date']); ?>
                            </td>
                            <td class="p-4 font-bold text-gray-800">৳<?php echo number_format($order['total'] ?? 0, 2); ?></td>
                            <td class="p-4">
                                <?php
                                    $statusColors = [
                                        'Pending'    => 'bg-orange-100 text-orange-800 border border-orange-200',
                                        'Processing' => 'bg-blue-100 text-blue-800 border border-blue-200',
                                        'Completed'  => 'bg-green-100 text-green-800 border border-green-200',
                                        'Cancelled'  => 'bg-red-100 text-red-800 border border-red-200'
                                    ];
                                    $color = $statusColors[$order['status']] ?? 'bg-gray-100 text-gray-800 border border-gray-200';
                                ?>
                                <span class="px-3 py-1.5 rounded text-xs font-bold uppercase shadow-sm <?php echo $color; ?>">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </span>
                            </td>
                            <td class="p-4 text-right">
                                <a href="view_order.php?id=<?php echo htmlspecialchars($order['id']); ?>" class="inline-block bg-gray-100 text-gray-700 hover:bg-[#c8102e] hover:text-white px-4 py-2 rounded shadow-sm transition-colors text-sm font-medium">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>