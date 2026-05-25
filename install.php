<?php
declare(strict_types=1);

$host = $_POST['host'] ?? 'localhost';
$dbName = $_POST['database'] ?? 'websites4u';
$user = $_POST['user'] ?? 'root';
$pass = $_POST['pass'] ?? '';
$done = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO("mysql:host={$host};charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $sql = get_schema_sql();
        seed_data(new PDO("mysql:host={$host};dbname={$dbName};charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]), $sql);
        save_config($host, $dbName, $user, $pass);
        $done = true;
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

function get_schema_sql(): string
{
    $schemaPath = __DIR__ . '/database/schema.sql';
    if (is_file($schemaPath)) {
        $sql = file_get_contents($schemaPath);
        if (is_string($sql) && trim($sql) !== '') {
            return $sql;
        }
    }

    return <<<'SQL'
CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(120) NOT NULL UNIQUE,
  setting_value TEXT NULL
);

CREATE TABLE IF NOT EXISTS industries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  subtitle VARCHAR(180) NULL,
  image VARCHAR(255) NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS stats (
  id INT AUTO_INCREMENT PRIMARY KEY,
  icon VARCHAR(80) DEFAULT 'star',
  value_text VARCHAR(80) NOT NULL,
  label VARCHAR(160) NOT NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(180) NOT NULL,
  description TEXT NULL,
  logo VARCHAR(255) NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS why_cards (
  id INT AUTO_INCREMENT PRIMARY KEY,
  icon VARCHAR(80) DEFAULT 'star',
  title VARCHAR(180) NOT NULL,
  description TEXT NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS projects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category VARCHAR(160) NULL,
  title VARCHAR(180) NOT NULL,
  image VARCHAR(255) NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS features (
  id INT AUTO_INCREMENT PRIMARY KEY,
  icon VARCHAR(80) DEFAULT 'check-circle',
  title VARCHAR(180) NOT NULL,
  description TEXT NULL,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS pricing_plans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(180) NOT NULL,
  price VARCHAR(80) NOT NULL,
  description TEXT NULL,
  button_text VARCHAR(80) DEFAULT 'Chat Now',
  is_featured TINYINT(1) DEFAULT 0,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS pricing_features (
  id INT AUTO_INCREMENT PRIMARY KEY,
  plan_id INT NOT NULL,
  feature_text VARCHAR(255) NOT NULL,
  sort_order INT DEFAULT 0,
  FOREIGN KEY (plan_id) REFERENCES pricing_plans(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS testimonials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(180) NOT NULL,
  role VARCHAR(180) NULL,
  quote TEXT NOT NULL,
  image VARCHAR(255) NULL,
  rating INT DEFAULT 5,
  sort_order INT DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1
);

CREATE TABLE IF NOT EXISTS leads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(180) NOT NULL,
  phone VARCHAR(80) NOT NULL,
  business_type VARCHAR(180) NULL,
  message TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO admins (username, password_hash)
VALUES ('admin', '$2y$10$xpVOH8RPJnZL1J1S3gucB.HgmqOzs1vCZkOknsTw8eyEU7LrcslFi');
SQL;
}

function seed_data(PDO $pdo, string $schemaSql): void
{
    $pdo->exec($schemaSql);
    $settings = [
        'site_name' => 'Websites4U',
        'meta_title' => 'Websites4U | Premium Web Design Agency India',
        'meta_description' => 'Premium websites for Indian businesses with SEO, speed and WhatsApp lead generation.',
        'phone_number' => '+91 98765 43210',
        'whatsapp_number' => '919999999999',
        'email' => 'hello@websites4u.in',
        'instagram_url' => 'https://instagram.com/',
        'hero_image' => 'assets/hero-device-mockup.png',
        'hero_badge' => 'Premium Website Agency',
        'hero_title' => 'Premium websites that make your business look trusted.',
        'hero_highlight' => 'trusted.',
        'hero_description' => 'Modern websites for schools, colleges, ecommerce brands, cafes, hotels, startups, news portals and mobile apps.',
        'hero_primary_button' => 'Get Free Demo',
        'hero_secondary_button' => 'View Projects',
        'services_heading' => 'Premium Services Included',
        'services_subtitle' => 'Everything you need to launch, manage and grow your business online.',
        'about_heading' => 'Why Smart Businesses Choose Websites4U',
        'about_text' => 'We combine premium design with powerful technology to deliver websites that generate leads, build trust and grow your business.',
        'projects_heading' => 'Selected Projects',
        'projects_text' => 'Handpicked projects. Real results. Modern design. Powerful performance.',
        'pricing_heading' => 'Clear plans for serious launches.',
        'pricing_text' => 'Start lean, grow into custom development, and keep WhatsApp leads at the center.',
        'testimonials_heading' => 'Clients feel the upgrade immediately.',
        'contact_heading' => 'Ready for a premium website demo?',
        'contact_text' => 'Tell us your business type and we will suggest a launch-ready structure with pricing, pages and timeline.',
        'footer_description' => 'WEBSITES 4U builds modern, high-performance websites for Indian brands with clean UI, responsive design, fast loading speed, and conversion-focused development.',
        'footer_text' => 'Premium white-themed websites for Indian businesses that need trust, speed and more WhatsApp leads.',
        'whatsapp_message' => 'Hi Websites4U, I want a premium website'
    ];
    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare('INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)');
        $stmt->execute([$key, $value]);
    }

    insert_rows($pdo, 'industries', ['title', 'subtitle', 'image', 'sort_order'], [
        ['School Websites', 'Smart & Modern', 'assets/industry-school.svg', 1],
        ['College Portals', 'Professional & Fast', 'assets/industry-college.svg', 2],
        ['Cafe Websites', 'Online Ordering', 'assets/industry-cafe.svg', 3],
        ['Hotel Websites', 'Booking & Rooms', 'assets/industry-hotel.svg', 4],
        ['News Portals', 'SEO Optimized', 'assets/industry-news.svg', 5],
        ['Ecommerce Stores', 'High Converting', 'assets/industry-ecommerce.svg', 6],
        ['Mobile App UI', 'Beautiful & Smooth', 'assets/industry-mobile.svg', 7],
    ]);
    insert_rows($pdo, 'stats', ['icon', 'value_text', 'label', 'sort_order'], [
        ['package', '50+', 'Projects Delivered', 1],
        ['star', '100%', 'Client Satisfaction', 2],
        ['zap', 'Fast', 'Delivery', 3],
        ['smartphone', 'Mobile', 'Responsive', 4],
        ['message-circle', 'WhatsApp', 'Lead System', 5],
    ]);
    insert_rows($pdo, 'services', ['title', 'description', 'logo', 'sort_order'], [
        ['Custom Web Development', 'Powerful, scalable and custom website builds.', 'assets/logos/wordpress.svg', 1],
        ['E-commerce Development', 'Secure, high-converting online stores.', 'assets/logos/shopify.svg', 2],
        ['Web Design', 'Creative, modern and user-focused interfaces.', 'assets/logos/figma.svg', 3],
        ['Landing Pages', 'High-converting pages built for campaigns.', 'assets/logos/google-ads.svg', 4],
        ['Website SEO Optimization', 'Rank higher and get found by your audience.', 'assets/logos/google.svg', 5],
        ['Website Speed Optimization', 'Lightning-fast websites for better performance.', 'assets/logos/pagespeed.svg', 6],
        ['Web Hosting', 'Fast, secure and reliable hosting setup.', 'assets/logos/cpanel.svg', 7],
        ['React Development', 'Fast, dynamic and scalable React applications.', 'assets/logos/react.svg', 8],
    ]);
    insert_rows($pdo, 'why_cards', ['icon', 'title', 'description', 'sort_order'], [
        ['pen-tool', 'Premium UI/UX', 'Modern, clean and conversion-focused designs.', 1],
        ['search-check', 'SEO Ready', 'Built with SEO best practices to help you rank higher.', 2],
        ['rocket', 'Fast Delivery', 'On-time delivery without compromising quality.', 3],
        ['layout-dashboard', 'Admin Panel Included', 'Manage website content without coding.', 4],
        ['smartphone', 'Mobile First', 'Responsive websites that look perfect on every screen.', 5],
        ['message-circle', 'WhatsApp Lead System', 'Convert visitors into real customers.', 6],
    ]);
    insert_rows($pdo, 'projects', ['category', 'title', 'image', 'sort_order'], [
        ['Ecommerce Website', 'Premium product store', 'assets/ecommerce-laptop-case.png', 1],
        ['Cafe Website', 'Menu and orders', 'assets/industry-preview-cafe.png', 2],
        ['Hotel Website', 'Booking-ready', 'assets/industry-preview-hotel.png', 3],
    ]);
    insert_rows($pdo, 'features', ['icon', 'title', 'description', 'sort_order'], [
        ['smartphone', 'Mobile First Approach', 'Perfect experience on every device.', 1],
        ['sparkles', 'Clean & Modern Design', 'Built with clarity and simplicity.', 2],
        ['zap', 'Fast & Smooth Experience', 'Optimized for speed and performance.', 3],
        ['shield-check', 'Reliable & Secure', 'Your data and security are our priority.', 4],
    ]);
    insert_rows($pdo, 'pricing_plans', ['name', 'price', 'description', 'button_text', 'is_featured', 'sort_order'], [
        ['Starter Plan', '₹9,999+', 'Small business website with core conversion sections.', 'Chat Now', 0, 1],
        ['Business Plan', '₹24,999+', 'Premium UI, admin panel and growth features.', 'Chat Now', 1, 2],
        ['Premium Plan', 'Custom', 'Advanced workflows, dashboards and custom development.', 'Chat Now', 0, 3],
    ]);
    insert_rows($pdo, 'testimonials', ['name', 'role', 'quote', 'image', 'rating', 'sort_order'], [
        ['Priya Sharma', 'School Director', 'Our school website finally looks professional. Parents now send enquiries directly on WhatsApp.', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=160&q=80', 5, 1],
        ['Arjun Mehta', 'D2C Founder', 'The ecommerce design feels premium and the loading speed is excellent on mobile.', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=160&q=80', 5, 2],
        ['Neha Kapoor', 'Cafe Owner', 'Our cafe site made the brand look expensive. Menu enquiries increased in the first week.', 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=160&q=80', 5, 3],
    ]);
}

function save_config(string $host, string $dbName, string $user, string $pass): void
{
    $config = "<?php\n"
        . "define('DB_HOST', " . var_export($host, true) . ");\n"
        . "define('DB_NAME', " . var_export($dbName, true) . ");\n"
        . "define('DB_USER', " . var_export($user, true) . ");\n"
        . "define('DB_PASS', " . var_export($pass, true) . ");\n";
    file_put_contents(__DIR__ . '/config/local.php', $config);
}

function insert_rows(PDO $pdo, string $table, array $columns, array $rows): void
{
    $count = (int) $pdo->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
    if ($count > 0) {
        return;
    }
    $placeholders = implode(',', array_fill(0, count($columns), '?'));
    $sql = "INSERT INTO {$table} (" . implode(',', $columns) . ") VALUES ({$placeholders})";
    $stmt = $pdo->prepare($sql);
    foreach ($rows as $row) {
        $stmt->execute($row);
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Install Websites4U CMS</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 p-6 font-sans">
  <main class="mx-auto max-w-xl rounded-2xl bg-white p-8 shadow-xl">
    <h1 class="text-3xl font-black">Install Websites4U CMS</h1>
    <?php if ($done): ?>
      <div class="mt-6 rounded-xl bg-emerald-50 p-4 font-bold text-emerald-700">Database ready. Admin login: admin / admin123</div>
      <a class="mt-6 inline-flex rounded-full bg-blue-600 px-5 py-3 font-bold text-white" href="admin/login.php">Open Admin Panel</a>
    <?php else: ?>
      <?php if ($error): ?><div class="mt-4 rounded-xl bg-red-50 p-4 text-red-700"><?= htmlspecialchars($error) ?></div><?php endif; ?>
      <form method="post" class="mt-6 grid gap-4">
        <label class="grid gap-2 font-bold">Host <input class="rounded-xl border p-3" name="host" value="<?= htmlspecialchars($host) ?>"></label>
        <label class="grid gap-2 font-bold">Database <input class="rounded-xl border p-3" name="database" value="<?= htmlspecialchars($dbName) ?>"></label>
        <label class="grid gap-2 font-bold">User <input class="rounded-xl border p-3" name="user" value="<?= htmlspecialchars($user) ?>"></label>
        <label class="grid gap-2 font-bold">Password <input class="rounded-xl border p-3" name="pass" type="password" value="<?= htmlspecialchars($pass) ?>"></label>
        <button class="rounded-full bg-blue-600 px-5 py-3 font-black text-white">Install Database</button>
      </form>
    <?php endif; ?>
  </main>
</body>
</html>
