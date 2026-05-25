<?php
require_once __DIR__ . '/_auth.php';

if (isset($_GET['delete'])) {
    $stmt = db()->prepare('DELETE FROM leads WHERE id = ?');
    $stmt->execute([(int) $_GET['delete']]);
    set_flash('Lead deleted.');
    header('Location: leads.php');
    exit;
}

$leads = db()->query('SELECT * FROM leads ORDER BY created_at DESC')->fetchAll();
admin_header('Leads', 'Track demo requests and customer enquiries.');
$message = flash();
?>
<?php if ($message): ?><div class="mb-5 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 font-bold text-emerald-700"><i data-lucide="check-circle-2" class="h-5 w-5"></i><?= e($message) ?></div><?php endif; ?>
<div class="overflow-hidden rounded-[1.35rem] border border-slate-200 bg-white shadow-panel">
  <div class="flex items-center justify-between border-b border-slate-100 p-6">
    <div>
      <h2 class="text-xl font-black">Lead Inbox</h2>
      <p class="mt-1 text-sm font-medium text-slate-500">Newest enquiries appear first.</p>
    </div>
    <span class="rounded-full bg-blue-50 px-3 py-1 text-sm font-black text-blue-700"><?= count($leads) ?> leads</span>
  </div>
  <div class="overflow-x-auto p-6">
  <table class="w-full min-w-[900px] text-left text-sm">
    <thead><tr class="border-b text-xs uppercase tracking-wide text-slate-500"><th class="py-3">Date</th><th>Name</th><th>Phone</th><th>Business</th><th>Message</th><th class="text-right">Action</th></tr></thead>
    <tbody>
      <?php foreach ($leads as $lead): ?>
        <tr class="border-b last:border-0 hover:bg-slate-50">
          <td class="py-3"><?= e($lead['created_at']) ?></td>
          <td class="font-bold"><?= e($lead['name']) ?></td>
          <td><a class="font-bold text-blue-600" href="tel:<?= e($lead['phone']) ?>"><?= e($lead['phone']) ?></a></td>
          <td><?= e($lead['business_type']) ?></td>
          <td><?= e($lead['message']) ?></td>
          <td class="text-right"><a class="font-black text-red-600" onclick="return confirm('Delete this lead?')" href="leads.php?delete=<?= e((string)$lead['id']) ?>">Delete</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$leads): ?><tr><td colspan="6" class="py-8 text-center text-slate-500">No leads yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
</div>
<?php admin_footer(); ?>
