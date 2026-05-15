<?php
$jsonFile = '../landing_pages.json';
$landingPages = [];

// Initialize if doesn't exist
if (!file_exists($jsonFile)) {
    file_put_contents($jsonFile, json_encode([]));
} else {
    $landingPages = json_decode(file_get_contents($jsonFile), true) ?? [];
}

include 'includes/header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Landing Pages</h1>
    <a href="manage_landing.php" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition-colors">
        <i class="fas fa-plus mr-2"></i> Create Landing Page
    </a>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 uppercase text-sm">
                <th class="p-4">Page Title</th>
                <th class="p-4">URL Slug</th>
                <th class="p-4">Theme Color</th>
                <th class="p-4 text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($landingPages)): ?>
                <tr>
                    <td colspan="4" class="p-8 text-center text-gray-500">
                        <i class="fas fa-file-alt text-4xl mb-3 text-gray-300 block"></i>
                        No landing pages created yet. Click "Create Landing Page" to start!
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach (array_reverse($landingPages) as $page): ?>
                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                        <td class="p-4 font-medium text-gray-800"><?php echo htmlspecialchars($page['page_title']); ?></td>
                        <td class="p-4 text-gray-600">
                            <span class="bg-gray-100 px-2 py-1 rounded text-sm">/landing/<?php echo htmlspecialchars($page['slug']); ?>/</span>
                        </td>
                        <td class="p-4">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full border border-gray-300" style="background-color: <?php echo htmlspecialchars($page['theme_color']); ?>;"></div>
                                <span class="text-xs text-gray-500 uppercase"><?php echo htmlspecialchars($page['theme_color']); ?></span>
                            </div>
                        </td>
                        <td class="p-4 text-right space-x-2">
                            <?php 
                                // Generate full URL based on current server
                                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
                                $host = $_SERVER['HTTP_HOST'];
                                $basePath = str_replace('/admin/landing_pages.php', '', $_SERVER['PHP_SELF']);
                                $fullUrl = $protocol . $host . $basePath . '/landing/' . $page['slug'] . '/';
                            ?>
                            <button onclick="copyLink('<?php echo $fullUrl; ?>')" class="text-green-500 hover:text-green-700 bg-green-50 px-2 py-1 rounded transition" title="Copy Live Link">
                                <i class="fas fa-link"></i> Copy
                            </button>
                            <a href="<?php echo $fullUrl; ?>" target="_blank" class="text-blue-500 hover:text-blue-700 bg-blue-50 px-2 py-1 rounded transition" title="View Live">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="manage_landing.php?id=<?php echo $page['id']; ?>" class="text-indigo-500 hover:text-indigo-700 bg-indigo-50 px-2 py-1 rounded transition" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete_landing.php?id=<?php echo $page['id']; ?>" onclick="return confirm('Are you sure you want to delete this landing page? The deployed folder will also be removed.');" class="text-red-500 hover:text-red-700 bg-red-50 px-2 py-1 rounded transition" title="Delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function copyLink(url) {
    // Create a temporary input to copy the text
    const tempInput = document.createElement("input");
    tempInput.value = url;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand("copy");
    document.body.removeChild(tempInput);
    alert("Landing page link copied to clipboard:\n" + url);
}
</script>

<?php include 'includes/footer.php'; ?>