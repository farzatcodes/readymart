<?php
include_once 'includes/header.php';

$settingsFile = '../settings.json';
$settings = file_exists($settingsFile) ? json_decode(file_get_contents($settingsFile), true) ?? [] : [];

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_pixels'])) {
    csrf_verify(); // Bug #16
    $settings['pixel_head']     = $_POST['pixel_head']     ?? '';
    $settings['pixel_body']     = $_POST['pixel_body']     ?? '';
    $settings['pixel_purchase'] = $_POST['pixel_purchase'] ?? '';
    file_put_contents($settingsFile, json_encode($settings, JSON_PRETTY_PRINT))
        ? $success = 'Pixel settings saved successfully.'
        : $error   = 'Failed to save. Check file permissions.';
}
?>

<div class="mb-5">
    <h1 class="text-xl font-bold text-gray-800">Pixel &amp; Tracking</h1>
    <p class="text-gray-500 text-sm mt-1">Inject tracking code (Facebook Pixel, Google Tag, TikTok, etc.) into your landing pages and order success page.</p>
</div>

<?php if ($success): ?>
    <div class="bg-green-50 border-l-4 border-green-500 p-3 mb-5 text-sm text-green-800 rounded-lg flex items-center gap-2">
        <i class="fas fa-check-circle flex-shrink-0"></i> <?= $success ?>
    </div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="bg-red-50 border-l-4 border-red-500 p-3 mb-5 text-sm text-red-800 rounded-lg flex items-center gap-2">
        <i class="fas fa-exclamation-circle flex-shrink-0"></i> <?= $error ?>
    </div>
<?php endif; ?>

<!-- How it works -->
<div class="bg-blue-50 border border-blue-200 rounded-xl px-5 py-4 mb-5 text-sm text-blue-800">
    <p class="font-bold mb-2"><i class="fas fa-info-circle mr-1.5"></i>How it works</p>
    <ul class="space-y-1 text-xs text-blue-700 list-disc list-inside">
        <li><strong>@head</strong> — injected inside <code>&lt;head&gt;</code> on every landing page (base pixel / gtag init)</li>
        <li><strong>@body</strong> — injected right after <code>&lt;body&gt;</code> opens (noscript fallback pixels)</li>
        <li><strong>@purchase</strong> — injected on the order success page only (conversion / purchase event)</li>
    </ul>
</div>

<form method="POST">
    <?= csrf_field() ?>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-code text-sm"></i>
            </div>
            <div>
                <h2 class="font-bold text-gray-800 text-sm">Tracking Code Slots</h2>
                <p class="text-xs text-gray-500">Paste code from Facebook Events Manager, Google Tag Manager, TikTok Pixel, etc.</p>
            </div>
        </div>

        <div class="p-5 space-y-6">
            <?php
            $slots = [
                ['key'=>'pixel_head',     'tag'=>'@head',     'color'=>'purple', 'label'=>'Head — Page View / Init',
                 'desc'=>'Paste the base pixel script here. Injected inside &lt;head&gt; on every landing page.',
                 'placeholder'=>"<!-- Facebook Pixel -->\n<script>\n  !function(f,b,e,v,n,t,s){...}('PIXEL_ID');\n  fbq('track','PageView');\n<\/script>"],
                ['key'=>'pixel_body',     'tag'=>'@body',     'color'=>'indigo', 'label'=>'Body Open — Noscript Fallback',
                 'desc'=>'Paste &lt;noscript&gt; pixel tags here. Injected right after &lt;body&gt; opens.',
                 'placeholder'=>'<noscript><img height="1" width="1" src="https://www.facebook.com/tr?id=PIXEL_ID&ev=PageView&noscript=1"/></noscript>'],
                ['key'=>'pixel_purchase', 'tag'=>'@purchase', 'color'=>'green',  'label'=>'Purchase / Conversion Event',
                 'desc'=>'Fires only on the order success page. Use to track completed purchases.',
                 'placeholder'=>"<script>\n  fbq('track','Purchase',{value:0,currency:'BDT'});\n<\/script>"],
            ];
            foreach ($slots as $s):
            ?>
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <code class="text-xs bg-<?= $s['color'] ?>-100 border border-<?= $s['color'] ?>-200 text-<?= $s['color'] ?>-700 font-mono px-2 py-0.5 rounded-md font-bold"><?= $s['tag'] ?></code>
                    <span class="font-bold text-sm text-gray-700"><?= $s['label'] ?></span>
                </div>
                <p class="text-xs text-gray-400 mb-2"><?= $s['desc'] ?></p>
                <textarea name="<?= $s['key'] ?>" rows="6" spellcheck="false"
                    class="w-full font-mono text-xs border border-gray-200 rounded-lg p-3 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-400 resize-y"
                    placeholder="<?= htmlspecialchars($s['placeholder']) ?>"><?= htmlspecialchars($settings[$s['key']] ?? '') ?></textarea>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50 flex justify-end">
            <button name="save_pixels" type="submit"
                class="bg-gray-900 hover:bg-gray-700 active:bg-black text-white font-bold px-6 py-2.5 rounded-lg shadow text-sm transition flex items-center gap-2">
                <i class="fas fa-save"></i> Save Pixel Settings
            </button>
        </div>
    </div>
</form>

<?php include_once 'includes/footer.php'; ?>
