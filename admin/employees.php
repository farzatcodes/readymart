<?php
include_once 'includes/header.php';

$empFile = '../employees.json';
$employees = file_exists($empFile) ? json_decode(file_get_contents($empFile), true) ?? [] : [];

$flash = $flashType = '';

// ── Add employee ───────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name     = trim($_POST['name']     ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = in_array($_POST['role'] ?? '', ['admin','manager','staff']) ? $_POST['role'] : 'staff';
    $phone    = trim($_POST['phone']    ?? '');

    if ($name === '' || $username === '' || $password === '') {
        $flash = 'Name, username and password are required.';
        $flashType = 'red';
    } else {
        // Check duplicate username
        $exists = array_filter($employees, fn($e) => strtolower($e['username']) === strtolower($username));
        if ($exists) {
            $flash = 'Username "' . htmlspecialchars($username) . '" is already taken.';
            $flashType = 'red';
        } else {
            $employees[] = [
                'id'         => 'EMP-' . strtoupper(substr(md5(uniqid()), 0, 6)),
                'name'       => $name,
                'username'   => $username,
                'password'   => password_hash($password, PASSWORD_DEFAULT),
                'role'       => $role,
                'phone'      => $phone,
                'status'     => 'active',
                'created_at' => date('Y-m-d H:i:s'),
            ];
            file_put_contents($empFile, json_encode($employees, JSON_PRETTY_PRINT));
            $flash = 'Employee <strong>' . htmlspecialchars($name) . '</strong> added successfully.';
            $flashType = 'green';
        }
    }
}

// ── Toggle status ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle') {
    $toggleId = $_POST['emp_id'] ?? '';
    foreach ($employees as &$emp) {
        if ($emp['id'] === $toggleId) {
            $emp['status'] = ($emp['status'] === 'active') ? 'inactive' : 'active';
            break;
        }
    }
    unset($emp);
    file_put_contents($empFile, json_encode($employees, JSON_PRETTY_PRINT));
    header('Location: employees.php');
    exit;
}

// ── Delete employee ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $deleteId = $_POST['emp_id'] ?? '';
    $employees = array_values(array_filter($employees, fn($e) => $e['id'] !== $deleteId));
    file_put_contents($empFile, json_encode($employees, JSON_PRETTY_PRINT));
    $flash = 'Employee removed.';
    $flashType = 'green';
}

$roles = ['admin'=>'Admin','manager'=>'Manager','staff'=>'Staff'];
$roleColors = ['admin'=>'bg-red-100 text-red-700','manager'=>'bg-purple-100 text-purple-700','staff'=>'bg-blue-100 text-blue-700'];
?>

<div class="mb-5 flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-gray-800">Employees</h1>
        <p class="text-gray-500 text-sm mt-1"><?= count($employees) ?> staff member<?= count($employees)!==1?'s':'' ?></p>
    </div>
    <button onclick="document.getElementById('add-modal').classList.remove('hidden')"
            class="bg-gray-900 hover:bg-gray-700 active:bg-black text-white font-bold px-4 py-2.5 rounded-lg text-sm flex items-center gap-2 transition">
        <i class="fas fa-plus"></i> <span class="hidden sm:inline">Add Employee</span>
    </button>
</div>

<?php if ($flash): ?>
<div class="mb-4 bg-<?= $flashType ?>-50 border-l-4 border-<?= $flashType ?>-500 text-<?= $flashType ?>-800 text-sm font-medium px-4 py-3 rounded-lg flex items-start gap-2">
    <i class="fas fa-<?= $flashType==='green'?'check':'exclamation' ?>-circle mt-0.5 flex-shrink-0"></i>
    <span><?= $flash ?></span>
</div>
<?php endif; ?>

