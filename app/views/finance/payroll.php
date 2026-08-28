<?php /** @var array $periods */ ?>
<?php partial('finance/_nav'); ?>

<div class="card">
  <div class="card-head">
    <h2>Payroll Periods</h2>
    <a class="btn btn-primary" href="<?= e(App::url('finance/payroll/generate')) ?>">+ Generate Payroll</a>
  </div>
  <div class="card-body flush">
    <?php if (!$periods): ?>
      <div class="empty"><div class="big">💵</div>No payroll periods yet. Click "Generate Payroll".</div>
    <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Period</th><th>Period</th><th>Days</th><th>Status</th><th>Entries</th><th>Gross</th><th>Net</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($periods as $p): $g = Database::one("SELECT COUNT(*) c, COALESCE(SUM(basic_salary+allowances),0) gross, COALESCE(SUM(net_pay),0) net FROM payroll_entries WHERE period_id=?", [(int)$p['id']]); ?>
          <tr>
            <td><strong><?= e($p['name']) ?></strong></td>
            <td><?= e(fmt_date($p['period_start'])) ?> → <?= e(fmt_date($p['period_end'])) ?></td>
            <td><?= (int)round((strtotime($p['period_end']) - strtotime($p['period_start'])) / 86400) + 1 ?></td>
            <td>
              <?php if ($p['is_paid']): ?><span class="badge badge-success">Paid</span>
              <?php else: ?><span class="badge badge-warning">Draft</span><?php endif; ?>
            </td>
            <td><?= (int)$g['c'] ?></td>
            <td class="num"><?= e(money($g['gross'])) ?></td>
            <td class="num"><strong><?= e(money($g['net'])) ?></strong></td>
            <td class="text-right">
              <?php if (!$p['is_paid']): ?>
                <form method="post" action="<?= e(App::url('finance/payroll/' . $p['id'] . '/mark-paid')) ?>" style="display:inline;">
                  <?= csrf_field() ?>
                  <button class="btn btn-success btn-sm" data-confirm="Mark this entire payroll as paid?">Mark Paid</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
