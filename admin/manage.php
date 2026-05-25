<?php
require_once __DIR__ . '/_auth.php';

$schemas = [
    'industries' => ['title' => 'text', 'subtitle' => 'text', 'image' => 'text', 'sort_order' => 'number', 'is_active' => 'checkbox'],
    'stats' => ['icon' => 'text', 'value_text' => 'text', 'label' => 'text', 'sort_order' => 'number', 'is_active' => 'checkbox'],
    'services' => ['title' => 'text', 'description' => 'textarea', 'logo' => 'text', 'sort_order' => 'number', 'is_active' => 'checkbox'],
    'why_cards' => ['icon' => 'text', 'title' => 'text', 'description' => 'textarea', 'sort_order' => 'number', 'is_active' => 'checkbox'],
    'projects' => ['category' => 'text', 'title' => 'text', 'image' => 'text', 'sort_order' => 'number', 'is_active' => 'checkbox'],
    'features' => ['icon' => 'text', 'title' => 'text', 'description' => 'textarea', 'sort_order' => 'number', 'is_active' => 'checkbox'],
    'pricing_plans' => ['name' => 'text', 'price' => 'text', 'description' => 'textarea', 'button_text' => 'text', 'is_featured' => 'checkbox', 'sort_order' => 'number', 'is_active' => 'checkbox'],
    'testimonials' => ['name' => 'text', 'role' => 'text', 'quote' => 'textarea', 'image' => 'text', 'rating' => 'number', 'sort_order' => 'number', 'is_active' => 'checkbox'],
];

$type = $_GET['type'] ?? 'services';
if (!isset($schemas[$type])) {
    $type = 'services';
}
$fields = $schemas[$type];
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (isset($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];
    $stmt = db()->prepare("DELETE FROM {$type} WHERE id = ?");
    $stmt->execute([$deleteId]);
    set_flash('Item deleted.');
    header('Location: manage.php?type=' . urlencode($type));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [];
    foreach ($fields as $name => $kind) {
        if ($kind === 'checkbox') {
            $data[$name] = isset($_POST[$name]) ? 1 : 0;
        } else {
            $data[$name] = trim($_POST[$name] ?? '');
        }
    }

    if ($id > 0) {
        $assignments = implode(', ', array_map(fn($field) => "{$field} = ?", array_keys($data)));
        $stmt = db()->prepare("UPDATE {$type} SET {$assignments} WHERE id = ?");
        $stmt->execute([...array_values($data), $id]);
        set_flash('Item updated.');
    } else {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $stmt = db()->prepare("INSERT INTO {$type} ({$columns}) VALUES ({$placeholders})");
        $stmt->execute(array_values($data));
        set_flash('Item added.');
    }
    header('Location: manage.php?type=' . urlencode($type));
    exit;
}

$item = [];
if ($id > 0) {
    $stmt = db()->prepare("SELECT * FROM {$type} WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch() ?: [];
}

$items = db()->query("SELECT * FROM {$type} ORDER BY sort_order ASC, id DESC")->fetchAll();
$title = ucwords(str_replace('_', ' ', $type));
admin_header($title, 'Add, edit, hide or delete website content blocks.');
$message = flash();
?>
<?php if ($message): ?><div class="mb-5 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 font-bold text-emerald-700"><i data-lucide="check-circle-2" class="h-5 w-5"></i><?= e($message) ?></div><?php endif; ?>

<section class="grid gap-6 xl:grid-cols-[420px_1fr]">
  <form method="post" class="rounded-[1.35rem] border border-slate-200 bg-white p-6 shadow-panel">
    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
      <div>
        <h2 class="text-xl font-black"><?= $id ? 'Edit Item' : 'Add New Item' ?></h2>
        <p class="mt-1 text-sm font-medium text-slate-500">Keep content ordered and active.</p>
      </div>
      <span class="grid h-10 w-10 place-items-center rounded-xl bg-blue-50 text-brand"><i data-lucide="plus" class="h-5 w-5"></i></span>
    </div>
    <div class="mt-5 grid gap-4">
      <?php foreach ($fields as $name => $kind): ?>
        <label class="field-label">
          <?= e(ucwords(str_replace('_', ' ', $name))) ?>
          <?php if ($kind === 'textarea'): ?>
            <textarea class="admin-input min-h-28" name="<?= e($name) ?>"><?= e((string)($item[$name] ?? '')) ?></textarea>
          <?php elseif ($kind === 'checkbox'): ?>
            <span class="flex items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 font-semibold text-slate-600"><input class="h-5 w-5 rounded border-slate-300" type="checkbox" name="<?= e($name) ?>" value="1" <?= (int)($item[$name] ?? 1) === 1 ? 'checked' : '' ?>> Active / Yes</span>
          <?php else: ?>
            <input class="admin-input" type="<?= e($kind) ?>" name="<?= e($name) ?>" value="<?= e((string)($item[$name] ?? ($kind === 'number' ? '0' : ''))) ?>">
          <?php endif; ?>
        </label>
      <?php endforeach; ?>
    </div>
    <div class="mt-6 flex gap-3">
      <button class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 font-black text-white shadow-lg shadow-blue-600/20"><i data-lucide="save" class="h-4 w-4"></i><?= $id ? 'Update' : 'Add' ?></button>
      <?php if ($id): ?><a class="rounded-xl border border-slate-200 px-5 py-3 font-black" href="manage.php?type=<?= e($type) ?>">Cancel</a><?php endif; ?>
    </div>
    <p class="mt-5 text-sm leading-6 text-slate-500">Image/logo fields accept paths like <code>assets/logos/react.svg</code> or full image URLs.</p>
  </form>

  <div class="overflow-hidden rounded-[1.35rem] border border-slate-200 bg-white shadow-panel">
    <div class="flex items-center justify-between border-b border-slate-100 p-6">
      <h2 class="text-xl font-black">All Items</h2>
      <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-black text-slate-600"><?= count($items) ?> total</span>
    </div>
    <div class="overflow-x-auto p-6">
    <table class="w-full min-w-[760px] text-left text-sm">
      <thead><tr class="border-b text-xs uppercase tracking-wide text-slate-500"><th class="py-3">ID</th><th>Title</th><th>Order</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
      <tbody>
        <?php foreach ($items as $row): ?>
          <?php $label = $row['title'] ?? $row['name'] ?? $row['value_text'] ?? ('Item #' . $row['id']); ?>
          <tr class="border-b last:border-0 hover:bg-slate-50">
            <td class="py-3 font-bold"><?= e((string)$row['id']) ?></td>
            <td class="font-bold"><?= e((string)$label) ?></td>
            <td><?= e((string)($row['sort_order'] ?? '0')) ?></td>
            <td><span class="rounded-full px-3 py-1 text-xs font-black <?= !empty($row['is_active']) ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' ?>"><?= !empty($row['is_active']) ? 'Active' : 'Hidden' ?></span></td>
            <td class="text-right">
              <a class="font-black text-blue-600" href="manage.php?type=<?= e($type) ?>&id=<?= e((string)$row['id']) ?>">Edit</a>
              <a class="ml-4 font-black text-red-600" onclick="return confirm('Delete this item?')" href="manage.php?type=<?= e($type) ?>&delete=<?= e((string)$row['id']) ?>">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    </div>
  </div>
</section>
<?php admin_footer(); ?>