<?php if (empty($employees)): ?>
<div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-400">
    <i class="fas fa-user-tie text-5xl mb-3 block text-gray-200"></i>
    <p class="font-semibold text-gray-600 mb-1">No employees yet</p>
    <p class="text-sm mb-4">Add your first staff member to get started.</p>
    <button onclick="document.getElementById('add-modal').classList.remove('hidden')"
            class="bg-gray-900 text-white font-bold px-5 py-2.5 rounded-lg text-sm">
        <i class="fas fa-plus mr-1.5"></i> Add Employee
    </button>
</div>
<?php else: ?>

<!-- ── DESKTOP TABLE ──────────────────────────────────────────────── -->
<div class="hidden md:block bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <table class="w-full text-left">
        <thead>
            <tr class="bg-gray-50 border-b border-gray-200 text-xs uppercase text-gray-500">
                <th class="px-5 py-3 font-bold">Employee</th>
                <th class="px-5 py-3 font-bold">Username</th>
                <th class="px-5 py-3 font-bold">Phone</th>
                <th class="px-5 py-3 font-bold">Role</th>
                <th class="px-5 py-3 font-bold">Added</th>
                <th class="px-5 py-3 font-bold">Status</th>
                <th class="px-5 py-3 font-bold text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($employees as $emp): ?>
        <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
            <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center font-bold text-sm flex-shrink-0">
                        <?= strtoupper(mb_substr($emp['name'], 0, 1)) ?>
                    </div>
                    <span class="font-semibold text-gray-800 text-sm"><?= htmlspecialchars($emp['name']) ?></span>
                </div>
            </td>
            <td class="px-5 py-3.5 text-sm font-mono text-gray-600">@<?= htmlspecialchars($emp['username']) ?></td>
            <td class="px-5 py-3.5">
                <?php if($emp['phone']??''): ?>
                <a href="tel:<?= bd_tel($emp['phone']) ?>" class="text-sm text-blue-600 hover:underline">
                    <?= htmlspecialchars($emp['phone']) ?>
                </a>
                <?php else: ?>—<?php endif; ?>
            </td>
            <td class="px-5 py-3.5">
                <span class="px-2.5 py-1 rounded-full text-xs font-bold <?= $roleColors[$emp['role']] ?? 'bg-gray-100 text-gray-600' ?>">
                    <?= $roles[$emp['role']] ?? ucfirst($emp['role']) ?>
                </span>
            </td>
            <td class="px-5 py-3.5 text-xs text-gray-500"><?= htmlspecialchars(substr($emp['created_at'], 0, 10)) ?></td>
            <td class="px-5 py-3.5">
                <form method="POST" class="inline">
                    <input type="hidden" name="action"  value="toggle">
                    <input type="hidden" name="emp_id"  value="<?= htmlspecialchars($emp['id']) ?>">
                    <button type="submit"
                        class="<?= $emp['status']==='active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?> px-2.5 py-1 rounded-full text-xs font-bold border <?= $emp['status']==='active' ? 'border-green-200' : 'border-gray-200' ?> hover:opacity-75 transition">
                        <?= $emp['status'] === 'active' ? '● Active' : '○ Inactive' ?>
                    </button>
                </form>
            </td>
            <td class="px-5 py-3.5 text-right">
                <form method="POST" onsubmit="return confirm('Remove this employee?')" class="inline">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="emp_id" value="<?= htmlspecialchars($emp['id']) ?>">
                    <button type="submit" class="text-xs font-bold text-red-500 hover:text-red-700 transition">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- ── MOBILE CARD LIST ───────────────────────────────────────────── -->
