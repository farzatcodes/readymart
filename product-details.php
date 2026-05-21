<?php
include_once 'includes/header.php';

$jsonFile = 'products.json';
$product = null;
$products = [];

// Fetch data from JSON
if (file_exists($jsonFile)) {
    $jsonData = file_get_contents($jsonFile);
    $products = json_decode($jsonData, true);

    if (isset($_GET['id'])) {
        $productId = $_GET['id'];
        foreach ($products as $p) {
            if ($p['id'] === $productId) {
                $product = $p;
                break;
            }
        }
    }
}

// Fallback if product not found
if (!$product) {
    $product = [
        "id" => "000",
        "name" => "Men's And Women's Shirts Fixed Non-Slip Shirt Stay Belt(Free Size)",
        "price" => "499",
        "old_price" => "790",
        "image_url" => "image_8cf6a1.png",
        "gallery" => [
            "image_8cf6a1.png",
            "image_8cf6a1.png",
            "image_8cf6a1.png"
        ],
        "description" => "<p>Description not found.</p>",
        "reviews" => []
    ];
} else {
    // Mock old price if missing
    if(!isset($product['old_price'])) {
        $product['old_price'] = (intval($product['price']) + 300);
    }
}
?>

<main class="bg-[#f0f2f5] min-h-screen py-8">
    <div class="container mx-auto px-4 max-w-5xl"> 
        
        <!-- Main Product Card Container -->
        <div class="flex flex-col lg:flex-row gap-6 bg-white p-4 md:p-6 rounded-xl shadow-sm">
            
            <!-- Left Side: Images -->
            <div class="w-full lg:w-[48%] flex flex-col gap-3">
                
                <!-- Main Image Container -->
                <div class="relative bg-white border border-gray-100 rounded-lg overflow-hidden flex items-center justify-center p-2 group shadow-sm">
                    
                    <!-- Left Arrow -->
                    <button class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-white rounded-full flex items-center justify-center text-[#cc0000] shadow-md z-10 hover:bg-gray-50 transition-colors opacity-80 group-hover:opacity-100">
                        <i class="fas fa-arrow-left text-sm"></i>
                    </button>
                    
                    <!-- Main Image -->
                    <img src="<?= htmlspecialchars($product['image_url']) ?>"
                         alt="<?= htmlspecialchars($product['name']) ?>"
                         loading="eager"
                         decoding="async"
                         class="w-full h-auto max-h-[450px] object-contain">

                    <!-- Right Arrow -->
                    <button class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 bg-white rounded-full flex items-center justify-center text-[#cc0000] shadow-md z-10 hover:bg-gray-50 transition-colors opacity-80 group-hover:opacity-100">
                        <i class="fas fa-arrow-right text-sm"></i>
                    </button>
                </div>

                <!-- Thumbnails Row -->
                <div class="flex gap-2 mt-1 flex-wrap">
                    <?php if (!empty($product['gallery']) && is_array($product['gallery'])): ?>
                        <?php foreach ($product['gallery'] as $index => $galleryImage): ?>
                            <div class="w-[70px] h-[70px] border <?= $index === 0 ? 'border-[#cc0000] border-2 shadow-sm' : 'border-gray-200 hover:border-[#cc0000]' ?> rounded overflow-hidden cursor-pointer p-0.5 transition-colors">
                                <img src="<?= htmlspecialchars($galleryImage) ?>" alt="Thumbnail <?= $index + 1 ?>" loading="lazy" decoding="async" class="w-full h-full object-contain <?= $index === 0 ? '' : 'opacity-70' ?>">
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="w-[70px] h-[70px] border-2 border-[#cc0000] shadow-sm rounded overflow-hidden cursor-pointer p-0.5">
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="Thumbnail" class="w-full h-full object-contain">
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Side: Details & Actions -->
            <div class="w-full lg:w-[52%] flex flex-col">
                
                <!-- Title -->
                <h1 class="text-[#2c3e50] font-medium text-xl md:text-[22px] leading-snug mb-2">
                    <?= htmlspecialchars($product['name']) ?>
                </h1>
                
                <!-- Price -->
                <div class="flex items-center gap-3 mb-5">
                    <span class="text-[#e71a3c] font-black text-2xl">
                        Tk <?= number_format((float)$product['price']) ?>
                    </span>
                    <?php if (isset($product['old_price'])): ?>
                        <span class="text-black line-through text-base font-medium opacity-60">
                            Tk <?= number_format((float)$product['old_price']) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Action Buttons Stack -->
                <div class="flex flex-col gap-3 mb-6">
                    <!-- Order Now Button (Adds to cart & redirects to checkout) -->
                    <button type="button" onclick="addToCart('<?= htmlspecialchars($product['id'], ENT_QUOTES) ?>', '<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>', <?= $product['price'] ?>, '<?= htmlspecialchars($product['image_url'], ENT_QUOTES) ?>', 1, true)" class="w-full bg-[#cc0000] hover:bg-red-800 text-white py-3.5 px-4 rounded-lg flex items-center justify-center gap-2 text-[17px] font-bold transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 bengali-text cursor-pointer">
                        <i class="fas fa-shopping-basket"></i> অর্ডার করুন
                    </button>

                    <!-- Add to Cart Button (Adds to cart silently) -->
                    <button type="button" onclick="addToCart('<?= htmlspecialchars($product['id'], ENT_QUOTES) ?>', '<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>', <?= $product['price'] ?>, '<?= htmlspecialchars($product['image_url'], ENT_QUOTES) ?>')" class="w-full bg-[#111] hover:bg-gray-800 text-white py-3 px-4 rounded-lg flex items-center justify-center gap-2 text-[16px] font-bold transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5 bengali-text cursor-pointer">
                        <i class="fas fa-shopping-cart"></i> কার্টে যোগ করুন
                    </button>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <a href="tel:01912135655" class="w-full bg-[#17a2b8] hover:bg-[#138496] text-white py-2.5 px-2 rounded-lg flex items-center justify-center gap-2 text-[14px] font-bold transition-colors shadow-sm bengali-text">
                            <i class="fas fa-phone-alt"></i> 01912135655
                        </a>

                        <a href="tel:01921368880" class="w-full bg-[#ffc107] hover:bg-[#e0a800] text-black py-2.5 px-2 rounded-lg flex items-center justify-center gap-2 text-[14px] font-bold transition-colors shadow-sm bengali-text">
                            <i class="fas fa-phone-alt transform scale-x-[-1]"></i> 01921368880
                        </a>
                    </div>

                    <a href="https://wa.me/8801978242687" target="_blank" class="w-full bg-[#25D366] hover:bg-[#128C7E] text-white py-3 px-4 rounded-lg flex items-center justify-center gap-2 text-[15px] font-bold transition-colors shadow-sm bengali-text">
                        <i class="fab fa-whatsapp text-lg"></i> WHATSAPP এ অর্ডার দিন !
                    </a>
                </div>

                <!-- Delivery Info Table -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg overflow-hidden mb-6 shadow-sm">
                    <div class="bg-white px-4 py-3 border-b border-gray-200">
                        <h3 class="text-center text-gray-800 text-[15px] font-bold bengali-text">কুরিয়ার ডেলিভারি খরচ</h3>
                    </div>
                    <table class="w-full border-collapse">
                        <tbody>
                            <tr class="border-b border-gray-200">
                                <td class="p-3 text-gray-700 bengali-text text-sm w-1/2 bg-white">ঢাকায় ডেলিভারি খরচ</td>
                                <td class="p-3 text-gray-900 text-sm font-bold w-1/2 bg-white text-right">৳ 60.00</td>
                            </tr>
                            <tr>
                                <td class="p-3 text-gray-700 bengali-text text-sm bg-white">ঢাকার বাইরের ডেলিভারি খরচ</td>
                                <td class="p-3 text-gray-900 text-sm font-bold bg-white text-right">৳ 130.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Bengali Instruction Text -->
                <div class="bg-blue-50/50 border border-blue-100 rounded-lg p-4 mb-6">
                    <div class="text-[13px] text-gray-800 leading-relaxed space-y-2 bengali-text font-medium">
                        <p class="flex items-start gap-2"><i class="fas fa-info-circle text-blue-500 mt-0.5 opacity-80"></i> <span>১) উল্লেখিত ডেলিভারি চার্জ ১(এক) কেজি পর্যন্ত ওজনের পন্যের জন্য। পন্যের ওজন বাড়লে ডেলিভারি চার্জ ও বাড়বে।</span></p>
                        <p class="flex items-start gap-2"><i class="fas fa-info-circle text-blue-500 mt-0.5 opacity-80"></i> <span>২) ছবি এবং বর্ণনার সাথে পন্যের মিল থাকা সত্যেও আপনি পন্য গ্রহন করতে না চাইলে কুরিয়ার চার্জ ১৫০ টাকা কুরিয়ার অফিসে প্রদান করে পন্য আমাদের ঠিকানায় রিটার্ন করবেন।</span></p>
                        <p class="flex items-start gap-2"><i class="fas fa-info-circle text-blue-500 mt-0.5 opacity-80"></i> <span>৩) পন্য সংক্রান্ত যেকোনো তথ্যের জন্য আমাদের দেওয়া হোয়াটসঅ্যাপ নাম্বারে যোগাযোগ করুন।</span></p>
                    </div>
                </div>

                <!-- Meta Tags -->
                <div class="flex flex-col gap-2 text-[11px] font-medium text-black uppercase">
                    <div>PRODUCT CODE: <span class="font-bold text-gray-700"><?= htmlspecialchars($product['id']) ?></span></div>
                    <div class="flex items-center gap-1.5">
                        <span class="normal-case text-gray-600">Tags:</span> 
                        <span class="bg-[#28a745] text-white px-2 py-0.5 rounded text-[10px] normal-case font-bold tracking-wide shadow-sm">ReadyMart</span>
                    </div>
                </div>

            </div>
        </div>

        <!-- Dynamic Product Tabs (Description, Policy, Reviews) -->
        <?php include_once 'includes/product-tabs.php'; ?>

        <!-- Related Products Section -->
        <div class="mt-8 mb-4">
            <div class="flex items-center justify-between mb-6 pb-2 border-b border-gray-200">
                <h2 class="text-xl md:text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-boxes text-[#cc0000]"></i> 
                    Related Products
                </h2>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 md:gap-6">
                <?php 
                // Display up to 5 related products (excluding current one)
                if (!empty($products)) {
                    $count = 0;
                    foreach ($products as $relatedProduct) {
                        // Skip current product
                        if (isset($productId) && $relatedProduct['id'] === $productId) {
                            continue;
                        }
                        if ($count >= 5) break;
                        ?>
                        
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl hover:border-gray-200 transition-all duration-300 group flex flex-col transform hover:-translate-y-1">
                            <div class="relative w-full pt-[100%] overflow-hidden bg-gray-50">
                                <a href="product-details.php?id=<?= htmlspecialchars($relatedProduct['id']) ?>" class="absolute inset-0 p-2">
                                    <img src="<?= htmlspecialchars($relatedProduct['image_url']) ?>"
                                         alt="<?= htmlspecialchars($relatedProduct['name']) ?>"
                                         loading="lazy"
                                         decoding="async"
                                         class="w-full h-full object-contain mix-blend-multiply group-hover:scale-105 transition-transform duration-500">
                                </a>
                                
                                <div class="absolute top-3 right-3 flex flex-col gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 translate-x-4 group-hover:translate-x-0">
                                    <button class="w-9 h-9 bg-white rounded-full flex items-center justify-center text-gray-600 hover:text-white hover:bg-[#cc0000] shadow-md transition-colors">
                                        <i class="far fa-heart"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="p-4 flex flex-col flex-grow">
                                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mb-1.5">General</span>
                                <a href="product-details.php?id=<?= htmlspecialchars($relatedProduct['id']) ?>">
                                    <h3 class="text-[14px] font-semibold text-gray-800 leading-snug line-clamp-2 hover:text-[#cc0000] transition-colors mb-3 min-h-[42px]">
                                        <?= htmlspecialchars($relatedProduct['name']) ?>
                                    </h3>
                                </a>
                                
                                <div class="mt-auto">
                                    <div class="flex items-end gap-2 mb-3">
                                        <span class="text-[#cc0000] font-black text-lg leading-none">
                                            ৳ <?= number_format($relatedProduct['price']) ?>
                                        </span>
                                    </div>
                                    <button type="button" onclick="addToCart('<?= htmlspecialchars($relatedProduct['id'], ENT_QUOTES) ?>', '<?= htmlspecialchars($relatedProduct['name'], ENT_QUOTES) ?>', <?= $relatedProduct['price'] ?>, '<?= htmlspecialchars($relatedProduct['image_url'], ENT_QUOTES) ?>', 1, true)" class="w-full bg-[#cc0000] hover:bg-red-800 text-white py-2.5 rounded-lg flex items-center justify-center gap-2 text-sm font-bold transition-all duration-300">
                                        <i class="fas fa-shopping-basket"></i> অর্ডার করুন
                                    </button>
                                </div>
                            </div>
                        </div>

                        <?php
                        $count++;
                    }
                }
                ?>
            </div>
        </div>

    </div>
</main>

<?php
include_once 'includes/footer.php';
?>