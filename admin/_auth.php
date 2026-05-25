<?php
require_once __DIR__ . '/../config/db.php';

function admin_header(string $title, string $subtitle = ''): void
{
    require_admin();
    $current = basename($_SERVER['PHP_SELF']);
    $type = $_GET['type'] ?? '';
    $nav = [
        ['href' => 'index.php', 'label' => 'Settings', 'icon' => 'settings', 'active' => $current === 'index.php'],
        ['href' => 'manage.php?type=industries', 'label' => 'Industries', 'icon' => 'blocks', 'active' => $type === 'industries'],
        ['href' => 'manage.php?type=stats', 'label' => 'Stats', 'icon' => 'bar-chart-3', 'active' => $type === 'stats'],
        ['href' => 'manage.php?type=services', 'label' => 'Services', 'icon' => 'panel-top', 'active' => $type === 'services'],
        ['href' => 'manage.php?type=why_cards', 'label' => 'Why Cards', 'icon' => 'badge-help', 'active' => $type === 'why_cards'],
        ['href' => 'manage.php?type=projects', 'label' => 'Projects', 'icon' => 'briefcase-business', 'active' => $type === 'projects'],
        ['href' => 'manage.php?type=features', 'label' => 'Features', 'icon' => 'sparkles', 'active' => $type === 'features'],
        ['href' => 'manage.php?type=pricing_plans', 'label' => 'Pricing', 'icon' => 'badge-indian-rupee', 'active' => $type === 'pricing_plans'],
        ['href' => 'manage.php?type=testimonials', 'label' => 'Testimonials', 'icon' => 'message-square-quote', 'active' => $type === 'testimonials'],
        ['href' => 'leads.php', 'label' => 'Leads', 'icon' => 'inbox', 'active' => $current === 'leads.php'],
    ];
    ?>
    <!doctype html>
    <html lang="en">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title><?= e($title) ?> - Admin</title>
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
      <script src="https://cdn.tailwindcss.com"></script>
      <script>
        tailwind.config = {
          theme: {
            extend: {
              fontFamily: { sans: ['Inter', 'ui-sans-serif', 'system-ui'] },
              colors: { brand: '#2563eb', ink: '#0f172a' },
              boxShadow: { panel: '0 24px 80px rgba(15, 23, 42, .08)' }
            }
          }
        }
      </script>
      <style>
        * { box-sizing: border-box; }
        body { font-family: Inter, ui-sans-serif, system-ui; }
        .admin-input {
          width: 100%;
          border: 1px solid #dbe3ef;
          border-radius: 14px;
          background: #fff;
          padding: 14px 16px;
          color: #263248;
          outline: none;
          transition: border-color .18s ease, box-shadow .18s ease;
        }
        .admin-input:focus {
          border-color: #93b4ff;
          box-shadow: 0 0 0 4px rgba(37, 99, 235, .10);
        }
        .field-label { display: grid; gap: 10px; font-weight: 800; color: #111827; }
        .field-label span { color: #64748b; font-size: 13px; font-weight: 600; }
      </style>
    </head>
    <body class="min-h-screen bg-[#f5f8fc] text-ink">
      <div class="min-h-screen lg:flex">
        <aside class="lg:fixed lg:inset-y-0 lg:left-0 lg:w-72 bg-slate-950 text-white">
          <div class="flex min-h-full flex-col p-5">
            <a href="index.php" class="flex items-center gap-3 px-2 py-3">
              <span class="grid h-9 w-9 place-items-center rounded-xl bg-brand text-lg font-black shadow-lg shadow-blue-600/30">W</span>
              <span class="text-xl font-black"><span class="text-blue-400">Websites4U</span> Admin</span>
            </a>
            <nav class="mt-7 grid gap-1.5 text-[15px] font-bold">
              <?php foreach ($nav as $item): ?>
                <a class="flex items-center gap-3 rounded-xl px-4 py-3.5 transition <?= $item['active'] ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/25' : 'text-slate-200 hover:bg-white/10 hover:text-white' ?>" href="<?= e($item['href']) ?>">
                  <i data-lucide="<?= e($item['icon']) ?>" class="h-5 w-5"></i>
                  <?= e($item['label']) ?>
                </a>
              <?php endforeach; ?>
            </nav>
            <div class="mt-auto grid gap-3 pt-8">
              <a class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3.5 font-black text-white shadow-lg shadow-blue-600/25 transition hover:-translate-y-0.5" href="../index.php" target="_blank">
                View Website <i data-lucide="external-link" class="h-4 w-4"></i>
              </a>
              <a class="inline-flex items-center justify-center gap-2 rounded-xl bg-white/10 px-4 py-3.5 font-black text-white transition hover:bg-white/15" href="logout.php">
                Logout <i data-lucide="log-out" class="h-4 w-4"></i>
              </a>
              <p class="px-2 pt-8 text-sm leading-6 text-slate-400">&copy; 2026 Websites4U<br>All rights reserved.</p>
            </div>
          </div>
        </aside>
        <main class="min-w-0 flex-1 lg:ml-72">
          <header class="sticky top-0 z-30 border-b border-slate-200/70 bg-[#f5f8fc]/85 px-5 py-5 backdrop-blur-xl md:px-8">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4">
              <div>
                <h1 class="text-3xl font-black tracking-tight md:text-4xl"><?= e($title) ?></h1>
                <p class="mt-1 text-base font-medium text-slate-500"><?= e($subtitle ?: 'Manage your website content from one place.') ?></p>
              </div>
              <div class="hidden items-center gap-3 md:flex">
                <span class="grid h-11 w-11 place-items-center rounded-full bg-slate-200 text-lg font-black text-slate-700">A</span>
                <div>
                  <div class="font-black">Admin</div>
                  <div class="text-sm font-medium text-slate-500">Super Admin</div>
                </div>
              </div>
            </div>
          </header>
          <div class="mx-auto max-w-7xl px-5 py-6 md:px-8">
    <?php
}

function admin_footer(): void
{
    ?>
          </div>
        </main>
      </div>
      <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
      <script>if (window.lucide) lucide.createIcons();</script>
    </body>
    </html>
    <?php
}

function flash(): ?string
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $message;
}

function set_flash(string $message): void
{
    $_SESSION['flash'] = $message;
}
