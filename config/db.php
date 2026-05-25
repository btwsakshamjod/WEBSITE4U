<?php
declare(strict_types=1);

session_start();

$localConfig = __DIR__ . '/local.php';
if (is_file($localConfig)) {
    require $localConfig;
} else {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'websites4u');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function setting(string $key, string $fallback = ''): string
{
    static $settings = null;

    if ($settings === null) {
        $settings = [];
        try {
            $rows = db()->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
            foreach ($rows as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Throwable $exception) {
            return $fallback;
        }
    }

    return $settings[$key] ?? $fallback;
}

function rows(string $table, string $orderBy = 'sort_order ASC, id ASC'): array
{
    $allowed = [
        'industries', 'stats', 'services', 'why_cards', 'projects',
        'features', 'pricing_plans', 'testimonials'
    ];

    if (!in_array($table, $allowed, true)) {
        return [];
    }

    try {
        return db()->query("SELECT * FROM {$table} WHERE is_active = 1 ORDER BY {$orderBy}")->fetchAll();
    } catch (Throwable $exception) {
        return [];
    }
}

function is_admin(): bool
{
    return !empty($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!is_admin()) {
        header('Location: login.php');
        exit;
    }
}

function whatsapp_link(string $message = ''): string
{
    $phone = preg_replace('/\D+/', '', setting('whatsapp_number', '919999999999'));
    $text = $message ?: setting('whatsapp_message', 'Hi Websites4U, I want a premium website');
    return 'https://wa.me/' . $phone . '?text=' . rawurlencode($text);
}
