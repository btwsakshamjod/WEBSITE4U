<?php
require_once __DIR__ . '/../config/db.php';

if (is_admin()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = db()->prepare('SELECT * FROM admins WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: index.php');
        exit;
    }
    $error = 'Wrong username or password.';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="grid min-h-screen place-items-center bg-slate-100 p-6 font-sans">
  <form method="post" class="w-full max-w-md rounded-2xl bg-white p-8 shadow-xl">
    <h1 class="text-3xl font-black">Admin Login</h1>
    <p class="mt-2 text-slate-500">Default: admin / admin123</p>
    <?php if ($error): ?><div class="mt-4 rounded-xl bg-red-50 p-3 font-bold text-red-700"><?= e($error) ?></div><?php endif; ?>
    <div class="mt-6 grid gap-4">
      <input class="rounded-xl border border-slate-200 p-4" name="username" placeholder="Username" required>
      <input class="rounded-xl border border-slate-200 p-4" name="password" placeholder="Password" type="password" required>
      <button class="rounded-full bg-blue-600 px-5 py-4 font-black text-white">Login</button>
    </div>
  </form>
</body>
</html>
