<?php
include_once 'includes/header.php';
$jsonFile = '../products.json';
$products = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];

function createSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    if (empty($text)) return 'product-' . time();
    return $text;
}

$idToEdit = $_GET['id'] ?? '';
$productIndex = null;
$productToEdit = null;

foreach ($products as $index => $product) {
    if ($product['id'] === $idToEdit) {
        $productIndex = $index;
        $productToEdit = $product;
        break;
    }
}

if (!$productToEdit) {
    echo "<div class='p-4 text-red-600'>Product not found. <a href='products.php' class='underline'>Back to Products</a></div>";
    include_once 'includes/footer.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $folderName = createSlug($_POST['name']);
    $uploadDir = '../assets/products/' . $folderName . '/';
    $jsonPathPrefix = '/assets/products/' . $folderName . '/';

    // Default to existing images
    $mainImageUrl = $productToEdit['image_url'] ?? '';
    $galleryUrls = $productToEdit['gallery'] ?? [];

    // Handle Main Image Update
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $tmpName = $_FILES['main_image']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['main_image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            $newFileName = 'main_' . time() . '.' . $ext;
            if (move_uploaded_file($tmpName, $uploadDir . $newFileName)) {
                $mainImageUrl = $jsonPathPrefix . $newFileName;
            }
        }
    }

    // Handle Gallery Images Update (Overwrite if new ones provided)
    if (isset($_FILES['gallery']) && !empty($_FILES['gallery']['name'][0])) {
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $newGalleryUrls = []; // Reset gallery if new ones are uploaded
        $fileCount = count($_FILES['gallery']['name']);
        $maxImages = min($fileCount, 5);
        
        for ($i = 0; $i < $maxImages; $i++) {
            if ($_FILES['gallery']['error'][$i] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['gallery']['tmp_name'][$i];
                $ext = strtolower(pathinfo($_FILES['gallery']['name'][$i], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                    $newFileName = 'gallery_' . $i . '_' . time() . '.' . $ext;
                    if (move_uploaded_file($tmpName, $uploadDir . $newFileName)) {
                        $newGalleryUrls[] = $jsonPathPrefix . $newFileName;
                    }
                }
            }
        }
        if (!empty($newGalleryUrls)) {
            $galleryUrls = $newGalleryUrls;
        }
    }

    // Update the array
    $products[$productIndex]['name'] = $_POST['name'];
    $products[$productIndex]['category'] = $_POST['category'];
    $products[$productIndex]['price'] = $_POST['price'];
    $products[$productIndex]['old_price'] = empty($_POST['old_price']) ? null : $_POST['old_price'];
    $products[$productIndex]['image_url'] = $mainImageUrl;
    $products[$productIndex]['gallery'] = $galleryUrls;
    $products[$productIndex]['description'] = $_POST['description'];
    
    file_put_contents($jsonFile, json_encode($products, JSON_PRETTY_PRINT));
    
    header('Location: products.php');
    exit;
}
?>

<div class="mb-4 flex items-center gap-3">
    <a href="products.php" class="text-[#2271b1] hover:underline text-sm">&larr; Back to Products</a>
    <h1 class="text-2xl font-normal text-[#1d2327]">Edit Product</h1>
</div>

<form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="col-span-2 space-y-4">
        <div class="wp-card p-4">
            <div class="mb-4">
                <label class="block text-[13px] font-semibold mb-1">Product Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($productToEdit['name']) ?>" class="wp-input py-2 text-lg" required>
            </div>
            <div>
                <label class="block text-[13px] font-semibold mb-1">Description (HTML allowed)</label>
                <textarea name="description" class="wp-input h-48 py-2" required><?= htmlspecialchars($productToEdit['description'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="wp-card p-4">
            <h2 class="font-semibold text-[14px] mb-3 border-b pb-2">Update Images</h2>
            
            <div class="mb-5">
                <label class="block text-[13px] font-bold mb-2">Replace Main Image <span class="text-gray-400 font-normal">(Leave empty to keep current)</span></label>
                <div class="flex items-center gap-4 mb-2">
                    <img src="../<?= htmlspecialchars($productToEdit['image_url']) ?>" class="w-16 h-16 object-contain border bg-gray-50 rounded">
                    <input type="file" name="main_image" accept="image/jpeg, image/png, image/webp, image/gif" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-[#2271b1] hover:file:bg-blue-100 border border-gray-300 rounded p-1 cursor-pointer">
                </div>
            </div>

            <div>
                <label class="block text-[13px] font-bold mb-2">Replace Gallery Images (Max 5) <span class="text-gray-400 font-normal">(Leave empty to keep current)</span></label>
                <input type="file" name="gallery[]" accept="image/jpeg, image/png, image/webp, image/gif" multiple class="block w-full mb-2 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100 border border-gray-300 rounded p-1 cursor-pointer">
                
                <?php if(!empty($productToEdit['gallery'])): ?>
                    <p class="text-xs text-gray-500 mb-2">Current Gallery:</p>
                    <div class="flex gap-2">
                        <?php foreach($productToEdit['gallery'] as $gal): ?>
                            <img src="../<?= htmlspecialchars($gal) ?>" class="w-12 h-12 object-contain border bg-gray-50 rounded">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="space-y-4">
        <div class="wp-card p-4">
            <h2 class="font-semibold text-[14px] mb-3 border-b pb-2">Publish</h2>
            <button type="submit" class="wp-button w-full py-2 text-base font-bold">Update Product</button>
        </div>
        
        <div class="wp-card p-4">
            <h2 class="font-semibold text-[14px] mb-3 border-b pb-2">Product Data</h2>
            <div class="mb-3">
                <label class="block text-[12px] mb-1">Product ID (Read-only)</label>
                <input type="text" value="<?= htmlspecialchars($productToEdit['id']) ?>" class="wp-input bg-gray-100 text-gray-500" readonly>
            </div>
            <div class="mb-3">
                <label class="block text-[12px] mb-1">Category</label>
                <?php $currentCat = $productToEdit['category'] ?? 'General'; ?>
                <select name="category" class="wp-input" required>
                    <option value="General" <?= $currentCat == 'General' ? 'selected' : '' ?>>General</option>
                    <option value="Kitchen Accessories" <?= $currentCat == 'Kitchen Accessories' ? 'selected' : '' ?>>Kitchen Accessories</option>
                    <option value="Flash Sale" <?= $currentCat == 'Flash Sale' ? 'selected' : '' ?>>Flash Sale</option>
                    <option value="Blender & Mixer" <?= $currentCat == 'Blender & Mixer' ? 'selected' : '' ?>>Blender & Mixer</option>
                    <option value="Health & Beauty" <?= $currentCat == 'Health & Beauty' ? 'selected' : '' ?>>Health & Beauty</option>
                    <option value="Watch" <?= $currentCat == 'Watch' ? 'selected' : '' ?>>Watch</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="block text-[12px] mb-1">Current Price (৳)</label>
                <input type="number" name="price" value="<?= htmlspecialchars($productToEdit['price']) ?>" class="wp-input" required>
            </div>
            <div class="mb-3">
                <label class="block text-[12px] mb-1">Old Price (৳) [Optional]</label>
                <input type="number" name="old_price" value="<?= htmlspecialchars($productToEdit['old_price'] ?? '') ?>" class="wp-input">
            </div>
        </div>
    </div>
</form>

<?php include_once 'includes/footer.php'; ?>