<div class="md:hidden space-y-3">
<?php foreach ($employees as $emp): ?>
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="flex items-center gap-3 px-4 py-4">
        <div class="w-12 h-12 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center font-black text-lg flex-shrink-0">
            <?= strtoupper(mb_substr($emp['name'], 0, 1)) ?>
        </div>
        <div class="flex-1 min-w-0">
            <div class="font-bold text-gray-900"><?= htmlspecialchars($emp['name']) ?></div>
            <div class="text-xs text-gray-500 font-mono mt-0.5">@<?= htmlspecialchars($emp['username']) ?></div>
            <?php if($emp['phone']??''): ?>
            <a href="tel:<?= bd_tel($emp['phone']) ?>" class="text-sm font-bold text-blue-600 block mt-0.5">
                <i class="fas fa-phone-alt text-xs mr-1"></i><?= htmlspecialchars($emp['phone']) ?>
            </a>
            <?php endif; ?>
        </div>
        <div class="flex-shrink-0 flex flex-col items-end gap-2">
            <span class="px-2.5 py-1 rounded-full text-xs font-bold <?= $roleColors[$emp['role']] ?? 'bg-gray-100 text-gray-600' ?>">
                <?= $roles[$emp['role']] ?? ucfirst($emp['role']) ?>
            </span>
            <span class="text-xs font-bold <?= $emp['status']==='active' ? 'text-green-600' : 'text-gray-400' ?>">
                <?= $emp['status']==='active' ? '● Active' : '○ Inactive' ?>
            </span>
        </div>
    </div>
    <div class="border-t border-gray-100 flex divide-x divide-gray-100">
        <form method="POST" class="flex-1">
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="emp_id" value="<?= htmlspecialchars($emp['id']) ?>">
            <button type="submit" class="w-full py-2.5 text-xs font-bold text-gray-600 hover:bg-gray-50 transition">
                <i class="fas fa-toggle-<?= $emp['status']==='active'?'on text-green-500':'off text-gray-400' ?> mr-1"></i>
                <?= $emp['status']==='active' ? 'Deactivate' : 'Activate' ?>
            </button>
        </form>
        <form method="POST" class="flex-1" onsubmit="return confirm('Remove this employee?')">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="emp_id" value="<?= htmlspecialchars($emp['id']) ?>">
            <button type="submit" class="w-full py-2.5 text-xs font-bold text-red-500 hover:bg-red-50 transition">
                <i class="fas fa-trash-alt mr-1"></i> Remove
            </button>
        </form>
    </div>
</div>
<?php endforeach; ?>
</div>

<?php endif; ?>

<!-- ═══ ADD EMPLOYEE MODAL ════════════════════════════════════════════ -->
<div id="add-modal" class="hidden fixed inset-0 z-50 flex items-end md:items-center justify-center px-0 md:px-4">
    <div class="absolute inset-0 bg-black/60" onclick="document.getElementById('add-modal').classList.add('hidden')"></div>
    <div class="relative bg-white w-full md:max-w-md md:rounded-xl rounded-t-2xl shadow-2xl z-10 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="font-bold text-gray-900">Add New Employee</h2>
            <button onclick="document.getElementById('add-modal').classList.add('hidden')"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" class="p-5 space-y-4">
            <input type="hidden" name="action" value="add">

            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase tracking-wide">Full Name *</label>
                <input type="text" name="name" required placeholder="e.g. Rahim Ahmed"
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-900">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase tracking-wide">Username *</label>
                    <input type="text" name="username" required placeholder="rahim"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-900">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase tracking-wide">Password *</label>
                    <input type="password" name="password" required placeholder="••••••"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-900">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase tracking-wide">Role</label>
                    <select name="role" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-900 bg-white">
                        <option value="staff">Staff</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1.5 uppercase tracking-wide">Phone</label>
                    <input type="tel" name="phone" placeholder="01XXXXXXXXX"
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-900">
                </div>
            </div>

            <div class="pt-2 flex gap-3">
                <button type="button" onclick="document.getElementById('add-modal').classList.add('hidden')"
                        class="flex-1 py-2.5 border border-gray-300 text-gray-700 font-bold rounded-lg text-sm hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 py-2.5 bg-gray-900 hover:bg-gray-700 text-white font-bold rounded-lg text-sm transition">
                    <i class="fas fa-user-plus mr-1.5"></i> Add Employee
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($flash && $flashType === 'red'): ?>
<script>document.getElementById('add-modal').classList.remove('hidden');</script>
<?php endif; ?>

<?php include_once 'includes/footer.php'; ?>
