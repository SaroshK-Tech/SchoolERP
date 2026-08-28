<?php /** @var array $stats @var array $recent */ ?>

<div class="row between" style="margin-bottom:20px;">
  <div class="row">
    <a class="btn btn-primary" href="<?= e(App::url('notifications/send')) ?>">+ Send Notification</a>
    <a class="btn btn-outline" href="<?= e(App::url('notifications/logs')) ?>">View All Logs</a>
  </div>
  <span class="badge <?= (bool)App::config('notifications.dry_run', true) ? 'badge-warning' : 'badge-success' ?>">
    <?= (bool)App::config('notifications.dry_run', true) ? 'Dry-run mode (not sending)' : 'Live delivery' ?>
  </span>
</div>

<div class="stats">
  <div class="stat"><div class="stat-ico" style="background:#6366f1;">📨</div><div><div class="stat-label">Total</div><div class="stat-value"><?= e($stats['total']) ?></div></div></div>
  <div class="stat"><div class="stat-ico" style="background:#10b981;">✅</div><div><div class="stat-label">Sent</div><div class="stat-value"><?= e($stats['sent']) ?></div></div></div>
  <div class="stat"><div class="stat-ico" style="background:#f59e0b;">⏳</div><div><div class="stat-label">Queued</div><div class="stat-value"><?= e($stats['queued']) ?></div></div></div>
  <div class="stat"><div class="stat-ico" style="background:#dc2626;">❌</div><div><div class="stat-label">Failed</div><div class="stat-value"><?= e($stats['failed']) ?></div></div></div>
</div>

<div class="card">
  <div class="card-head"><h2>Recent Notifications</h2></div>
  <div class="card-body flush">
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>ID</th><th>Channel</th><th>To</th><th>Recipient</th><th>Subject</th><th>Status</th><th>Sent</th></tr></thead>
        <tbody>
        <?php foreach ($recent as $n): ?>
          <tr>
            <td><?= e($n['id']) ?></td>
            <td><span class="badge <?= $n['channel']==='whatsapp' ? 'badge-success' : 'badge-info' ?>"><?= e(strtoupper($n['channel'])) ?></span></td>
            <td><?= e($n['to_phone']) ?></td>
            <td><?= e($n['recipient_name'] ?: '—') ?></td>
            <td><?= e($n['subject'] ?: '—') ?></td>
            <td>
              <?php $c = ['queued'=>'warning','sent'=>'success','failed'=>'danger']; ?>
              <span class="badge badge-<?= $c[$n['status']] ?? 'muted' ?>"><?= e(ucfirst($n['status'])) ?></span>
            </td>
            <td><?= e(fmt_date_time($n['sent_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$recent): ?>
          <tr><td colspan="7"><div class="empty"><div class="big">📲</div>No notifications sent yet.</div></td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
