<?php /** @var array $staff */ ?>
<?php partial('finance/_nav'); ?>

<form method="post" action="<?= e(App::url('finance/payroll/generate')) ?>">
  <?= csrf_field() ?>
  <div class="card">
    <div class="card-head"><h2>Payroll Period</h2></div>
    <div class="card-body">
      <div class="form-grid">
        <div class="form-row"><label>Period Name *</label>
          <input type="text" name="name" placeholder="e.g. January 2025" required></div>
        <div class="form-row"><label>Start Date *</label>
          <input type="date" name="period_start" required></div>
        <div class="form-row"><label>End Date *</label>
          <input type="date" name="period_end" required></div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-head"><h2>Select Staff</h2>
      <span class="text-muted" style="font-size:12.5px;">Entries use each staff member's saved salary basis.</span>
    </div>
    <div class="card-body flush">
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th><input type="checkbox" data-check-all="#payrollStaff input.staff-cb" checked></th>
              <th>Employee</th><th>Role</th><th>Basic</th><th>Allowances</th><th>Net (projected)</th>
            </tr>
          </thead>
          <tbody id="payrollStaff">
          <?php foreach ($staff as $s): ?>
            <tr>
              <td><input type="checkbox" class="staff-cb" name="staff_ids[]" value="<?= e($s['id']) ?>" checked></td>
              <td><?= e($s['employee_no']) ?> — <strong><?= e($s['full_name']) ?></strong></td>
              <td><span class="badge badge-brand"><?= e(ucfirst($s['role'])) ?></span></td>
              <td class="num"><?= e(money($s['basic_salary'] ?? 0)) ?></td>
              <td class="num"><?= e(money($s['allowances'] ?? 0)) ?></td>
              <td class="num"><strong><?= e(money(($s['basic_salary'] ?? 0) + ($s['allowances'] ?? 0) - ($s['monthly_deductions'] ?? 0))) ?></strong></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$staff): ?>
            <tr><td colspan="6"><div class="empty"><div class="big">👥</div>No active staff with salary basis. <a href="<?= e(App::url('staff/create')) ?>">Add staff</a></div></td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-body">
      <button class="btn btn-primary" type="submit">Generate Payroll</button>
      <a class="btn btn-outline" href="<?= e(App::url('finance/payroll')) ?>">Cancel</a>
    </div>
  </div>
</form>
