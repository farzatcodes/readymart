<?php
$settingsFile = __DIR__ . '/settings.json';
$siteSettings = file_exists($settingsFile) ? json_decode(file_get_contents($settingsFile), true) ?? [] : [];
include_once 'includes/header.php';
?>

<main class="bg-[#f0f2f5] min-h-[60vh] flex items-center justify-center py-12">
    <div class="bg-white p-8 rounded-xl shadow-sm max-w-md w-full text-center border border-gray-200">
        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center text-green-500 mx-auto mb-6">
            <i class="fas fa-check-circle text-4xl"></i>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2 bengali-text">অর্ডার সম্পন্ন হয়েছে!</h1>
        <p class="text-gray-600 mb-6">Thank you for your purchase. We have received your order.</p>
        
        <div class="bg-gray-50 p-4 rounded-lg mb-8 border border-gray-100">
            <span class="block text-sm text-gray-500 mb-1">Order ID</span>
            <span class="block text-xl font-bold text-[#cc0000]"><?= htmlspecialchars($_GET['order_id'] ?? 'N/A') ?></span>
        </div>

        <a href="index.php" class="inline-block bg-[#cc0000] text-white font-bold py-3 px-8 rounded-lg hover:bg-red-800 transition-colors">
            Return to Home
        </a>
    </div>
</main>

<script>
    // Empty the user's local storage cart now that the order has been placed successfully
    localStorage.removeItem('readymart_cart');
</script>

<?php if (!empty($siteSettings['pixel_purchase'])): ?>
<!-- @purchase pixel -->
<?php echo $siteSettings['pixel_purchase']; ?>
<?php endif; ?>

<?php include_once 'includes/footer.php'; ?>