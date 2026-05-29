<?php
// Bug #16: bootstrap session + helpers before header.php is loaded
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/includes/functions.php';

$jsonFile = '../orders.json';
$orders   = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) ?? [] : [];

$flashMsg  = '';
$flashType = '';

$validStatuses = ['Pending', 'Processing', 'Completed', 'Cancelled', 'Hold'];

// ── Bulk status update ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_status'], $_POST['order_ids'])) {
    csrf_verify(); // Bug #16
    $newStatus   = $_POST['bulk_status'];
    $selectedIds = (array)$_POST['order_ids'];

    if (in_array($newStatus, $validStatuses) && !empty($selectedIds)) {
        $updated = 0;
        foreach ($orders as &$order) {
            if (in_array($order['id'], $selectedIds)) {
                $order['status'] = $newStatus;
                $updated++;
            }
        }
        unset($order);
        file_put_contents($jsonFile, json_encode($orders, JSON_PRETTY_PRINT));
        $flashMsg  = $updated . ' order' . ($updated !== 1 ? 's' : '') . ' marked as <strong>' . htmlspecialchars($newStatus) . '</strong>.';
        $flashType = 'green';
    } else {
        $flashMsg  = 'Invalid action.';
        $flashType = 'red';
    }
}

// ── Date / status filters ───────────────────────────────────────────────────
// Bug #12: validate date strings to prevent arbitrary string injection
$today  = date('Y-m-d');
// Default to today when landing with no filters at all
$noFiltersSet = !isset($_GET['date_from']) && !isset($_GET['date_to']) && !isset($_GET['status']);
$rawFrom = trim($_GET['date_from'] ?? ($noFiltersSet ? $today : ''));
$rawTo   = trim($_GET['date_to']   ?? ($noFiltersSet ? $today : ''));
$filterDateFrom = (DateTime::createFromFormat('Y-m-d', $rawFrom) !== false) ? $rawFrom : '';
$filterDateTo   = (DateTime::createFromFormat('Y-m-d', $rawTo)   !== false) ? $rawTo   : '';
$filterStatus   = trim($_GET['status'] ?? '');

$ordersDisplay = array_reverse($orders);

if ($filterDateFrom !== '') {
    $ordersDisplay = array_filter($ordersDisplay, fn($o) => substr($o['date'] ?? '', 0, 10) >= $filterDateFrom);
}
if ($filterDateTo !== '') {
    $ordersDisplay = array_filter($ordersDisplay, fn($o) => substr($o['date'] ?? '', 0, 10) <= $filterDateTo);
}
if ($filterStatus !== '' && in_array($filterStatus, $validStatuses)) {
    $ordersDisplay = array_filter($ordersDisplay, fn($o) => ($o['status'] ?? '') === $filterStatus);
}
$ordersDisplay = array_values($ordersDisplay);

$statusColors = [
    'Pending'    => ['pill'=>'bg-orange-100 text-orange-700 border-orange-200', 'dot'=>'bg-orange-400'],
    'Processing' => ['pill'=>'bg-blue-100 text-blue-700 border-blue-200',      'dot'=>'bg-blue-400'],
    'Completed'  => ['pill'=>'bg-green-100 text-green-700 border-green-200',   'dot'=>'bg-green-400'],
    'Cancelled'  => ['pill'=>'bg-red-100 text-red-700 border-red-200',         'dot'=>'bg-red-400'],
    'Hold'       => ['pill'=>'bg-yellow-100 text-yellow-700 border-yellow-200','dot'=>'bg-yellow-400'],
];

include 'includes/header.php';
?>

<div class="mb-4 flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-gray-800">Orders</h1>
        <p class="text-gray-500 text-sm mt-0.5">
            <?= count($ordersDisplay) ?> order<?= count($ordersDisplay)!==1?'s':'' ?>
            <?= ($filterDateFrom||$filterDateTo||$filterStatus) ? '<span class="text-blue-600 font-semibold">(filtered)</span>' : '' ?>
        </p>
    </div>
