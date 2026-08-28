<?php /** @var array $logs @var string $channel @var string $status */ ?>

<div class="card">
  <div class="card-head"><h2>Notification Logs</h2><a class="btn btn-outline" href="<?= e(App::url('notifications/send')) ?>">Send New</a></div>
  <div class="card-body">
    <form method="get" action="<?= e(App::url('notifications/logs')) ?>">
      <div class="filters">
        <div class="form-row"><label>Channel</label>
          <select name="channel" data-auto-submit>
            <option value="">All</option>
            <option value="whatsapp" <?= $channel==='whatsapp'?'selected':'' ?>>WhatsApp</option>
            <option value="sms" <?= $channel==='sms'?'selected':'' ?>>SMS</option>
          </select></div>
        <div class="form-row"><label>Status</label>
          <select name="status" data-auto-submit>
            <option value="">All</option>
            <option value="queued" <?= $status==='queued'?'selected':'' ?>>Queued</option>
            <option value="sent" <?= $status==='sent'?'selected':'' ?>>Sent</option>
            <option value="failed" <?= $status==='failed'?'selected':'' ?>>Failed</option>
          </select></div>
      </div>
    </form>
  </div>
  <div class="card-body flush">
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>ID</th><th>Channel</th><th>To</th><th>Recipient</th><th>Subject</th><th>Message</th><th>Status</th><th>Sent</th><th>Error</th></tr></thead>
        <tbody>
        <?php foreach ($logs as $n): ?>
          <tr>
            <td><?= e($n['id']) ?></td>
            <td><span class="badge <?= $n['channel']==='whatsapp'?'badge-success':'badge-info' ?>"><?= e(strtoupper($n['channel'])) ?></span></td>
            <td><?= e($n['to_phone']) ?></td>
            <td><?= e($n['recipient_name'] ?: '—') ?></td>
            <td><?= e($n['subject'] ?: '—') ?></td>
            <td><span title="<?= e($n['message']) ?>" style="cursor:help;"><?= e(mb_strimwidth($n['message'], 0, 40, '…')) ?></span></td>
            <td><?php $c=['queued'=>'warning','sent'=>'success','failed'=>'danger']; ?><span class="badge badge-<?= $c[$n['status']] ?? 'muted' ?>"><?= e(ucfirst($n['status'])) ?></span></td>
            <td><?= e(fmt_date_time($n['sent_at'])) ?></td>
            <td><span class="text-danger" style="color:var(--danger);"><?= e($n['error'] ?: '') ?></span></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$logs): ?>
          <tr><td colspan="9"><div class="empty"><div class="big">📲</div>No logs found.</div></td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
