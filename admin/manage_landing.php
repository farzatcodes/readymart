<?php
$jsonFile = '../landing_pages.json';
$landingPages = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) ?? [] : [];

// Fetch products for the dropdown
$productsFile = '../products.json';
$products = file_exists($productsFile) ? json_decode(file_get_contents($productsFile), true) ?? [] : [];

$pageId = isset($_GET['id']) ? $_GET['id'] : null;
$pageData = null;

if ($pageId) {
    foreach ($landingPages as $lp) {
        if ($lp['id'] === $pageId) {
            $pageData = $lp;
            break;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?: uniqid('lp_');
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['slug'])));
    
    // Prepare Data
    $newData = [
        'id' => $id,
        'slug' => $slug,
        'page_title' => $_POST['page_title'],
        'theme_color' => $_POST['theme_color'],
        'product_id' => $_POST['product_id'],
        'contact_phone' => $_POST['contact_phone'] ?? '',
        'ticker_text' => $_POST['ticker_text'] ?? '',
        'top_subheading' => $_POST['top_subheading'] ?? '',
        'hero_heading' => $_POST['hero_heading'] ?? '',
        'hero_subheading' => $_POST['hero_subheading'] ?? '',
        'offer_text' => $_POST['offer_text'] ?? '',
        'features' => explode("\n", str_replace("\r", "", trim($_POST['features']))),
        'hero_image' => $pageData['hero_image'] ?? ''
    ];

    // Handle Image Upload
    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/landing/' . $slug . '/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $filename = 'hero_' . time() . '.' . pathinfo($_FILES['hero_image']['name'], PATHINFO_EXTENSION);
        $targetFile = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['hero_image']['tmp_name'], $targetFile)) {
            $newData['hero_image'] = '/assets/landing/' . $slug . '/' . $filename;
        }
    }

    // Save to JSON
    $updated = false;
    foreach ($landingPages as $key => $lp) {
        if ($lp['id'] === $id) {
            $landingPages[$key] = $newData;
            $updated = true;
            
            // If slug changed, delete old deployed folder
            if($lp['slug'] !== $slug) {
                $oldDir = '../landing/' . $lp['slug'];
                if(is_dir($oldDir)) {
                    @unlink($oldDir . '/index.php');
                    @rmdir($oldDir);
                }
            }
            break;
        }
    }
    if (!$updated) {
        $landingPages[] = $newData;
    }
    file_put_contents($jsonFile, json_encode($landingPages, JSON_PRETTY_PRINT));

    // DEPLOY LOGIC: Create physical folder and index.php
    $deployDir = '../landing/' . $slug;
    if (!file_exists($deployDir)) {
        mkdir($deployDir, 0777, true);
    }
    // This creates a file that just loads our central renderer, passing its slug!
    $indexContent = "<?php\n\$landing_slug = '" . $slug . "';\nrequire_once '../../render_landing.php';\n?>";
    file_put_contents($deployDir . '/index.php', $indexContent);

    header('Location: landing_pages.php');
    exit;
}

include 'includes/header.php';
?>

<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-800"><?php echo $pageData ? 'Edit Landing Page' : 'Create Landing Page'; ?></h1>
    <a href="landing_pages.php" class="text-gray-500 hover:text-gray-700">Back to List</a>
</div>

