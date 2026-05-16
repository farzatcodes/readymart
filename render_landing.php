<?php
// This file is called from within /landing/{slug}/index.php
if (!isset($landing_slug)) {
    die("Invalid landing page configuration.");
}

// 1. Fetch Config
$pagesFile = __DIR__ . '/landing_pages.json';
$landingPages = file_exists($pagesFile) ? json_decode(file_get_contents($pagesFile), true) ?? [] : [];

$pageConfig = null;
foreach ($landingPages as $page) {
    if ($page['slug'] === $landing_slug) {
        $pageConfig = $page;
        break;
    }
}
if (!$pageConfig) die("Landing page not found.");

// 2. Fetch Product
$productsFile = __DIR__ . '/products.json';
$products = file_exists($productsFile) ? json_decode(file_get_contents($productsFile), true) ?? [] : [];

$linkedProduct = null;
foreach ($products as $prod) {
    if ($prod['id'] === $pageConfig['product_id']) {
        $linkedProduct = $prod;
        break;
    }
}

$themeColor = htmlspecialchars($pageConfig['theme_color'] ?? '#d0021b');
$phone = htmlspecialchars($pageConfig['contact_phone'] ?? '01896070330');
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageConfig['page_title']); ?> - ReadyMart</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Hind Siliguri', 'Kalpurush', 'Arial', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Local Font Awesome -->
    <link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
    
    <style>
        body { background-color: #f3f4f6; }
        .theme-bg { background-color: <?php echo $themeColor; ?>; }
        .theme-text { color: <?php echo $themeColor; ?>; }
        .theme-border { border-color: <?php echo $themeColor; ?>; }
        .theme-hover:hover { filter: brightness(90%); }
        .checkout-input:focus { border-color: <?php echo $themeColor; ?>; outline: none; box-shadow: 0 0 0 2px <?php echo $themeColor; ?>33; }
    </style>
</head>
<body class="text-gray-800 pb-12">

    <div class="max-w-3xl mx-auto bg-white shadow-2xl min-h-screen overflow-hidden">
        
        <!-- Animated Ticker Header -->
        <?php if(!empty($pageConfig['ticker_text'])): ?>
        <div class="bg-red-50 text-red-600 font-bold py-2 px-4 overflow-hidden text-center text-sm md:text-base border-b border-red-100">
            <span class="inline-block animate-pulse"><?php echo htmlspecialchars($pageConfig['ticker_text']); ?></span>
        </div>
        <?php endif; ?>

        <!-- Logo -->
        <div class="py-4 text-center border-b border-gray-100">
            <img src="/logo.svg" alt="ReadyMart" class="h-10 mx-auto" onerror="this.style.display='none'; document.getElementById('text-logo').style.display='block';">
            <span id="text-logo" style="display:none;" class="text-3xl font-black theme-text tracking-tighter">ReadyMart</span>
        </div>

        <!-- Hero Section -->
        <div class="p-4 md:p-8">
            
            <?php if(!empty($pageConfig['top_subheading'])): ?>
                <h3 class="text-center text-xl md:text-2xl font-bold text-gray-800 mb-3"><?php echo htmlspecialchars($pageConfig['top_subheading']); ?></h3>
            <?php endif; ?>

            <h1 class="text-center text-2xl md:text-4xl font-extrabold text-[#111] leading-snug mb-4">
                <?php echo nl2br(htmlspecialchars($pageConfig['hero_heading'])); ?>
            </h1>

            <?php if(!empty($pageConfig['hero_subheading'])): ?>
                <p class="text-center text-lg font-bold text-gray-700 mb-6">
                    <?php echo nl2br(htmlspecialchars($pageConfig['hero_subheading'])); ?>
                </p>
            <?php endif; ?>

            <!-- Hero Image -->
            <?php $displayImage = !empty($pageConfig['hero_image']) ? $pageConfig['hero_image'] : ($linkedProduct['image_url'] ?? ''); ?>
            <?php if($displayImage): ?>
                <img src="<?php echo htmlspecialchars($displayImage); ?>" alt="Offer Image" class="w-full max-w-lg mx-auto rounded-lg shadow-md mb-8 object-contain bg-gray-50 border border-gray-100">
            <?php endif; ?>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row justify-center items-center gap-4 mb-8">
                <button onclick="scrollToCheckout()" class="w-full sm:w-auto theme-bg theme-hover text-white font-bold text-xl px-8 py-3.5 rounded shadow-lg transform transition-transform hover:scale-105 flex items-center justify-center gap-2">
                    <i class="fas fa-shopping-cart"></i> এখনই অর্ডার করুন
                </button>
                <a href="tel:<?php echo $phone; ?>" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white font-bold text-xl px-8 py-3.5 rounded shadow-lg transform transition-transform hover:scale-105 flex items-center justify-center gap-2">
                    <i class="fas fa-phone-alt"></i> Call: <?php echo $phone; ?>
                </a>
            </div>

            <!-- Highlight Offer Box -->
            <?php if(!empty($pageConfig['offer_text'])): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-5 text-center mb-10 shadow-sm">
                <h3 class="text-red-600 font-bold text-xl md:text-2xl leading-snug">
                    <?php echo htmlspecialchars($pageConfig['offer_text']); ?>
                </h3>
                <div class="mt-4 flex justify-center gap-4 text-center">
                    <div class="bg-white px-3 py-2 rounded border border-red-100 shadow-sm"><span class="block text-2xl font-black theme-text">01</span><span class="text-xs font-bold">Days</span></div>
                    <div class="bg-white px-3 py-2 rounded border border-red-100 shadow-sm"><span class="block text-2xl font-black theme-text">12</span><span class="text-xs font-bold">Hours</span></div>
                    <div class="bg-white px-3 py-2 rounded border border-red-100 shadow-sm"><span class="block text-2xl font-black theme-text">45</span><span class="text-xs font-bold">Mins</span></div>
                </div>
                <button onclick="scrollToCheckout()" class="mt-5 theme-bg text-white font-bold px-6 py-2 rounded shadow hover:bg-red-700 text-sm">
                    <i class="fas fa-shopping-cart"></i> অর্ডার করতে চাই
                </button>
            </div>
            <?php endif; ?>

            <!-- Features Section -->
            <?php if(!empty($pageConfig['features']) && count($pageConfig['features']) > 0 && $pageConfig['features'][0] !== ""): ?>
            <div class="mb-10">
                <h2 class="text-2xl font-bold mb-4 flex items-center gap-2">
                    <span>📌</span> ব্যবহারের সুবিধা সমুহ-
                </h2>
                <div class="w-full h-1 bg-gray-100 mb-6 relative"><div class="absolute left-0 top-0 h-1 w-16 theme-bg"></div></div>
                
                <div class="space-y-4">
                    <?php foreach($pageConfig['features'] as $feature): if(trim($feature) === '') continue; ?>
                    <div class="flex items-start gap-3 bg-gray-50 p-4 rounded-lg border border-gray-100 shadow-sm">
                        <span class="text-green-500 text-xl mt-0.5"><i class="fas fa-check-circle"></i></span>
                        <span class="font-bold text-lg text-gray-800 leading-snug"><?php echo htmlspecialchars(trim($feature)); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="text-center mt-6">
                    <button onclick="scrollToCheckout()" class="theme-bg text-white font-bold px-8 py-3 rounded shadow-md hover:bg-red-700">
                        <i class="fas fa-shopping-cart"></i> অর্ডার করতে চাই
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <!-- Product Gallery Carousel -->
            <?php if(!empty($linkedProduct['gallery']) && count($linkedProduct['gallery']) > 0): ?>
            <div class="mb-12">
                <h2 class="text-2xl font-bold mb-4 flex items-center gap-2">
                    <span>📌</span> প্রোডাক্টের কিছু ছবি-
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <?php foreach(array_slice($linkedProduct['gallery'], 0, 6) as $img): ?>
                        <div class="aspect-square bg-gray-100 rounded overflow-hidden border border-gray-200">
                            <img src="<?php echo htmlspecialchars($img); ?>" class="w-full h-full object-cover">
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-6">
                    <button onclick="scrollToCheckout()" class="theme-bg text-white font-bold px-8 py-3 rounded shadow-md hover:bg-red-700">
                        <i class="fas fa-shopping-cart"></i> অর্ডার করতে চাই
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <!-- Checkout Form Section -->
            <div id="checkout-section" class="bg-white rounded-xl border border-gray-200 shadow-xl overflow-hidden mt-8">
                <div class="bg-gray-50 p-6 border-b border-gray-200 text-center">
                    <h2 class="text-2xl font-black text-gray-900">এখানে অর্ডার করুন!</h2>
                    <p class="text-sm font-bold mt-2 text-gray-600">
                        সঠিক ভাবে পণ্য অর্ডার করতে, অনুগ্রহ করে আপনার সম্পূর্ণ নাম, মোবাইল নম্বর, সম্পূর্ণ ঠিকানা লিখুন, এবং <span class="theme-text">"অর্ডার কনফার্ম করুন"</span> বাটনে ক্লিক করুন।
                    </p>
                </div>

                <form action="/process_checkout.php" method="POST" class="p-6 md:p-8" id="landing-checkout-form">
                    
                    <input type="hidden" name="cart_data" id="landing_cart_data">
                    <input type="hidden" name="payment_method" value="cod">

                    <div class="mb-8 border border-red-100 bg-red-50/30 rounded-lg p-4">
                        <h3 class="font-bold text-gray-800 mb-3 border-b pb-2">যেকোনো একটি প্যাকেজ নির্বাচন করুন-</h3>
                        <div class="flex items-center gap-4 bg-white p-3 rounded shadow-sm border border-red-200 relative overflow-hidden">
                            <?php if(!empty($linkedProduct['old_price']) && $linkedProduct['old_price'] > $linkedProduct['price']): ?>
                                <div class="absolute top-0 right-0 bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded-bl">
                                    সেভ ৳ <?php echo ($linkedProduct['old_price'] - $linkedProduct['price']); ?>
                                </div>
                            <?php endif; ?>
                            
                            <img src="<?php echo htmlspecialchars($displayImage); ?>" class="w-20 h-20 object-cover rounded border border-gray-100 bg-gray-50">
                            <div class="flex-1">
                                <h4 class="font-bold text-gray-900 leading-tight"><?php echo htmlspecialchars($linkedProduct['name']); ?></h4>
                                <div class="mt-1 flex items-end gap-2">
                                    <span class="text-lg font-black theme-text">৳ <?php echo $linkedProduct['price']; ?></span>
                                    <?php if(!empty($linkedProduct['old_price'])): ?>
                                        <span class="text-xs text-gray-400 line-through font-bold">৳ <?php echo $linkedProduct['old_price']; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 mb-8">
                        <h3 class="font-bold text-gray-800 border-b pb-2">Billing details</h3>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">আপনার নাম <span class="text-red-500">*</span></label>
                            <input type="text" name="billing_name" required placeholder="নাম লিখুন" class="checkout-input w-full p-3 border border-gray-300 rounded bg-gray-50">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">ফোন নাম্বার <span class="text-red-500">*</span></label>
                            <input type="tel" name="billing_phone" required placeholder="+88" class="checkout-input w-full p-3 border border-gray-300 rounded bg-gray-50">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">ডেলিভারি ঠিকানা <span class="text-red-500">*</span></label>
                            <input type="text" name="billing_address" required placeholder="গ্রাম, থানা, জেলাসহ সম্পূর্ণ ঠিকানা" class="checkout-input w-full p-3 border border-gray-300 rounded bg-gray-50">
                        </div>
                    </div>

                    <div class="mb-8">
                        <h3 class="font-bold text-gray-800 border-b pb-2 mb-3">ডেলিভারি এরিয়া সিলেক্ট করুন</h3>
                        <div class="space-y-3">
                            <label class="flex items-center p-3 border border-gray-200 rounded cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="radio" name="delivery_zone" value="outside_dhaka" checked class="w-5 h-5 text-red-600 focus:ring-red-500" onchange="updateTotal()">
                                <span class="ml-3 font-bold text-gray-700">ঢাকার বাইরের: <span class="text-gray-900 font-black">৳ 120.00</span></span>
                            </label>
                            <label class="flex items-center p-3 border border-gray-200 rounded cursor-pointer hover:bg-gray-50 transition-colors">
                                <input type="radio" name="delivery_zone" value="inside_dhaka" class="w-5 h-5 text-red-600 focus:ring-red-500" onchange="updateTotal()">
                                <span class="ml-3 font-bold text-gray-700">ঢাকার ভিতরে: <span class="text-gray-900 font-black">৳ 60.00</span></span>
                            </label>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-5 rounded border border-gray-200 mb-6">
                        <div class="flex justify-between items-center mb-2 font-bold text-gray-600">
                            <span>Subtotal</span>
                            <span>৳ <?php echo $linkedProduct['price']; ?></span>
                        </div>
                        <div class="flex justify-between items-center mb-4 font-bold text-gray-600">
                            <span>Shipment</span>
                            <span id="display_shipping">৳ 120.00</span>
                        </div>
                        <div class="flex justify-between items-center pt-4 border-t border-gray-300 font-black text-xl text-gray-900">
                            <span>Total</span>
                            <span class="theme-text" id="display_total">৳ 0.00</span>
                        </div>
                    </div>

                    <div class="bg-gray-100 p-4 rounded border border-gray-300 mb-6">
                        <div class="flex items-center gap-2 font-bold text-gray-800 mb-1">
                            <input type="radio" checked disabled class="w-4 h-4 text-gray-600">
                            <span>ক্যাশ অন ডেলিভেরি</span>
                        </div>
                        <p class="text-sm text-gray-600 ml-6">আমি অবশ্যই পণ্যটি রিসিভ করবো, ইনশাআল্লাহ!</p>
                    </div>

                    <button type="submit" class="w-full theme-bg theme-hover text-white font-black text-lg py-4 rounded shadow-lg flex justify-center items-center gap-2 transform transition-transform active:scale-[0.98]">
                        <i class="fas fa-lock"></i> অর্ডার কনফার্ম করুন <span id="btn_total"></span>
                    </button>

                </form>
            </div>

            <!-- Footer Links -->
            <div class="mt-12 pt-8 border-t border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <ul class="space-y-3 font-bold text-gray-600">
                            <li class="flex items-center gap-3"><i class="fas fa-map-marker-alt text-gray-400"></i> Mirpur 10, Dhaka, Bangladesh</li>
                            <li><a href="tel:<?php echo $phone; ?>" class="flex items-center gap-3 hover:text-[#d0021b] transition"><i class="fas fa-phone-alt text-gray-400"></i> <?php echo $phone; ?></a></li>
                            <li><a href="mailto:info@readymartbd.com" class="flex items-center gap-3 hover:text-[#d0021b] transition"><i class="fas fa-envelope-open text-gray-400"></i> info@readymartbd.com</a></li>
                        </ul>
                    </div>
                    <div>
                        <ul class="space-y-3 font-bold text-gray-600">
                            <li><a href="/" class="flex items-center gap-3 hover:text-[#d0021b] transition"><i class="fas fa-home text-gray-400"></i> Home Page</a></li>
                            <li><a href="/" class="flex items-center gap-3 hover:text-[#d0021b] transition"><i class="fas fa-link text-gray-400"></i> Refund & Returns Policy</a></li>
                            <li><a href="#" class="flex items-center gap-3 hover:text-[#d0021b] transition"><i class="fab fa-facebook-square text-gray-400"></i> Facebook Page</a></li>
                        </ul>
                    </div>
                </div>
                <div class="text-center font-bold text-sm text-gray-500">
                    &copy; <?php echo date('Y'); ?> ReadyMart. All Rights Reserved By <span class="theme-text">ReadyMart.</span>
                </div>
            </div>

        </div>
    </div>

    <script>
        const productPrice = <?php echo $linkedProduct['price']; ?>;
        
        document.getElementById('landing_cart_data').value = JSON.stringify([{
            id: "<?php echo $linkedProduct['id']; ?>",
            name: "<?php echo addslashes($linkedProduct['name']); ?>",
            price: productPrice,
            image: "<?php echo addslashes($displayImage); ?>",
            quantity: 1
        }]);

        function updateTotal() {
            const shippingRadios = document.getElementsByName('delivery_zone');
            let shippingCost = 120;
            
            for (const radio of shippingRadios) {
                if (radio.checked) {
                    shippingCost = radio.value === 'inside_dhaka' ? 60 : 120;
                    break;
                }
            }

            const total = productPrice + shippingCost;
            
            document.getElementById('display_shipping').innerText = "৳ " + shippingCost.toFixed(2);
            document.getElementById('display_total').innerText = "৳ " + total.toFixed(2);
            document.getElementById('btn_total').innerText = " ৳ " + total.toFixed(2);
        }

        function scrollToCheckout() {
            document.getElementById('checkout-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
            document.querySelector('input[name="billing_name"]').focus();
        }

        updateTotal();
    </script>
</body>
</html>