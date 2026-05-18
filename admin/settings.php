<?php
include_once 'includes/header.php';

$settingsFile = '../settings.json';
$settings = file_exists($settingsFile) ? json_decode(file_get_contents($settingsFile), true) ?? [] : [];

$saFile      = '../fcm_service_account.json';
$saExists    = file_exists($saFile);
$saProjectId = $saExists ? (json_decode(file_get_contents($saFile), true)['project_id'] ?? null) : null;

$tokensFile    = '../fcm_tokens.json';
$deviceCount   = file_exists($tokensFile) ? count(json_decode(file_get_contents($tokensFile), true) ?? []) : 0;

$success = $error = '';

// ── Handle FCM service account save ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_fcm'])) {
    $raw = trim($_POST['fcm_service_account'] ?? '');
    if ($raw) {
        $parsed = json_decode($raw, true);
        if ($parsed && !empty($parsed['project_id']) && !empty($parsed['private_key'])) {
            file_put_contents($saFile, json_encode($parsed, JSON_PRETTY_PRINT));
            $saExists    = true;
            $saProjectId = $parsed['project_id'];
            $success = 'FCM Service Account saved. Project: <strong>' . htmlspecialchars($saProjectId) . '</strong>';
        } else {
            $error = 'Invalid Service Account JSON. Make sure you copied the full file from Firebase.';
        }
    } else {
        $error = 'Paste the Service Account JSON first.';
    }
}

// ── Handle test notification ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_test'])) {
    require_once '../includes/fcm.php';
    if (!$saExists) {
        $error = 'No Service Account configured yet.';
    } elseif ($deviceCount === 0) {
        $error = 'No devices registered. Install the app and open it once first.';
    } else {
        $sent = fcm_send('✅ Test Notification', 'ReadyMart push notifications are working!', ['test' => '1']);
        $sent ? $success = 'Test notification sent to ' . $deviceCount . ' device(s).'
              : $error   = 'Send failed — check the Service Account JSON and internet access.';
    }
}
?>

<div class="mb-5 flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-gray-800">Settings</h1>
        <p class="text-gray-500 text-sm mt-1">Push notification configuration and Android app setup.</p>
    </div>
    <a href="pixel.php" class="text-sm font-bold text-blue-600 hover:underline flex items-center gap-1">
        <i class="fas fa-code text-xs"></i> Pixel Settings
    </a>
</div>

<?php if ($success): ?>
    <div class="bg-green-50 border-l-4 border-green-500 p-3 mb-5 text-sm text-green-800 rounded"><?= $success ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="bg-red-50 border-l-4 border-red-500 p-3 mb-5 text-sm text-red-800 rounded"><?= $error ?></div>
<?php endif; ?>

<!-- ── FCM Push Notifications ─────────────────────────────────────────── -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center gap-3">
        <i class="fas fa-bell text-orange-500"></i>
        <div>
            <h2 class="font-semibold text-gray-800">Push Notifications (FCM)</h2>
            <p class="text-xs text-gray-500 mt-0.5">Get notified on your Android app whenever a new order is placed.</p>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <?php if ($saExists && $saProjectId): ?>
                <span class="text-xs font-bold bg-green-100 text-green-700 border border-green-200 px-2.5 py-1 rounded-full">
                    <i class="fas fa-check-circle mr-1"></i>Configured
                </span>
            <?php else: ?>
                <span class="text-xs font-bold bg-red-100 text-red-600 border border-red-200 px-2.5 py-1 rounded-full">
                    <i class="fas fa-times-circle mr-1"></i>Not configured
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Status row -->
    <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-gray-100 border-b border-gray-100">
        <div class="px-6 py-4">
            <p class="text-xs text-gray-400 font-bold uppercase tracking-wide mb-1">Firebase Project</p>
            <p class="text-sm font-bold text-gray-800"><?= $saProjectId ? htmlspecialchars($saProjectId) : '—' ?></p>
        </div>
        <div class="px-6 py-4">
            <p class="text-xs text-gray-400 font-bold uppercase tracking-wide mb-1">Registered Devices</p>
            <p class="text-sm font-bold text-gray-800"><?= $deviceCount ?> device<?= $deviceCount !== 1 ? 's' : '' ?></p>
        </div>
        <div class="px-6 py-4 flex items-center">
            <form method="POST">
                <button name="send_test" type="submit"
                    class="<?= ($saExists && $deviceCount > 0) ? 'bg-orange-500 hover:bg-orange-600' : 'bg-gray-200 text-gray-400 cursor-not-allowed' ?> text-white font-bold text-xs px-4 py-2 rounded transition flex items-center gap-2"
                    <?= (!$saExists || $deviceCount === 0) ? 'disabled' : '' ?>>
                    <i class="fas fa-paper-plane"></i> Send Test Notification
                </button>
            </form>
        </div>
    </div>

    <!-- Service account paste -->
    <form method="POST" class="p-6">
        <label class="block text-sm font-bold text-gray-700 mb-1">
            Firebase Service Account JSON
            <span class="font-normal text-gray-400">(from Firebase Console → Project Settings → Service Accounts → Generate new private key)</span>
        </label>
        <textarea name="fcm_service_account" rows="6" spellcheck="false"
            class="w-full font-mono text-xs border border-gray-200 rounded p-3 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-400 resize-y"
            placeholder='{"type":"service_account","project_id":"your-project","private_key":"-----BEGIN RSA PRIVATE KEY-----\n...","client_email":"firebase-adminsdk-...@your-project.iam.gserviceaccount.com",...}'></textarea>
        <p class="text-xs text-red-500 mt-2 flex items-start gap-1">
            <i class="fas fa-lock mt-0.5"></i>
            This file contains a private key. It is stored on the server and never exposed publicly.
            Add <code class="font-mono bg-gray-100 px-1 rounded">fcm_service_account.json</code> to your <code class="font-mono bg-gray-100 px-1 rounded">.gitignore</code>.
        </p>
        <div class="mt-4 flex justify-end">
            <button name="save_fcm" type="submit"
                class="bg-orange-500 hover:bg-orange-600 text-white font-bold px-5 py-2 rounded shadow text-sm transition flex items-center gap-2">
                <i class="fas fa-save"></i> Save Service Account
            </button>
        </div>
    </form>
