<?php
include_once 'includes/header.php';

$settingsFile = '../settings.json';
$settings = file_exists($settingsFile) ? json_decode(file_get_contents($settingsFile), true) ?? [] : [];

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings['pixel_head']     = $_POST['pixel_head']     ?? '';
    $settings['pixel_body']     = $_POST['pixel_body']     ?? '';
    $settings['pixel_purchase'] = $_POST['pixel_purchase'] ?? '';

    if (file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT))) {
        $success = 'Settings saved successfully.';
    } else {
        $error = 'Failed to save settings. Check file permissions.';
    }
}
?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Site Settings</h1>
    <p class="text-gray-500 text-sm mt-1">Configure tracking pixels and other global options.</p>
</div>

<?php if ($success): ?>
    <div class="bg-green-50 border-l-4 border-green-500 p-3 mb-6 text-sm text-green-700 rounded"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="bg-red-50 border-l-4 border-red-500 p-3 mb-6 text-sm text-red-700 rounded"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST">
    <!-- Pixel Code Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center gap-3">
            <i class="fas fa-code text-blue-500"></i>
            <div>
                <h2 class="font-semibold text-gray-800">Pixel &amp; Tracking Code</h2>
                <p class="text-xs text-gray-500 mt-0.5">Paste your Facebook Pixel, TikTok Pixel, Google Analytics, or any tracking script. Each slot is injected at a specific position in your landing pages.</p>
            </div>
        </div>

        <div class="p-6 space-y-8">

            <!-- @head -->
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <code class="text-xs bg-gray-100 border border-gray-300 text-purple-700 font-mono px-2 py-0.5 rounded">@head</code>
                    <span class="font-semibold text-gray-700 text-sm">Head Pixel (Page View)</span>
                </div>
                <p class="text-xs text-gray-500 mb-2">Injected inside <code class="font-mono bg-gray-100 px-1 rounded">&lt;head&gt;</code> on every landing page. Use for pixel base code &amp; page-view events.</p>
                <textarea
                    name="pixel_head"
                    rows="8"
                    spellcheck="false"
                    class="w-full font-mono text-xs border border-gray-300 rounded p-3 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y"
                    placeholder="<!-- Paste your pixel base code here, e.g. Facebook Pixel initialization -->"
                ><?= htmlspecialchars($settings['pixel_head'] ?? '') ?></textarea>
            </div>

            <!-- @body -->
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <code class="text-xs bg-gray-100 border border-gray-300 text-purple-700 font-mono px-2 py-0.5 rounded">@body</code>
                    <span class="font-semibold text-gray-700 text-sm">Body Open (Noscript Fallback)</span>
                </div>
                <p class="text-xs text-gray-500 mb-2">Injected right after <code class="font-mono bg-gray-100 px-1 rounded">&lt;body&gt;</code> on every landing page. Use for <code class="font-mono bg-gray-100 px-1 rounded">&lt;noscript&gt;</code> pixel fallbacks.</p>
                <textarea
                    name="pixel_body"
                    rows="5"
                    spellcheck="false"
                    class="w-full font-mono text-xs border border-gray-300 rounded p-3 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y"
                    placeholder="<!-- Paste noscript pixel fallback here -->"
                ><?= htmlspecialchars($settings['pixel_body'] ?? '') ?></textarea>
            </div>

            <!-- @purchase -->
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <code class="text-xs bg-gray-100 border border-gray-300 text-green-700 font-mono px-2 py-0.5 rounded">@purchase</code>
                    <span class="font-semibold text-gray-700 text-sm">Purchase / Conversion Event</span>
                </div>
                <p class="text-xs text-gray-500 mb-2">Injected on the order success page after a successful checkout. Use for conversion events like <code class="font-mono bg-gray-100 px-1 rounded">fbq('track', 'Purchase')</code>.</p>
                <textarea
                    name="pixel_purchase"
                    rows="6"
                    spellcheck="false"
                    class="w-full font-mono text-xs border border-gray-300 rounded p-3 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y"
                    placeholder="<!-- Paste your purchase conversion event code here -->"
                ><?= htmlspecialchars($settings['pixel_purchase'] ?? '') ?></textarea>
            </div>

        </div>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="bg-gray-900 hover:bg-gray-700 text-white font-bold px-6 py-2.5 rounded shadow transition-colors flex items-center gap-2">
            <i class="fas fa-save"></i> Save Settings
        </button>
    </div>
</form>

<?php include_once 'includes/footer.php'; ?>