</div>

<?php if ($flashMsg): ?>
<div class="mb-4 bg-<?= $flashType ?>-50 border-l-4 border-<?= $flashType ?>-500 text-<?= $flashType ?>-800 text-sm font-medium px-4 py-3 rounded-lg flex items-start gap-2">
    <i class="fas fa-<?= $flashType==='green'?'check':'exclamation' ?>-circle mt-0.5 flex-shrink-0"></i>
    <span><?= $flashMsg ?></span>
</div>
<?php endif; ?>

<!-- ── Filter bar ──────────────────────────────────────────────────────────── -->
<form method="GET" action="orders.php" id="filter-form"
      class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-3 mb-4 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-[130px]">
        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">From Date</label>
        <input type="date" name="date_from" id="input-date-from"
               value="<?= htmlspecialchars($filterDateFrom) ?>"
               onchange="document.getElementById('filter-form').submit()"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-800 bg-white">
    </div>
    <div class="flex-1 min-w-[130px]">
        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">To Date</label>
        <input type="date" name="date_to" id="input-date-to"
               value="<?= htmlspecialchars($filterDateTo) ?>"
               onchange="document.getElementById('filter-form').submit()"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-800 bg-white">
    </div>
    <div class="flex-1 min-w-[120px]">
        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Status</label>
        <select name="status" onchange="this.form.submit()"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-800 bg-white">
            <option value="">All Statuses</option>
            <?php foreach($validStatuses as $s): ?>
            <option value="<?= $s ?>" <?= $filterStatus===$s?'selected':'' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="flex gap-2 flex-wrap">
        <button type="button" onclick="setToday()"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-3 py-2 rounded-lg text-sm transition flex items-center gap-1.5">
            <i class="fas fa-calendar-day text-xs"></i> Today
        </button>
        <a href="orders.php?date_from=&date_to=&status="
           class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-3 py-2 rounded-lg text-sm transition flex items-center gap-1.5">
            <i class="fas fa-list text-xs"></i> All
        </a>
        <?php if($filterStatus): ?>
        <a href="orders.php?date_from=<?= urlencode($filterDateFrom) ?>&date_to=<?= urlencode($filterDateTo) ?>"
           class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold px-3 py-2 rounded-lg text-sm transition flex items-center gap-1.5">
            <i class="fas fa-times text-xs"></i> Clear Status
        </a>
        <?php endif; ?>
    </div>
</form>
<script>
function setToday() {
    var today = new Date().toISOString().slice(0,10);
    document.getElementById('input-date-from').value = today;
    document.getElementById('input-date-to').value   = today;
    document.getElementById('filter-form').submit();
}
</script>

<!-- Bulk action bar -->
<div id="bulk-bar"
     class="hidden sticky top-14 md:top-0 z-30 bg-gray-900 text-white rounded-xl px-4 py-3 mb-4 shadow-xl">
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <span class="font-bold text-sm flex-1">
            <span id="bulk-count">0</span> order<span id="bulk-plural">s</span> selected
        </span>
        <div class="flex items-center gap-2 flex-wrap">
            <select id="bulk-status-select"
                    class="bg-gray-800 border border-gray-600 text-white text-sm rounded-lg px-3 py-2 focus:outline-none focus:border-gray-400 flex-1 sm:flex-none">
                <option value="">-- Pick status --</option>
                <?php foreach($validStatuses as $s): ?>
                <option value="<?= $s ?>"><?= $s ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" onclick="applyBulk()"
                    class="bg-red-600 active:bg-red-700 text-white text-sm font-bold px-5 py-2 rounded-lg transition">
                Apply
            </button>
            <button type="button" onclick="clearSelection()"
                    class="text-gray-400 hover:text-white text-sm font-bold px-3 py-2 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>

<!-- Hidden bulk-submit form -->
<form id="bulk-form" method="POST" action="orders.php">
    <?= csrf_field() ?>
    <input type="hidden" name="bulk_status" id="bulk-status-input">
    <div id="bulk-ids-container"></div>
