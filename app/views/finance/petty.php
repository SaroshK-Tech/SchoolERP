<?php /** @var array $records @var array $totals @var string $type */ ?>
<?php partial('finance/_nav'); ?>

<div class="stats">
  <div class="stat">
    <div class="stat-ico" style="background:#16a34a;">⬆️</div>
    <div><div class="stat-label">Total Income</div><div class="stat-value"><?= e(money($totals['income'])) ?></div></div>
  </div>
  <div class="stat">
    <div class="stat-ico" style="background:#dc2626;">⬇️</div>
    <div><div class="stat-label">Total Expense</div><div class="stat-value"><?= e(money($totals['expense'])) ?></div></div>
  </div>
  <div class="stat">
    <div class="stat-ico" style="background:#0ea5e9;">⚖️</div>
    <div><div class="stat-label">Net</div><div class="stat-value"><?= e(money($totals['income'] - $totals['expense'])) ?></div></div>
  </div>
</div>

<div class="row" style="align-items:flex-start;">
  <div class="card" style="flex:1.5; min-width:340px;">
    <div class="card-head"><h2>Petty Ledger</h2>
      <div class="row">
        <a class="btn btn-outline btn-sm <?= $type===''?'btn-primary':'' ?>" href="<?= e(App::url('finance/petty')) ?>">All</a>
        <a class="btn btn-outline btn-sm <?= $type==='income'?'btn-primary':'' ?>" href="<?= e(App::url('finance/petty?type=income')) ?>">Income</a>
        <a class="btn btn-outline btn-sm <?= $type==='expense'?'btn-primary':'' ?>" href="<?= e(App::url('finance/petty?type=expense')) ?>">Expense</a>
      </div>
    </div>
    <div class="card-body flush">
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Date</th><th>Type</th><th>Category</th><th>Description</th><th>Amount</th><th>Ref</th></tr></thead>
          <tbody>
          <?php foreach ($records as $r): ?>
            <tr>
              <td><?= e(fmt_date($r['entry_date'])) ?></td>
              <td>
                <?php if ($r['type'] === 'income'): ?><span class="badge badge-success">Income</span>
                <?php else: ?><span class="badge badge-danger">Expense</span><?php endif; ?>
              </td>
              <td><?= e($r['category']) ?></td>
              <td><?= e($r['description'] ?: '—') ?></td>
              <td class="num"><strong><?= e(money($r['amount'])) ?></strong></td>
              <td><?= e($r['ref_no'] ?: '—') ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$records): ?>
            <tr><td colspan="6"><div class="empty"><div class="big">🧾</div>No entries found.</div></td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="card" style="flex:1; min-width:300px;">
    <div class="card-head"><h2>Add Entry</h2></div>
    <div class="card-body">
      <form method="post" action="<?= e(App::url('finance/petty/create')) ?>">
        <?= csrf_field() ?>
        <div class="form-row"><label>Type *</label>
          <select name="type" required>
            <option value="income">Income</option>
            <option value="expense">Expense</option>
          </select></div>
        <div class="form-row"><label>Category *</label>
          <input type="text" name="category" placeholder="e.g. Stationery, Donation" required></div>
        <div class="form-row"><label>Amount *</label>
          <input type="number" step="0.01" name="amount" required></div>
        <div class="form-row"><label>Date</label>
          <input type="date" name="entry_date" value="<?= e(date('Y-m-d')) ?>"></div>
        <div class="form-row"><label>Description</label>
          <textarea name="description" rows="2"></textarea></div>
        <div class="form-row"><label>Reference No.</label>
          <input type="text" name="ref_no"></div>
        <button class="btn btn-primary" type="submit">Save Entry</button>
      </form>
    </div>
  </div>
</div>
