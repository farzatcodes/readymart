<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ordersFile = 'orders.json';
    $orders = [];
    
    if (file_exists($ordersFile)) {
        $orders = json_decode(file_get_contents($ordersFile), true);
    }

    $shipping_cost = isset($_POST['shipping_area']) ? (int)$_POST['shipping_area'] : 150;
    
    // Dynamically parse the cart data sent from checkout.php via Javascript
    $cartData = isset($_POST['cart_data']) ? json_decode($_POST['cart_data'], true) : [];
    
    $subtotal = 0;
    $items = [];
    
    // Loop through dynamic cart data to build the final order arrays safely
    if (is_array($cartData)) {
        foreach ($cartData as $item) {
            $itemTotal = (int)$item['price'] * (int)$item['qty'];
            $subtotal += $itemTotal;
            
            $items[] = [
                'name' => htmlspecialchars($item['name']),
                'qty' => (int)$item['qty'],
                'price' => (int)$item['price'],
                'total' => $itemTotal
            ];
        }
    }

    $total = $subtotal + $shipping_cost;

    $newOrder = [
        'id' => 'ORD-' . rand(10000, 99999), 
        'date' => date('Y-m-d h:i A'),
        'customer' => [
            'name' => htmlspecialchars($_POST['customer_name'] ?? ''),
            'phone' => htmlspecialchars($_POST['customer_phone'] ?? ''),
            'address' => htmlspecialchars($_POST['customer_address'] ?? ''),
            'comment' => htmlspecialchars($_POST['customer_comment'] ?? ''),
        ],
        'shipping_cost' => $shipping_cost,
        'subtotal' => $subtotal,
        'total' => $total,
        'payment_method' => $_POST['payment_method'] ?? 'Cash on Delivery',
        'status' => 'Pending',
        'items' => $items
    ];

    // Add new order to the top of the array
    array_unshift($orders, $newOrder);
    
    // Save to JSON
    file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT));

    // Redirect to success
    header("Location: checkout_success.php?order_id=" . $newOrder['id']);
    exit;
} else {
    header("Location: checkout.php");
    exit;
}
?>