</form>

<!-- ═══ DESKTOP TABLE ═══════════════════════════════════════════════════════ -->
<div class="hidden md:block bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs uppercase text-gray-500">
                    <th class="px-4 py-3 w-10">
                        <input type="checkbox" id="select-all-desktop"
                               class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600"
                               onchange="toggleAll(this,'desktop')">
                    </th>
                    <th class="px-4 py-3">Order</th>
                    <th class="px-4 py-3">Product(s)</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($ordersDisplay)): ?>
            <tr>
                <td colspan="8" class="px-4 py-16 text-center text-gray-400">
                    <i class="fas fa-box-open text-5xl mb-3 block text-gray-200"></i>
                    <?= ($filterDateFrom||$filterDateTo||$filterStatus) ? 'No orders match the current filter.' : 'No orders placed yet.' ?>
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($ordersDisplay as $order):
                $sc        = $statusColors[$order['status'] ?? ''] ?? ['pill'=>'bg-gray-100 text-gray-700 border-gray-200'];
                $items     = $order['items'] ?? [];
                $firstImg  = $items[0]['image'] ?? '';
                $extraCount = max(0, count($items) - 1);
                $totalQty   = array_sum(array_column($items, 'qty'));
            ?>
            <tr class="order-row border-b border-gray-100 hover:bg-gray-50 transition" data-id="<?= htmlspecialchars($order['id']) ?>">
                <td class="px-4 py-3">
                    <input type="checkbox" value="<?= htmlspecialchars($order['id']) ?>"
                           class="row-check-desktop row-check w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600"
                           onchange="onRowCheck()">
                </td>
                <td class="px-4 py-3">
                    <div class="font-bold text-gray-900 text-sm">#<?= htmlspecialchars($order['id']) ?></div>
                    <?php if (($order['source'] ?? '') === 'landing_page'): ?>
                        <span class="text-[10px] font-bold bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded-full border border-blue-200">Landing</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <?php if ($firstImg): ?>
                            <div class="relative flex-shrink-0">
                                <img src="<?= htmlspecialchars($firstImg) ?>"
                                     class="w-12 h-12 object-cover rounded-lg border border-gray-200 bg-gray-50"
                                     onerror="this.style.display='none'">
                                <?php if ($extraCount > 0): ?>
                                <span class="absolute -top-1 -right-1 bg-gray-700 text-white text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center">+<?= $extraCount ?></span>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="w-12 h-12 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-box text-gray-300 text-lg"></i>
                            </div>
                        <?php endif; ?>
                        <div class="min-w-0">
                            <?php foreach (array_slice($items, 0, 2) as $item): ?>
                            <div class="text-sm font-medium text-gray-800 truncate max-w-[160px]"><?= htmlspecialchars($item['name']) ?></div>
                            <div class="text-xs text-gray-400">Qty: <?= (int)$item['qty'] ?> · ৳<?= number_format($item['price']) ?></div>
                            <?php endforeach; ?>
                            <?php if ($extraCount > 1): ?><div class="text-xs text-gray-400">+<?= $extraCount-1 ?> more</div><?php endif; ?>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <div class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($order['customer']['name'] ?? '—') ?></div>
                    <?php $dp = $order['customer']['phone'] ?? ''; ?>
                    <a href="tel:<?= bd_tel($dp) ?>" class="text-xs text-blue-600 hover:underline mt-0.5 block"><i class="fas fa-phone-alt mr-1"></i><?= htmlspecialchars($dp) ?></a>
                </td>
                <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap"><?= htmlspecialchars(substr($order['date'] ?? '', 0, 16)) ?></td>
                <td class="px-4 py-3 font-bold text-gray-900">৳<?= number_format($order['total'] ?? 0) ?></td>
                <td class="px-4 py-3">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold border <?= $sc['pill'] ?>"><?= htmlspecialchars($order['status'] ?? '') ?></span>
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="view_order.php?id=<?= htmlspecialchars($order['id']) ?>"
                       class="inline-flex items-center gap-1 text-xs font-bold text-gray-500 hover:text-red-600 transition">
                        Details <i class="fas fa-chevron-right text-[10px]"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ═══ MOBILE CARD LIST ════════════════════════════════════════════════════ -->
