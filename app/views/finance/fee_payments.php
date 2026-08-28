<?php /** @var array $records @var string $q */ ?>
<?php partial('finance/_nav'); ?>

<div class="card">
  <div class="card-head"><h2>Fee Payments (latest 200)</h2></div>
  <div class="card-body">
    <form method="get" action="<?= e(App::url('finance/fee-payments')) ?>">
      <div class="filters">
        <div class="form-row"><label>Search</label>
          <input type="text" name="q" value="<?= e($q) ?>" placeholder="Receipt, student, admission no..."></div>
        <div class="form-row"><button class="btn btn-outline" type="submit">Search</button></div>
      </div>
    </form>
  </div>
  <div class="card-body flush">
    <?php if (!$records): ?>
      <div class="empty"><div class="big">💳</div>No payments found.</div>
    <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Receipt</th><th>Student</th><th>Session</th><th>Amount</th><th>Date</th><th>Mode</th><th>Reference</th></tr></thead>
        <tbody>
        <?php foreach ($records as $p): ?>
          <tr>
            <td><?= e($p['receipt_no']) ?></td>
            <td><a href="<?= e(App::url('students/view/' . $p['student_id'])) ?>"><?= e($p['student_name']) ?></a></td>
            <td><?= e($p['session_name'] ?: '—') ?></td>
            <td class="num"><strong><?= e(money($p['amount'])) ?></strong></td>
            <td><?= e(fmt_date($p['paid_on'])) ?></td>
            <td><span class="badge badge-info"><?= e(ucfirst($p['mode'])) ?></span></td>
            <td><?= e($p['ref_no'] ?: '—') ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
