<?php $flashes = flash_drain(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign in · <?= e(App::config('app.name', 'SchoolERP')) ?></title>
<link rel="stylesheet" href="<?= e(App::url('assets/css/app.css')) ?>">
</head>
<body>
<div class="auth-wrap">
  <div class="auth-box">
    <div class="auth-head">
      <h1>SchoolERP</h1>
      <p>School Management ERP · Sign in to continue</p>
    </div>
    <div class="auth-body">
      <?php foreach ($flashes as $f): ?>
        <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
      <?php endforeach; ?>
      <form method="post" action="<?= e(App::url('login')) ?>">
        <?= csrf_field() ?>
        <div class="form-row">
          <label>Username</label>
          <input type="text" name="username" value="<?= e(old('username')) ?>" required autofocus autocomplete="username">
        </div>
        <div class="form-row">
          <label>Password</label>
          <input type="password" name="password" required autocomplete="current-password">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Sign in</button>
      </form>
      <div class="mt-2 text-muted" style="font-size:12px; text-align:center;">
        Default: admin / admin123
      </div>
    </div>
  </div>
</div>
</body>
</html>
