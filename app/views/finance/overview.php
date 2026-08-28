<?php /** @var array $stats */ ?>
<?php partial('finance/_nav'); ?>

<div class="stats">
  <div class="stat">
    <div class="stat-ico" style="background:#10b981;">📊</div>
    <div>
      <div class="stat-label">Total Fees Collected</div>
      <div class="stat-value"><?= e(money($stats['fees_collected'])) ?></div>
    </div>
  </div>
  <div class="stat">
    <div class="stat-ico" style="background:#0ea5e9;">🗓️</div>
    <div>
      <div class="stat-label">This Month (Fees)</div>
      <div class="stat-value"><?= e(money($stats['month_fees'])) ?></div>
    </div>
  </div>
  <div class="stat">
    <div class="stat-ico" style="background:#16a34a;">⬆️</div>
    <div>
      <div class="stat-label">Petty Income</div>
      <div class="stat-value"><?= e(money($stats['petty_income'])) ?></div>
    </div>
  </div>
  <div class="stat">
    <div class="stat-ico" style="background:#dc2626;">⬇️</div>
    <div>
      <div class="stat-label">Petty Expense</div>
      <div class="stat-value"><?= e(money($stats['petty_expense'])) ?></div>
    </div>
  </div>
  <div class="stat">
    <div class="stat-ico" style="background:#7c3aed;">💵</div>
    <div>
      <div class="stat-label">Payroll Paid</div>
      <div class="stat-value"><?= e(money($stats['payroll_paid'])) ?></div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-head">
    <h2>Quick Actions</h2>
  </div>
  <div class="card-body">
    <div class="row">
      <a class="btn btn-outline" href="<?= e(App::url('finance/fees')) ?>">Collect Fee</a>
      <a class="btn btn-outline" href="<?= e(App::url('finance/payroll/generate')) ?>">Generate Payroll</a>
      <a class="btn btn-outline" href="<?= e(App::url('finance/petty')) ?>">Record Petty Entry</a>
    </div>
  </div>
</div>
