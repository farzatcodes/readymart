<?php
$jsonFile = '../orders.json';
$orders   = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) ?? [] : [];

$flashMsg   = '';
$flashType  = '';

// ── Bulk status update ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_status'], $_POST['order_ids'])) {
    $validStatuses = ['Pending', 'Processing', 'Completed', 'Cancelled'];
    $newStatus     = $_POST['bulk_status'];
    $selectedIds   = (array)$_POST['order_ids'];

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
        $flashMsg  = 'Invalid action. Please select a valid status.';
        $flashType = 'red';
    }
}

$ordersDisplay = array_reverse($orders);

include 'includes/header.php';
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Customer Orders</h1>
        <p class="text-gray-500 text-sm mt-1"><?= count($orders) ?> total orders</p>
    </div>
</div>

<?php if ($flashMsg): ?>
<div class="mb-4 bg-<?= $flashType ?>-50 border-l-4 border-<?= $flashType ?>-500 text-<?= $flashType ?>-800 text-sm font-medium px-4 py-3 rounded">
    <?= $flashMsg ?>
</div>
<?php endif; ?>

<!-- Bulk action bar (shown via JS when rows are checked) -->
<div id="bulk-bar"
     class="hidden sticky top-0 z-30 bg-gray-900 text-white rounded-xl px-5 py-3 mb-4 flex flex-col sm:flex-row sm:items-center gap-3 shadow-xl">
    <span class="font-bold text-sm flex-1">
        <span id="bulk-count">0</span> order<span id="bulk-plural">s</span> selected
    </span>
    <div class="flex items-center gap-2 flex-wrap">
        <label class="text-xs font-bold text-gray-300">Change status to:</label>
        <select id="bulk-status-select"
                class="bg-gray-800 border border-gray-600 text-white text-sm rounded px-3 py-1.5 focus:outline-none focus:border-gray-400">
            <option value="">-- Pick status --</option>
            <option value="Pending">Pending</option>
            <option value="Processing">Processing</option>
            <option value="Completed">Completed</option>
            <option value="Cancelled">Cancelled</option>
        </select>
        <button id="bulk-apply-btn" type="button" onclick="applyBulk()"
                class="bg-red-600 hover:bg-red-700 text-white text-sm font-bold px-4 py-1.5 rounded transition">
            Apply
        </button>
        <button type="button" onclick="clearSelection()"
                class="text-gray-400 hover:text-white text-sm font-bold px-2 py-1.5 transition">
            Cancel
        </button>
    </div>
</div>

<!-- Hidden bulk-submit form (submitted by JS) -->
<form id="bulk-form" method="POST" action="orders.php">
    <input type="hidden" name="bulk_status" id="bulk-status-input">
    <div id="bulk-ids-container"></div>
