<?php
require_once __DIR__ . '/_auth.php';

$groups = [
    'General' => ['site_name', 'meta_title', 'meta_description'],
    'Contact' => ['phone_number', 'whatsapp_number', 'email', 'instagram_url'],
    'Hero' => ['hero_image', 'hero_badge', 'hero_title', 'hero_description', 'hero_primary_button', 'hero_secondary_button'],
    'Sections' => ['services_heading', 'services_subtitle', 'about_heading', 'about_text', 'projects_heading', 'projects_text', 'pricing_heading', 'pricing_text', 'testimonials_heading', 'contact_heading', 'contact_text'],
    'Footer' => ['footer_description', 'footer_text', 'whatsapp_message'],
];
$editable = array_merge(...array_values($groups));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($editable as $key) {
        if ($key === 'hero_image' && !empty($_FILES['hero_image_upload']['tmp_name'])) {
            $uploadDir = __DIR__ . '/../uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $originalName = basename((string) $_FILES['hero_image_upload']['name']);
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (in_array($extension, $allowed, true)) {
                $fileName = 'hero-' . time() . '.' . $extension;
                if (move_uploaded_file($_FILES['hero_image_upload']['tmp_name'], $uploadDir . '/' . $fileName)) {
                    $_POST[$key] = 'uploads/' . $fileName;
                }
            }
        }
        $stmt = db()->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
        $stmt->execute([$key, $_POST[$key] ?? '']);
    }
    set_flash('Settings saved successfully.');
    header('Location: index.php');
    exit;
}

function field_icon(string $key): string
{
    return match ($key) {
        'phone_number' => 'phone',
        'whatsapp_number', 'whatsapp_message' => 'message-circle',
        'email' => 'mail',
        'instagram_url' => 'instagram',
        'hero_image' => 'image',
        'hero_badge' => 'badge-star',
        default => 'pen-line',
    };
}

function field_help(string $key): string
{
    return match ($key) {
        'hero_image' => 'Upload a PNG/JPG/WebP image or enter a path like assets/hero-device-mockup.png.',
        'whatsapp_number' => 'Use country code, only numbers are best. Example: 919999999999.',
        'meta_description' => 'Short text used by Google and social previews.',
        default => '',
    };
}

admin_header('Website Settings', 'Manage homepage content, images, SEO and contact details.');
$message = flash();
?>
<?php if ($message): ?>
  <div class="mb-5 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 font-bold text-emerald-700">
    <i data-lucide="check-circle-2" class="h-5 w-5"></i><?= e($message) ?>
  </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="space-y-6">
  <?php foreach ($groups as $groupTitle => $fields): ?>
    <section class="rounded-[1.35rem] border border-slate-200 bg-white p-5 shadow-panel md:p-6">
      <div class="mb-6 flex items-center justify-between gap-4 border-b border-slate-100 pb-4">
        <div>
          <h2 class="text-xl font-black"><?= e($groupTitle) ?></h2>
          <p class="mt-1 text-sm font-medium text-slate-500">Update <?= e(strtolower($groupTitle)) ?> content.</p>
        </div>
        <span class="grid h-10 w-10 place-items-center rounded-xl bg-blue-50 text-brand"><i data-lucide="settings-2" class="h-5 w-5"></i></span>
      </div>

      <div class="grid gap-5 md:grid-cols-2">
        <?php foreach ($fields as $key): ?>
          <?php
            $long = str_contains($key, 'text') || str_contains($key, 'description') || str_contains($key, 'message');
            $label = ucwords(str_replace('_', ' ', $key));
            $help = field_help($key);
          ?>
          <label class="field-label <?= $long ? 'md:col-span-2' : '' ?>">
            <div class="flex items-center gap-2">
              <i data-lucide="<?= e(field_icon($key)) ?>" class="h-4 w-4 text-slate-500"></i>
              <?= e($label) ?>
            </div>
            <?php if ($key === 'hero_image'): ?>
              <input class="admin-input" name="<?= e($key) ?>" value="<?= e(setting($key, 'assets/hero-device-mockup.png')) ?>">
              <input class="admin-input cursor-pointer border-dashed" name="hero_image_upload" type="file" accept="image/png,image/jpeg,image/webp,image/gif">
            <?php elseif ($long): ?>
              <textarea class="admin-input min-h-28 resize-y" name="<?= e($key) ?>"><?= e(setting($key)) ?></textarea>
            <?php else: ?>
              <input class="admin-input" name="<?= e($key) ?>" value="<?= e(setting($key)) ?>">
            <?php endif; ?>
            <?php if ($help): ?><span><?= e($help) ?></span><?php endif; ?>
          </label>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endforeach; ?>

  <div class="sticky bottom-0 z-20 -mx-5 border-t border-slate-200 bg-white/90 px-5 py-4 backdrop-blur md:-mx-8 md:px-8">
    <div class="mx-auto flex max-w-7xl justify-end">
      <button class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-7 py-4 font-black text-white shadow-lg shadow-blue-600/25 transition hover:-translate-y-0.5">
        <i data-lucide="save" class="h-5 w-5"></i> Save Changes
      </button>
    </div>
  </div>
</form>
<?php admin_footer(); ?>
