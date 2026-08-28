<?php /** @var array $structures @var array $classes @var array $students */ ?>
<?php partial('finance/_nav'); ?>

<div class="row" style="align-items:flex-start;">
  <div class="card" style="flex:1.5; min-width:340px;">
    <div class="card-head"><h2>Fee Structures</h2></div>
    <div class="card-body flush">
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Class</th><th>Fee Type</th><th>Amount</th><th>Frequency</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($structures as $fs): ?>
            <tr>
              <td><?= e($fs['class_name']) ?></td>
              <td><?= e($fs['fee_type']) ?></td>
              <td class="num"><strong><?= e(money($fs['amount'])) ?></strong></td>
              <td><span class="badge badge-info"><?= e(ucfirst($fs['frequency'])) ?></span></td>
              <td class="text-right">
                <form method="post" action="<?= e(App::url('finance/fees/' . $fs['id'] . '/delete')) ?>" style="display:inline;">
                  <?= csrf_field() ?>
                  <button class="btn btn-danger btn-sm" data-confirm="Remove this fee structure?">Remove</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$structures): ?>
            <tr><td colspan="5"><div class="empty"><div class="big">💰</div>No fee structures defined.</div></td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card-head" style="border-top:1px solid var(--line);"><h2>Add Fee Structure</h2></div>
    <div class="card-body">
      <form method="post" action="<?= e(App::url('finance/fees/create')) ?>">
        <?= csrf_field() ?>
        <div class="form-grid">
          <div class="form-row"><label>Class *</label>
            <select name="class_id" required>
              <option value="">Select class</option>
              <?php foreach ($classes as $c): ?><option value="<?= e($c['id']) ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
            </select></div>
          <div class="form-row"><label>Fee Type *</label>
            <input type="text" name="fee_type" placeholder="e.g. Tuition" required></div>
          <div class="form-row"><label>Amount *</label>
            <input type="number" step="0.01" name="amount" required></div>
          <div class="form-row"><label>Frequency</label>
            <select name="frequency">
              <?php foreach (['monthly','term','yearly','one-time'] as $f): ?><option value="<?= e($f) ?>"><?= e(ucfirst($f)) ?></option><?php endforeach; ?>
            </select></div>
        </div>
        <button class="btn btn-primary" type="submit">Add Fee Structure</button>
      </form>
    </div>
  </div>

  <div class="card" style="flex:1; min-width:300px;">
    <div class="card-head"><h2>Collect Fee</h2><a class="btn btn-outline btn-sm" href="<?= e(App::url('finance/fee-payments')) ?>">View Payments</a></div>
    <div class="card-body">
      <form method="post" action="<?= e(App::url('finance/fees/collect')) ?>">
        <?= csrf_field() ?>
        <div class="form-row"><label>Student *</label>
          <select name="student_id" required>
            <option value="">Select student</option>
            <?php foreach ($students as $st): ?>
              <option value="<?= e($st['id']) ?>"><?= e($st['admission_no']) ?> — <?= e($st['student_name']) ?><?= $st['class_name'] ? ' (' . e($st['class_name']) . e($st['section_name'] ? '-' . $st['section_name'] : '') . ')' : '' ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="form-row"><label>Amount *</label>
          <input type="number" step="0.01" name="amount" required></div>
        <div class="form-grid">
          <div class="form-row"><label>Date</label>
            <input type="date" name="paid_on" value="<?= e(date('Y-m-d')) ?>"></div>
          <div class="form-row"><label>Mode</label>
            <select name="mode">
              <?php foreach (['cash','bank','card','online','other'] as $m): ?><option value="<?= e($m) ?>"><?= e(ucfirst($m)) ?></option><?php endforeach; ?>
            </select></div>
        </div>
        <div class="form-row"><label>Reference No.</label>
          <input type="text" name="ref_no"></div>
        <div class="form-row"><label>Notes</label>
          <textarea name="notes" rows="2"></textarea></div>
        <button class="btn btn-success" type="submit">Collect Payment</button>
      </form>
    </div>
  </div>
</div>
