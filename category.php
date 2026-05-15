<?php
$slug = isset($_GET['slug']) ? htmlspecialchars($_GET['slug']) : '';

// Map slugs to display names
$categoryMap = [
    'household-items' => 'Household Items',
    'kitchen-items' => 'Kitchen Items',
    'baby-mom' => 'Baby & Mom',
    'lifestyle' => 'Lifestyle & Health',
    'vibration-pleasure' => 'Vibration & Pleasure'
];

$displayTitle = isset($categoryMap[$slug]) ? $categoryMap[$slug] : 'Category Products';

// Load products
$jsonFile = 'products.json';
$allProducts = [];
if (file_exists($jsonFile)) {
    $jsonString = file_get_contents($jsonFile);
    $decoded = json_decode($jsonString, true);
    if (is_array($decoded)) {
        $allProducts = $decoded;
    }
}

// Filter products by the chosen category slug
$categoryProducts = array_filter($allProducts, function($product) use ($slug) {
    return isset($product['category']) && $product['category'] === $slug;
});

include 'includes/header.php';
?>

<!-- Main Content Area -->
<main class="flex-grow max-w-7xl mx-auto px-4 py-8 w-full">
    
    <div class="mb-6 flex items-center justify-between border-b pb-4">
        <h1 class="text-2xl font-bold text-gray-800"><?php echo $displayTitle; ?></h1>
        <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full"><?php echo count($categoryProducts); ?> Items</span>
    </div>

    <?php if (count($categoryProducts) > 0): ?>
        <!-- Product Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <?php foreach ($categoryProducts as $product): ?>
                <?php
                    $id = htmlspecialchars($product['id'] ?? '');
                    $name = htmlspecialchars($product['name'] ?? 'Unknown Product');
                    $price = htmlspecialchars($product['price'] ?? '0');
                    $old_price = htmlspecialchars($product['old_price'] ?? '');
                    $image_url = htmlspecialchars($product['image_url'] ?? 'https://via.placeholder.com/300?text=No+Image');
                    
                    // Calculate discount percentage safely
                    $discount_percentage = 0;
                    if (!empty($old_price) && is_numeric($old_price) && is_numeric($price) && $old_price > $price) {
                        $discount_percentage = round((($old_price - $price) / $old_price) * 100);
                    }
                ?>
                <!-- Single Product Card -->
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-lg transition-all duration-300 group flex flex-col">
                    <a href="product-details.php?id=<?php echo $id; ?>" class="relative block bg-gray-100 aspect-square overflow-hidden">
                        <?php if ($discount_percentage > 0): ?>
                            <span class="absolute top-2 left-2 bg-[#c8102e] text-white text-xs font-bold px-2 py-1 rounded-full z-10">
                                -<?php echo $discount_percentage; ?>%
                            </span>
                        <?php endif; ?>
                        <img src="<?php echo $image_url; ?>" alt="<?php echo $name; ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    </a>
                    <div class="p-4 flex flex-col flex-grow">
                        <a href="product-details.php?id=<?php echo $id; ?>" class="text-sm font-medium text-gray-800 hover:text-[#c8102e] line-clamp-2 mb-2 flex-grow transition-colors">
                            <?php echo $name; ?>
                        </a>
                        <div class="mt-auto">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-[#c8102e] font-bold text-lg">৳ <?php echo $price; ?></span>
                                <?php if (!empty($old_price)): ?>
                                    <span class="text-gray-400 text-sm line-through decoration-gray-400">৳ <?php echo $old_price; ?></span>
                                <?php endif; ?>
                            </div>
                            <button onclick="addToCartGlobal({id: '<?php echo $id; ?>', name: '<?php echo addslashes($name); ?>', price: <?php echo $price; ?>, image: '<?php echo $image_url; ?>'})" class="w-full bg-[#c8102e] text-white py-2 rounded font-medium hover:bg-[#a00c24] hover:shadow-md transition-all active:scale-[0.98] flex justify-center items-center gap-2 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                </svg>
                                Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <!-- Empty State -->
        <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mx-auto text-gray-300 mb-4">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
            </svg>
            <h2 class="text-xl font-bold text-gray-700 mb-2">No Products Found</h2>
            <p class="text-gray-500 mb-6">We couldn't find any products in the "<?php echo $displayTitle; ?>" category right now.</p>
            <a href="index.php" class="inline-block bg-[#c8102e] text-white px-6 py-2.5 rounded-full font-medium hover:bg-[#a00c24] transition-colors">Return to Home</a>
        </div>
    <?php endif; ?>

</main>

<?php include 'includes/footer.php'; ?>