</div>

<!-- ── APK Setup Guide ─────────────────────────────────────────────────── -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center gap-3">
        <i class="fab fa-android text-green-500 text-lg"></i>
        <h2 class="font-semibold text-gray-800">Android App Setup Guide</h2>
    </div>
    <div class="p-6 space-y-5 text-sm text-gray-700">

        <div class="flex gap-4">
            <div class="w-7 h-7 rounded-full bg-gray-900 text-white flex-shrink-0 flex items-center justify-center text-xs font-black">1</div>
            <div>
                <p class="font-bold">Create a Firebase project</p>
                <p class="text-gray-500 mt-0.5">Go to <strong>console.firebase.google.com</strong> → Add project → give it any name.</p>
            </div>
        </div>

        <div class="flex gap-4">
            <div class="w-7 h-7 rounded-full bg-gray-900 text-white flex-shrink-0 flex items-center justify-center text-xs font-black">2</div>
            <div>
                <p class="font-bold">Add an Android app to the project</p>
                <p class="text-gray-500 mt-0.5">Package name: <code class="font-mono bg-gray-100 px-1 rounded">com.readymart.adminapp</code><br>
                Download <code class="font-mono bg-gray-100 px-1 rounded">google-services.json</code> and place it in <code class="font-mono bg-gray-100 px-1 rounded">android/app/</code></p>
            </div>
        </div>

        <div class="flex gap-4">
            <div class="w-7 h-7 rounded-full bg-gray-900 text-white flex-shrink-0 flex items-center justify-center text-xs font-black">3</div>
            <div>
                <p class="font-bold">Get the Service Account key (for this settings page)</p>
                <p class="text-gray-500 mt-0.5">Firebase Console → Project Settings → <strong>Service Accounts</strong> tab → <strong>Generate new private key</strong> → paste the JSON above.</p>
            </div>
        </div>

        <div class="flex gap-4">
            <div class="w-7 h-7 rounded-full bg-gray-900 text-white flex-shrink-0 flex items-center justify-center text-xs font-black">4</div>
            <div>
                <p class="font-bold">Set your admin URL in the app</p>
                <p class="text-gray-500 mt-0.5">Open <code class="font-mono bg-gray-100 px-1 rounded">android/app/src/main/java/com/readymart/adminapp/MainActivity.java</code> and change <code class="font-mono bg-gray-100 px-1 rounded">ADMIN_URL</code> to your site's admin address.</p>
            </div>
        </div>

        <div class="flex gap-4">
            <div class="w-7 h-7 rounded-full bg-gray-900 text-white flex-shrink-0 flex items-center justify-center text-xs font-black">5</div>
            <div>
                <p class="font-bold">Build the APK in Android Studio</p>
                <p class="text-gray-500 mt-0.5">Open the <code class="font-mono bg-gray-100 px-1 rounded">android/</code> folder in Android Studio → <strong>Build → Build Bundle(s)/APK(s) → Build APK</strong>.</p>
            </div>
        </div>

        <div class="flex gap-4">
            <div class="w-7 h-7 rounded-full bg-gray-900 text-white flex-shrink-0 flex items-center justify-center text-xs font-black">6</div>
            <div>
                <p class="font-bold">Install &amp; open the APK on the admin phone</p>
                <p class="text-gray-500 mt-0.5">Enable "Install unknown apps" on the device, transfer the APK, install it, and open it once. The app registers its FCM token automatically — you'll see the device count above update to 1.</p>
            </div>
        </div>

        <div class="flex gap-4">
            <div class="w-7 h-7 rounded-full bg-green-600 text-white flex-shrink-0 flex items-center justify-center text-xs font-black">✓</div>
            <div>
                <p class="font-bold text-green-700">Every new order will now trigger a push notification</p>
                <p class="text-gray-500 mt-0.5">Tap the notification to open the order detail directly inside the app.</p>
            </div>
        </div>

    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