<div class="md:hidden space-y-3">

    <?php if (empty($ordersDisplay)): ?>
    <div class="bg-white rounded-xl border border-gray-200 p-10 text-center text-gray-400">
        <i class="fas fa-box-open text-4xl mb-3 block text-gray-200"></i>
        <?= ($filterDateFrom||$filterDateTo||$filterStatus) ? 'No orders match this filter.' : 'No orders placed yet.' ?>
    </div>
    <?php else: ?>

    <!-- Mobile Select All -->
    <div class="bg-white rounded-xl border border-gray-200 px-4 py-3 flex items-center justify-between">
        <label class="flex items-center gap-2.5 cursor-pointer text-sm font-semibold text-gray-700">
            <input type="checkbox" id="select-all-mobile"
                   class="w-4 h-4 rounded border-gray-300 accent-red-600"
                   onchange="toggleAll(this,'mobile')">
            Select All
        </label>
        <span class="text-xs text-gray-400"><?= count($ordersDisplay) ?> orders</span>
    </div>

    <?php foreach ($ordersDisplay as $order):
        $sc        = $statusColors[$order['status'] ?? ''] ?? ['pill'=>'bg-gray-100 text-gray-700'];
        $items     = $order['items'] ?? [];
        $firstImg  = $items[0]['image'] ?? '';
        $totalQty  = array_sum(array_column($items, 'qty'));
        $mPhone    = $order['customer']['phone'] ?? '';
    ?>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden order-card">
        <!-- Card header -->
        <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 bg-gray-50">
            <input type="checkbox" value="<?= htmlspecialchars($order['id']) ?>"
                   class="row-check-mobile row-check w-4 h-4 rounded border-gray-300 accent-red-600 flex-shrink-0"
                   onchange="onRowCheck()">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-gray-900 text-sm">#<?= htmlspecialchars($order['id']) ?></span>
                    <?php if (($order['source'] ?? '') === 'landing_page'): ?>
                        <span class="text-[10px] font-bold bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded-full">Landing</span>
                    <?php endif; ?>
                </div>
                <span class="text-xs text-gray-400"><?= htmlspecialchars(substr($order['date'] ?? '', 0, 16)) ?></span>
            </div>
            <span class="flex-shrink-0 px-2.5 py-1 rounded-full text-xs font-bold <?= $sc['pill'] ?>">
                <?= htmlspecialchars($order['status'] ?? '') ?>
            </span>
        </div>

        <!-- Card body -->
        <div class="px-4 py-3 flex gap-3">
            <!-- Product image with qty badge -->
            <div class="flex-shrink-0">
                <?php if ($firstImg): ?>
                    <div class="relative">
                        <img src="<?= htmlspecialchars($firstImg) ?>"
                             class="w-16 h-16 object-cover rounded-lg border border-gray-200"
                             onerror="this.parentElement.innerHTML='<div class=\'w-16 h-16 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center\'><i class=\'fas fa-box text-gray-300 text-xl\'></i></div>'">
                        <span class="absolute -top-1 -right-1 bg-green-500 text-white text-[10px] font-black min-w-[18px] h-[18px] px-1 rounded-full flex items-center justify-center"><?= $totalQty ?></span>
                    </div>
                <?php else: ?>
                    <div class="relative w-16 h-16 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center">
                        <i class="fas fa-box text-gray-300 text-xl"></i>
                        <span class="absolute -top-1 -right-1 bg-green-500 text-white text-[10px] font-black min-w-[18px] h-[18px] px-1 rounded-full flex items-center justify-center"><?= $totalQty ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Customer info -->
            <div class="flex-1 min-w-0">
                <div class="text-xs text-gray-500 truncate mb-0.5"><?= htmlspecialchars($items[0]['name'] ?? '—') ?><?php if(count($items)>1): ?> <span class="text-gray-400">+<?= count($items)-1 ?></span><?php endif; ?></div>
                <div class="font-bold text-gray-900 text-sm leading-tight"><?= htmlspecialchars($order['customer']['name'] ?? '—') ?></div>
                <a href="tel:<?= bd_tel($mPhone) ?>"
                   onclick="event.stopPropagation()"
                   class="text-sm font-black text-blue-600 block leading-tight mt-0.5 active:opacity-70">
                    <?= htmlspecialchars($mPhone) ?>
                </a>
                <div class="text-xs text-gray-500 mt-0.5 line-clamp-2 leading-snug">
                    <?= htmlspecialchars($order['customer']['address'] ?? '') ?>
                </div>
            </div>

            <!-- Total + action -->
            <div class="flex-shrink-0 flex flex-col items-end justify-between min-w-[70px]">
                <div class="text-right">
                    <div class="text-xs text-gray-400">Total</div>
                    <div class="font-black text-gray-900 text-sm">৳<?= number_format($order['total'] ?? 0) ?></div>
                    <div class="text-[10px] text-gray-400 mt-0.5">Ship: ৳<?= number_format($order['shipping_cost'] ?? 0) ?></div>
                </div>
                <a href="view_order.php?id=<?= htmlspecialchars($order['id']) ?>"
                   class="inline-flex items-center gap-1 text-xs font-bold text-red-600 bg-red-50 border border-red-200 px-3 py-1.5 rounded-lg active:bg-red-100 transition mt-2">
                    View <i class="fas fa-chevron-right text-[10px]"></i>
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function getChecked() {
    return [...document.querySelectorAll('.row-check:checked')];
}
function onRowCheck() {
    var checked = getChecked();
    var total   = document.querySelectorAll('.row-check').length;
    ['desktop','mobile'].forEach(function(v){
        var sa = document.getElementById('select-all-'+v);
        if(!sa) return;
        sa.indeterminate = checked.length > 0 && checked.length < total;
        sa.checked       = checked.length === total && total > 0;
    });
    updateBulkBar(checked.length);
}
function toggleAll(master, variant) {
    document.querySelectorAll('.row-check-'+variant).forEach(function(cb){ cb.checked = master.checked; });
    var other = document.getElementById('select-all-'+(variant==='mobile'?'desktop':'mobile'));
    if(other) other.checked = master.checked;
    updateBulkBar(master.checked ? document.querySelectorAll('.row-check').length : 0);
}
function clearSelection() {
    document.querySelectorAll('.row-check').forEach(function(cb){ cb.checked = false; });
    ['select-all-desktop','select-all-mobile'].forEach(function(id){
        var el = document.getElementById(id);
        if(el){ el.checked = false; el.indeterminate = false; }
    });
    updateBulkBar(0);
}
function updateBulkBar(count) {
    var bar = document.getElementById('bulk-bar');
    document.getElementById('bulk-count').textContent = count;
    document.getElementById('bulk-plural').textContent = count===1?'':'s';
    if(count>0){ bar.classList.remove('hidden'); bar.classList.add('flex'); }
    else        { bar.classList.add('hidden');    bar.classList.remove('flex'); }
}
function applyBulk() {
    var status = document.getElementById('bulk-status-select').value;
    if(!status){ alert('Please choose a status.'); return; }
    var ids = getChecked().map(function(cb){ return cb.value; });
    if(!ids.length){ alert('No orders selected.'); return; }
    document.getElementById('bulk-status-input').value = status;
    var c = document.getElementById('bulk-ids-container');
    c.innerHTML = '';
    ids.forEach(function(id){
        var inp = document.createElement('input');
        inp.type='hidden'; inp.name='order_ids[]'; inp.value=id;
        c.appendChild(inp);
    });
    document.getElementById('bulk-form').submit();
}
</script>

<?php include 'includes/footer.php'; ?>