<form action="manage_landing.php" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow max-w-5xl overflow-hidden">
    <input type="hidden" name="id" value="<?php echo $pageData ? $pageData['id'] : ''; ?>">
    
    <!-- General Settings Section -->
    <div class="p-6 border-b border-gray-200 bg-gray-50">
        <h2 class="text-lg font-bold text-gray-800 mb-4 border-l-4 border-red-500 pl-3">1. General Settings</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Page Title (Browser Tab)</label>
                <input type="text" name="page_title" required value="<?php echo $pageData ? htmlspecialchars($pageData['page_title']) : ''; ?>" class="w-full border-gray-300 rounded focus:border-red-500 focus:ring-red-500 p-2 border bg-white" placeholder="e.g. Digital Scale Offer">
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">URL Slug</label>
                <input type="text" name="slug" required value="<?php echo $pageData ? htmlspecialchars($pageData['slug']) : ''; ?>" class="w-full border-gray-300 rounded focus:border-red-500 focus:ring-red-500 p-2 border bg-white" placeholder="e.g. digital-scale-offer">
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Theme Color</label>
                <div class="flex items-center gap-2">
                    <input type="color" name="theme_color" value="<?php echo $pageData ? htmlspecialchars($pageData['theme_color']) : '#d0021b'; ?>" class="h-10 w-14 border border-gray-300 rounded cursor-pointer">
                    <span class="text-xs text-gray-500">Primary Color</span>
                </div>
            </div>
            <div class="lg:col-span-2">
                <label class="block text-sm font-bold text-gray-700 mb-1">Select Product</label>
                <select name="product_id" required class="w-full border-gray-300 rounded focus:border-red-500 focus:ring-red-500 p-2 border bg-white">
                    <option value="">-- Choose a Product --</option>
                    <?php foreach($products as $prod): ?>
                        <option value="<?php echo $prod['id']; ?>" <?php echo ($pageData && $pageData['product_id'] == $prod['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($prod['name']) . ' (৳' . $prod['price'] . ')'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs text-gray-500 mt-1">This product's details and gallery will automatically load on the page.</p>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Contact Phone</label>
                <input type="text" name="contact_phone" value="<?php echo $pageData ? htmlspecialchars($pageData['contact_phone'] ?? '') : '01896070330'; ?>" class="w-full border-gray-300 rounded focus:border-red-500 focus:ring-red-500 p-2 border bg-white">
            </div>
        </div>
    </div>

    <!-- Page Content Section -->
    <div class="p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-4 border-l-4 border-red-500 pl-3">2. Page Content & Copy</h2>
        
        <div class="space-y-5">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Top Ticker Text (e.g. ধামাকা অফার! আর মাত্র ১ দিন বাকি 🎁)</label>
                <input type="text" name="ticker_text" value="<?php echo $pageData ? htmlspecialchars($pageData['ticker_text'] ?? '') : '🔥ধামাকা অফার! আর মাত্র ১ দিন বাকি 🎁'; ?>" class="w-full border-gray-300 rounded p-2 border focus:border-red-500">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Top Subheading (e.g. 🌙এবার ঈদে অস্থির অফার🔥)</label>
                <input type="text" name="top_subheading" value="<?php echo $pageData ? htmlspecialchars($pageData['top_subheading'] ?? '') : '🌙এবার ঈদে অস্থির অফার🔥'; ?>" class="w-full border-gray-300 rounded p-2 border focus:border-red-500">
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Main Hero Heading (Large Text)</label>
                <textarea name="hero_heading" rows="2" class="w-full border-gray-300 rounded p-2 border focus:border-red-500" placeholder="কোরবানির ঈদে মাংস মাপার ঝামেলা থেকে মুক্তি..."><?php echo $pageData ? htmlspecialchars($pageData['hero_heading'] ?? '') : ''; ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Hero Subheading (Smaller text under heading)</label>
                <textarea name="hero_subheading" rows="2" class="w-full border-gray-300 rounded p-2 border focus:border-red-500" placeholder="🔥 মাছ, মাংস থেকে শুরু করে..."><?php echo $pageData ? htmlspecialchars($pageData['hero_subheading'] ?? '') : ''; ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Offer Highlight Text</label>
                <input type="text" name="offer_text" value="<?php echo $pageData ? htmlspecialchars($pageData['offer_text'] ?? '') : '🔥অবিশ্বাস্য মূল্যছাড়‼ এবং ক্যাশ-অন ডেলিভারি অফার !'; ?>" class="w-full border-gray-300 rounded p-2 border focus:border-red-500">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Key Features / Benefits (One per line)</label>
                    <textarea name="features" rows="6" class="w-full border-gray-300 rounded p-2 border focus:border-red-500" placeholder="LCD ডিসপ্লে&#10;সহজে বহনযোগ্য&#10;সর্বনিম্ন 10 গ্রাম হতে 50 কেজি পর্যন্ত ওজন মাপা যায়"><?php echo $pageData ? htmlspecialchars(implode("\n", $pageData['features'] ?? [])) : ''; ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">These will appear with green checkmarks (✅) automatically.</p>
                </div>
                
                <div class="bg-gray-50 p-4 border border-gray-200 rounded">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Custom Hero Image (Optional)</label>
                    <?php if($pageData && !empty($pageData['hero_image'])): ?>
                        <img src="..<?php echo $pageData['hero_image']; ?>" class="h-32 object-contain mb-3 bg-white rounded shadow-sm border p-1">
                    <?php endif; ?>
                    <input type="file" name="hero_image" accept="image/*" class="w-full text-sm bg-white p-2 border rounded">
                    <p class="text-xs text-gray-500 mt-2">If left blank, the product's default image will be used.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="p-6 bg-gray-50 border-t border-gray-200 flex justify-end">
        <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded shadow hover:bg-blue-700 transition font-bold text-lg">
            <i class="fas fa-rocket mr-2"></i> Save & Deploy Page
        </button>
    </div>
</form>

<?php include 'includes/footer.php'; ?>