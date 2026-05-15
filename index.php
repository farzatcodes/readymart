<?php
$jsonFile = 'products.json';
$products = [];

if (file_exists($jsonFile)) {
    $jsonData = file_get_contents($jsonFile);
    $products = json_decode($jsonData, true) ?: [];
}

// Expose first product image for LCP preload hint in header
$lcpImageUrl = !empty($products[0]['image_url']) ? $products[0]['image_url'] : null;

include_once 'includes/header.php';
?>

<main class="bg-[#f4f4f4] min-h-screen pb-12">
    <!-- Banner Section -->
    <div class="container mx-auto px-4 py-6">
        <div class="w-full bg-white rounded-xl shadow-sm overflow-hidden relative">
            <div class="w-full h-[200px] md:h-[350px] lg:h-[400px] bg-gradient-to-r from-red-800 to-[#cc0000] flex items-center justify-center relative">
                <div class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]"></div>
                
                <div class="relative z-10 text-center px-4">
                    <span class="inline-block py-1.5 px-4 rounded-full bg-white text-[#cc0000] text-sm font-bold tracking-wider mb-4 uppercase shadow-sm">Grand Opening</span>
                    <h1 class="text-3xl md:text-5xl lg:text-6xl font-black text-white mb-4 drop-shadow-md tracking-tight">Mega Discount Fair</h1>
                    <p class="text-white text-lg md:text-xl mb-8 opacity-95 max-w-2xl mx-auto bengali-text drop-shadow-sm font-medium">আপনার পছন্দের সব পণ্য এখন আরও কম দামে। আজই অর্ডার করুন রেডি মার্ট থেকে!</p>
                    <a href="#products" class="inline-flex items-center gap-2 bg-white text-[#cc0000] font-bold py-3.5 px-8 rounded-full shadow-lg hover:bg-gray-50 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                        Shop Now <i class="fas fa-arrow-down text-sm"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Section -->
    <div id="products" class="container mx-auto px-4 py-8">
        
        <div class="flex items-center justify-between mb-6 pb-3 border-b-2 border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <div class="w-8 h-8 rounded bg-red-100 flex items-center justify-center text-[#cc0000]">
                    <i class="fas fa-box-open"></i>
                </div>
                Trending Products
            </h2>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
            
            <?php if (!empty($products)): ?>
                <?php $productIndex = 0; foreach ($products as $product): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:border-gray-200 transition-all duration-300 group flex flex-col transform hover:-translate-y-1">

                        <div class="relative w-full pt-[100%] overflow-hidden bg-gray-50">
                            <a href="product-details.php?id=<?= htmlspecialchars($product['id']) ?>" class="absolute inset-0 p-2">
                                <img src="<?= htmlspecialchars($product['image_url']) ?>"
                                     alt="<?= htmlspecialchars($product['name']) ?>"
                                     loading="<?= $productIndex < 5 ? 'eager' : 'lazy' ?>"
                                     decoding="async"
                                     class="w-full h-full object-contain mix-blend-multiply group-hover:scale-105 transition-transform duration-500">
                            </a>
                            
                            <?php if (!empty($product['old_price']) && $product['old_price'] > $product['price']): ?>
                                <?php $discount = round((($product['old_price'] - $product['price']) / $product['old_price']) * 100); ?>
                                <div class="absolute top-3 left-3 bg-[#cc0000] text-white text-xs font-bold px-2 py-1 rounded shadow-sm">
                                    -<?= $discount ?>%
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="p-4 flex flex-col flex-grow">
                            <!-- Dynamic Category Added Here -->
                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1.5"><?= htmlspecialchars($product['category'] ?? 'General') ?></span>
                            
                            <a href="product-details.php?id=<?= htmlspecialchars($product['id']) ?>">
                                <h3 class="text-[14px] font-semibold text-gray-800 leading-snug line-clamp-2 hover:text-[#cc0000] transition-colors mb-3 min-h-[42px]">
                                    <?= htmlspecialchars($product['name']) ?>
                                </h3>
                            </a>
                            
                            <div class="mt-auto">
                                <div class="flex items-center gap-2 mb-3.5">
                                    <span class="text-[#cc0000] font-black text-lg">
                                        ৳ <?= number_format($product['price']) ?>
                                    </span>
                                    <?php if (!empty($product['old_price'])): ?>
                                        <span class="text-gray-400 line-through text-sm font-medium">
                                            ৳ <?= number_format($product['old_price']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <button type="button" onclick="addToCart('<?= htmlspecialchars($product['id'], ENT_QUOTES) ?>', '<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>', <?= $product['price'] ?>, '<?= htmlspecialchars($product['image_url'], ENT_QUOTES) ?>')" class="w-full bg-white border-2 border-[#cc0000] text-[#cc0000] hover:bg-[#cc0000] hover:text-white py-2.5 rounded-lg flex items-center justify-center gap-2 text-sm font-bold transition-all duration-300 transform hover:-translate-y-0.5 shadow-sm">
                                    <i class="fas fa-shopping-basket"></i> Add to Cart
                                </button>
                            </div>
                        </div>
                        
                    </div>
                <?php $productIndex++; endforeach; ?>
            <?php else: ?>
                <div class="col-span-full py-16 bg-white rounded-xl border border-gray-100 text-center flex flex-col items-center justify-center shadow-sm">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center text-gray-300 mb-4">
                        <i class="fas fa-box-open text-4xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">No Products Found</h3>
                    <p class="text-gray-500">Add products from the admin panel to see them here.</p>
                </div>
            <?php endif; ?>
            
        </div>
    </div>
</main>

<?php include_once 'includes/footer.php'; ?>