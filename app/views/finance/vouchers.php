<?php /** @var array $classes @var array $feesByClass @var array $vouchers */ ?>
<?php partial('finance/_nav'); ?>

<div class="row" style="align-items:flex-start;">
  <div class="card" style="flex:1; min-width:300px;">
    <div class="card-head"><h2>Bulk Fee Voucher Generator</h2></div>
    <div class="card-body">
      <form method="post" action="<?= e(App::url('finance/vouchers/generate')) ?>">
        <?= csrf_field() ?>
        <div class="form-row">
          <label>Classes *</label>
          <select name="class_ids[]" multiple required size="6" style="width:100%;">
            <?php foreach ($classes as $c): ?>
              <option value="<?= e($c['id']) ?>" <?= isset($feesByClass[$c['id']]) ? '' : 'disabled' ?>>
                <?= e($c['name']) ?> (<?= e($c['code']) ?>)<?= isset($feesByClass[$c['id']]) ? '' : ' — no fees' ?>
              </option>
            <?php endforeach; ?>
          </select>
          <span class="text-muted" style="font-size:12px;">Ctrl/Cmd+click to select multiple. Disabled classes have no fee structure.</span>
        </div>
        <div class="form-grid">
          <div class="form-row"><label>Issue Date</label>
            <input type="date" name="issue_date" value="<?= e(date('Y-m-d')) ?>"></div>
          <div class="form-row"><label>Due Date</label>
            <input type="date" name="due_date"></div>
        </div>
        <div class="form-row">
          <label>Label (optional)</label>
          <input type="text" name="label" placeholder="e.g. June Term, 1st Quarter">
        </div>
        <div class="form-row">
          <label>&nbsp;</label>
          <button class="btn btn-primary" type="submit">Generate Vouchers</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card" style="flex:1.4; min-width:320px;">
    <div class="card-head"><h2>Generated Vouchers (<?= count($vouchers) ?>)</h2></div>
    <div class="card-body flush">
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Voucher No.</th><th>Student</th><th>Class</th><th>Label</th><th>Total</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($vouchers as $v): ?>
            <tr>
              <td><a href="<?= e(App::url('finance/vouchers/' . $v['id'])) ?>"><strong><?= e($v['voucher_no']) ?></strong></a></td>
              <td><?= e($v['student_name']) ?><br><span class="text-muted" style="font-size:12px;"><?= e($v['admission_no']) ?></span></td>
              <td><?= e($v['class_name']) ?></td>
              <td><?= e($v['label'] ?: '—') ?></td>
              <td class="num"><strong><?= e(money($v['total_amount'])) ?></strong></td>
              <td>
                <?php if ($v['status'] === 'paid'): ?><span class="badge badge-success">Paid</span>
                <?php elseif ($v['status'] === 'cancelled'): ?><span class="badge badge-danger">Cancelled</span>
                <?php else: ?><span class="badge badge-warning">Issued</span><?php endif; ?>
              </td>
              <td class="text-right" style="white-space:nowrap;">
                <a class="btn btn-outline btn-sm" href="<?= e(App::url('finance/vouchers/' . $v['id'])) ?>">Print</a>
                <form method="post" action="<?= e(App::url('finance/vouchers/' . $v['id'] . '/delete')) ?>" style="display:inline;">
                  <?= csrf_field() ?>
                  <button class="btn btn-danger btn-sm" data-confirm="Delete this voucher?" <?= !is_admin() ? 'disabled title="Admins only"' : '' ?>>Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$vouchers): ?>
            <tr><td colspan="7"><div class="empty"><div class="big">🧾</div>No vouchers generated yet.</div></td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