</form>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs uppercase text-gray-500">
                    <th class="px-4 py-3 w-10">
                        <input type="checkbox" id="select-all"
                               class="w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600"
                               onchange="toggleAll(this)">
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
                        No orders placed yet.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($ordersDisplay as $order):
                    $statusColors = [
                        'Pending'    => 'bg-orange-100 text-orange-700 border-orange-200',
                        'Processing' => 'bg-blue-100 text-blue-700 border-blue-200',
                        'Completed'  => 'bg-green-100 text-green-700 border-green-200',
                        'Cancelled'  => 'bg-red-100 text-red-700 border-red-200',
                    ];
                    $sc        = $statusColors[$order['status'] ?? ''] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                    $items     = $order['items'] ?? [];
                    $firstImg  = $items[0]['image'] ?? '';
                    $extraCount = max(0, count($items) - 1);
                ?>
                <tr class="order-row border-b border-gray-100 hover:bg-gray-50 transition"
                    data-id="<?= htmlspecialchars($order['id']) ?>">

                    <!-- Checkbox -->
                    <td class="px-4 py-3">
                        <input type="checkbox" value="<?= htmlspecialchars($order['id']) ?>"
                               class="row-check w-4 h-4 rounded border-gray-300 cursor-pointer accent-red-600"
                               onchange="onRowCheck()">
                    </td>

                    <!-- Order ID + source badge -->
                    <td class="px-4 py-3">
                        <div class="font-bold text-gray-900 text-sm">#<?= htmlspecialchars($order['id']) ?></div>
                        <?php if (($order['source'] ?? '') === 'landing_page'): ?>
                            <span class="text-[10px] font-bold bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded-full border border-blue-200">Landing</span>
                        <?php endif; ?>
                    </td>

                    <!-- Product image(s) + name(s) -->
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <?php if ($firstImg): ?>
                                <div class="relative flex-shrink-0">
                                    <img src="<?= htmlspecialchars($firstImg) ?>"
                                         class="w-12 h-12 object-cover rounded-lg border border-gray-200 bg-gray-50"
                                         onerror="this.style.display='none'">
                                    <?php if ($extraCount > 0): ?>
                                    <span class="absolute -top-1 -right-1 bg-gray-700 text-white text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center">
                                        +<?= $extraCount ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="w-12 h-12 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-box text-gray-300 text-lg"></i>
                                </div>
                            <?php endif; ?>
                            <div class="min-w-0">
                                <?php foreach (array_slice($items, 0, 2) as $item): ?>
                                <div class="text-sm font-medium text-gray-800 truncate max-w-[160px]">
                                    <?= htmlspecialchars($item['name']) ?>
                                </div>
                                <div class="text-xs text-gray-400">
                                    Qty: <?= (int)$item['qty'] ?> &nbsp;·&nbsp; ৳<?= number_format($item['price']) ?>
                                </div>
                                <?php endforeach; ?>
                                <?php if ($extraCount > 1): ?>
                                <div class="text-xs text-gray-400">+<?= $extraCount - 1 ?> more item(s)</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>

                    <!-- Customer -->
                    <td class="px-4 py-3">
                        <div class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($order['customer']['name'] ?? '—') ?></div>
                        <div class="text-xs text-gray-400 mt-0.5">
                            <i class="fas fa-phone-alt mr-1"></i><?= htmlspecialchars($order['customer']['phone'] ?? '') ?>
                        </div>
                    </td>

                    <!-- Date -->
                    <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                        <?= htmlspecialchars($order['date'] ?? '') ?>
                    </td>

                    <!-- Total -->
                    <td class="px-4 py-3 font-bold text-gray-900">
                        ৳<?= number_format($order['total'] ?? 0) ?>
                    </td>

                    <!-- Status badge -->
                    <td class="px-4 py-3">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold border <?= $sc ?>">
                            <?= htmlspecialchars($order['status'] ?? '') ?>
                        </span>
                    </td>

                    <!-- Action -->
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

<script>
/* ── Checkbox helpers ─────────────────────────────────────────────── */
function getChecked() {
    return [...document.querySelectorAll('.row-check:checked')];
}

function onRowCheck() {
    const checked   = getChecked();
    const total     = document.querySelectorAll('.row-check').length;
    const selectAll = document.getElementById('select-all');

    selectAll.indeterminate = checked.length > 0 && checked.length < total;
    selectAll.checked       = checked.length === total && total > 0;

    updateBulkBar(checked.length);
}

function toggleAll(master) {
    document.querySelectorAll('.row-check').forEach(cb => { cb.checked = master.checked; });
    updateBulkBar(master.checked ? document.querySelectorAll('.row-check').length : 0);
}

function clearSelection() {
    document.querySelectorAll('.row-check').forEach(cb => { cb.checked = false; });
    document.getElementById('select-all').checked       = false;
    document.getElementById('select-all').indeterminate = false;
    updateBulkBar(0);
}

function updateBulkBar(count) {
    const bar    = document.getElementById('bulk-bar');
    const countEl = document.getElementById('bulk-count');
    const plural  = document.getElementById('bulk-plural');

    countEl.textContent = count;
    plural.textContent  = count === 1 ? '' : 's';

    if (count > 0) {
        bar.classList.remove('hidden');
        bar.classList.add('flex');
    } else {
        bar.classList.add('hidden');
        bar.classList.remove('flex');
    }
}

/* ── Bulk submit ──────────────────────────────────────────────────── */
function applyBulk() {
    const status = document.getElementById('bulk-status-select').value;
    if (!status) { alert('Please choose a status before applying.'); return; }

    const ids = getChecked().map(cb => cb.value);
    if (!ids.length) { alert('No orders selected.'); return; }

    document.getElementById('bulk-status-input').value = status;

    const container = document.getElementById('bulk-ids-container');
    container.innerHTML = '';
    ids.forEach(id => {
        const inp = document.createElement('input');
        inp.type  = 'hidden';
        inp.name  = 'order_ids[]';
        inp.value = id;
        container.appendChild(inp);
    });

    document.getElementById('bulk-form').submit();
}
</script>

<?php include 'includes/footer.php'; ?>
