<?php
include_once 'includes/header.php';

$ordersFile = '../orders.json';
$orders = file_exists($ordersFile) ? json_decode(file_get_contents($ordersFile), true) : [];
$orderId = $_GET['id'] ?? '';
$orderIndex = null;
$order = null;

// Find Order
foreach ($orders as $index => $o) {
    if ($o['id'] === $orderId) {
        $orderIndex = $index;
        $order = $o;
        break;
    }
}

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status']) && $orderIndex !== null) {
    $orders[$orderIndex]['status'] = $_POST['status'];
    file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT));
    // Refresh variable
    $order = $orders[$orderIndex];
    $success = "Order status updated successfully.";
}

if (!$order) {
    echo "<div class='p-4 text-red-600'>Order not found. <a href='orders.php' class='underline'>Back to Orders</a></div>";
    include_once 'includes/footer.php';
    exit;
}
?>

<div class="mb-4 flex items-center gap-3">
    <a href="orders.php" class="text-[#2271b1] hover:underline text-sm">&larr; Back</a>
    <h1 class="text-2xl font-normal text-[#1d2327]">Order Details: #<?= htmlspecialchars($order['id']) ?></h1>
</div>

<?php if(isset($success)): ?>
    <div class="bg-green-50 border-l-4 border-green-500 p-3 mb-4 text-sm text-green-700 wp-card border-t-0 border-r-0 border-b-0"><?= $success ?></div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Main Order Info -->
    <div class="col-span-2 space-y-6">
        <div class="wp-card p-0">
            <h2 class="px-4 py-3 border-b border-[#c3c4c7] bg-[#f6f7f7] font-semibold text-[14px]">Items Ordered</h2>
            <table class="wp-table">
                <thead>
                    <tr><th>Item</th><th>Qty</th><th class="text-right">Total</th></tr>
                </thead>
                <tbody>
                    <?php foreach($order['items'] as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= htmlspecialchars($item['qty']) ?></td>
                        <td class="text-right">৳ <?= number_format($item['total']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="border-t-2 border-gray-200">
                        <td colspan="2" class="text-right text-gray-500">Subtotal:</td>
                        <td class="text-right">৳ <?= number_format($order['subtotal']) ?></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-right text-gray-500">Shipping:</td>
                        <td class="text-right">৳ <?= number_format($order['shipping_cost']) ?></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-right font-bold">Grand Total:</td>
                        <td class="text-right font-bold text-[#cc0000] text-lg">৳ <?= number_format($order['total']) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="space-y-6">
        <div class="wp-card p-4">
            <h2 class="font-semibold text-[14px] mb-3 border-b pb-2">Order Status</h2>
            <form method="POST" class="flex flex-col gap-3">
                <select name="status" class="wp-input">
                    <option value="Pending" <?= $order['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="Processing" <?= $order['status'] == 'Processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="Completed" <?= $order['status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="Cancelled" <?= $order['status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                </select>
                <button type="submit" class="wp-button">Update Status</button>
            </form>
        </div>

        <div class="wp-card p-4">
            <h2 class="font-semibold text-[14px] mb-3 border-b pb-2">Customer Details</h2>
            <p class="text-[13px] mb-1"><strong>Name:</strong> <?= htmlspecialchars($order['customer']['name']) ?></p>
            <p class="text-[13px] mb-1"><strong>Phone:</strong> <?= htmlspecialchars($order['customer']['phone']) ?></p>
            <p class="text-[13px] mb-1"><strong>Payment:</strong> <?= htmlspecialchars($order['payment_method']) ?></p>
            
            <h3 class="font-semibold text-[13px] mt-4 mb-1">Shipping Address</h3>
            <p class="text-[13px] text-gray-600 bg-gray-50 p-2 rounded border border-gray-100">
                <?= nl2br(htmlspecialchars($order['customer']['address'])) ?>
            </p>

            <?php if(!empty($order['customer']['comment'])): ?>
                <h3 class="font-semibold text-[13px] mt-4 mb-1">Customer Note</h3>
                <p class="text-[13px] text-orange-700 bg-orange-50 p-2 rounded border border-orange-100">
                    <?= nl2br(htmlspecialchars($order['customer']['comment'])) ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>