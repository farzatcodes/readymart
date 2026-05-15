<?php
$jsonFile = '../products.json';
$products = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) ?? [] : [];
include 'includes/header.php';
?>

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
    <h1 class="text-2xl font-bold text-gray-800">Products List</h1>
    <a href="add_product.php" class="bg-[#c8102e] text-white px-5 py-2.5 rounded shadow hover:bg-[#a00c24] transition-colors font-medium flex items-center gap-2">
        <i class="fas fa-plus"></i> Add New Product
    </a>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 text-sm uppercase">
                    <th class="p-4 w-16">Image</th>
                    <th class="p-4">Name & Category</th>
                    <th class="p-4">Price</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-500">No products found. Add your first product!</td>
                    </tr>
                <?php else: ?>
                    <?php foreach (array_reverse($products) as $product): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="p-4">
                                <img src="..<?php echo htmlspecialchars($product['image_url']); ?>" class="w-12 h-12 object-cover rounded border border-gray-200 bg-white" alt="Product Image">
                            </td>
                            <td class="p-4">
                                <div class="font-bold text-gray-900"><?php echo htmlspecialchars($product['name']); ?></div>
                                <div class="text-xs text-gray-500 uppercase mt-0.5"><?php echo htmlspecialchars($product['category'] ?? 'Uncategorized'); ?></div>
                            </td>
                            <td class="p-4 font-bold text-gray-800">
                                ৳<?php echo htmlspecialchars($product['price']); ?>
                                <?php if(!empty($product['old_price'])): ?>
                                    <span class="text-xs text-gray-400 line-through font-normal ml-1">৳<?php echo htmlspecialchars($product['old_price']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-right space-x-2 whitespace-nowrap">
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="inline-block text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded transition" title="Edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="delete_product.php?id=<?php echo $product['id']; ?>" onclick="return confirm('Are you sure you want to delete this product?');" class="inline-block text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded transition" title="Delete">
                                    <i class="fas fa-trash"></i> Delete
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