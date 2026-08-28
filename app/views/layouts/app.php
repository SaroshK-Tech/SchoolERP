<?php
/** @var string $title @var string $content @var array $flashes @var ?array $user */
$appName = App::config('app.name', 'SchoolERP');
$nav = [
    'Dashboard' => ['dashboard', '🏠'],
    'Sessions' => ['sessions', '📅'],
    'Staff' => ['staff', '👥'],
    'Students' => ['students', '🎓'],
    'Classes' => ['classes', '🏫'],
    'Finance' => ['finance', '💰'],
    'Exams & Results' => ['exams', '📝'],
    'Timetable' => ['timetable', '⏰'],
    'Notifications' => ['notifications', '📲'],
];
$current = $page ?? '';
$activePath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$activePath = '/' . ltrim($activePath, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> · <?= e($appName) ?></title>
<link rel="stylesheet" href="<?= e(App::url('assets/css/app.css')) ?>">
</head>
<body>
<div class="layout">
  <aside class="sidebar">
    <div class="brand">
      <span class="logo-dot">S</span>
      <?= e($appName) ?>
    </div>
    <nav>
      <?php foreach ($nav as $label => [$route, $icon]): ?>
        <?php
          $prefix = '/' . $route;
          $isActive = $activePath === $prefix
                      || str_starts_with($activePath, $prefix . '/')
                      || ($route === 'finance' && str_starts_with($activePath, '/'.$route));
        ?>
        <a class="nav-link <?= $isActive ? 'active' : '' ?>" href="<?= e(App::url($route)) ?>">
          <span class="ico"><?= $icon ?></span> <?= e($label) ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="sidebar-foot">
      <div><strong>v<?= e(App::config('app.version')) ?></strong></div>
      <div>School Management ERP</div>
    </div>
  </aside>

  <div class="main">
    <header class="topbar">
      <div>
        <div class="page-title"><?= e($title) ?></div>
        <?php if (!empty($pageSub)): ?><div class="breadcrumb"><?= e($pageSub) ?></div><?php endif; ?>
      </div>
      <div class="user-menu">
        <div class="text-right">
          <div style="font-weight:600; font-size:13px;"><?= e($user['full_name'] ?? $user['username'] ?? '') ?></div>
          <div class="text-muted" style="font-size:12px; text-transform:capitalize;"><?= e($user['role'] ?? '') ?></div>
        </div>
        <div class="avatar"><?= e(strtoupper(substr($user['full_name'] ?? ($user['username'] ?? 'U'), 0, 1))) ?></div>
      </div>
    </header>

    <main class="content">
      <?php foreach ($flashes as $f): ?>
        <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
      <?php endforeach; ?>
      <?= $content ?>
    </main>
  </div>
</div>
<script src="<?= e(App::url('assets/js/app.js')) ?>"></script>
</body>
</html>
