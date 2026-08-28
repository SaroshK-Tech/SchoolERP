<?php /** @var string $path */ ?>
<div class="empty" style="padding-top:80px;">
  <div class="big">🗺️</div>
  <h2 style="margin:8px 0;">404 - Page not found</h2>
  <p class="text-muted">The page <code><?= e($path) ?></code> does not exist.</p>
  <a class="btn btn-primary" href="<?= e(App::url('dashboard')) ?>">Go to Dashboard</a>
</div>
