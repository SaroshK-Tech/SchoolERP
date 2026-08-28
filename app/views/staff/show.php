<?php /** @var array $staff @var ?array $basis @var array $payroll */ ?>
<?php require_role(['admin','accountant']); ?>
<div class="row between">
  <div style="display:flex; align-items:center; gap:16px;">
    <div class="avatar" style="width:56px; height:56px; font-size:22px;"><?= e(strtoupper(substr($staff['first_name'] ?? 'S', 0, 1))) ?></div>
    <div>
      <div style="font-size:20px; font-weight:700;"><?= e($staff['first_name'] . ' ' . $staff['last_name']) ?></div>
      <div class="text-muted"><?= e($staff['designation']) ?><?= $staff['department'] ? ' · ' . e($staff['department']) : '' ?></div>
      <div>
        <span class="badge badge-brand"><?= e(ucfirst($staff['role'])) ?></span>
        <?php if ($staff['is_active']): ?><span class="badge badge-success">Active</span>
        <?php else: ?><span class="badge badge-danger">Inactive</span><?php endif; ?>
      </div>
    </div>
  </div>
  <div class="row">
    <?php if (can_manage_staff((int)$staff['id'])): ?>
      <a class="btn btn-outline" href="<?= e(App::url('staff/edit/' . $staff['id'])) ?>">Edit</a>
    <?php else: ?>
      <span class="badge badge-danger">👑 Protected superadmin</span>
    <?php endif; ?>
    <a class="btn btn-outline" href="<?= e(App::url('staff')) ?>">Back</a>
  </div>
</div>

<div class="mt-2" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:24px;">
  <div class="card" style="margin-top:16px;">
    <div class="card-head"><h2>Personal Details</h2></div>
    <div class="card-body">
      <table class="table">
        <tr><td class="text-muted">Employee No.</td><td><strong><?= e($staff['employee_no']) ?></strong></td></tr>
        <tr><td class="text-muted">Date of Birth</td><td><?= e(fmt_date($staff['dob'])) ?></td></tr>
        <tr><td class="text-muted">Gender</td><td><?= e(ucfirst($staff['gender'])) ?></td></tr>
        <tr><td class="text-muted">Nationality</td><td><?= e($staff['nationality'] ?: '—') ?></td></tr>
        <tr><td class="text-muted">Phone</td><td><?= e($staff['phone'] ?: '—') ?></td></tr>
        <tr><td class="text-muted">Email</td><td><?= e($staff['email'] ?: '—') ?></td></tr>
        <tr><td class="text-muted">Address</td><td><?= e($staff['address'] ?: '—') ?></td></tr>
      </table>
    </div>
  </div>

  <div class="card" style="margin-top:16px;">
    <div class="card-head"><h2>Employment & Salary</h2></div>
    <div class="card-body">
      <table class="table">
        <tr><td class="text-muted">Join Date</td><td><?= e(fmt_date($staff['join_date'])) ?></td></tr>
        <tr><td class="text-muted">Leave Date</td><td><?= e(fmt_date($staff['leave_date'])) ?: '—' ?></td></tr>
        <tr><td class="text-muted">Basic Salary</td><td><?= e(money($basis['basic_salary'] ?? 0)) ?></td></tr>
        <tr><td class="text-muted">Allowances</td><td><?= e(money($basis['allowances'] ?? 0)) ?></td></tr>
        <tr><td class="text-muted">Deductions</td><td><?= e(money($basis['monthly_deductions'] ?? 0)) ?></td></tr>
        <tr><td class="text-muted">Net Monthly</td>
          <td><strong><?= e(money(($basis['basic_salary'] ?? 0) + ($basis['allowances'] ?? 0) - ($basis['monthly_deductions'] ?? 0))) ?></strong></td>
        </tr>
        <tr><td class="text-muted">Bank Account</td><td><?= e($basis['bank_account'] ?: '—') ?></td></tr>
      </table>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-head"><h2>Payroll History</h2><a class="btn btn-outline btn-sm" href="<?= e(App::url('finance/payroll')) ?>">Go to Payroll</a></div>
  <div class="card-body flush">
    <?php if (!$payroll): ?>
      <div class="empty"><div class="big">💵</div>No payroll records yet.</div>
    <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Period</th><th>Basic</th><th>Allowances</th><th>Deductions</th><th>Net Pay</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($payroll as $p): ?>
          <tr>
            <td><?= e($p['period_name']) ?></td>
            <td class="num"><?= e(money($p['basic_salary'])) ?></td>
            <td class="num"><?= e(money($p['allowances'] + $p['earnings'])) ?></td>
            <td class="num"><?= e(money($p['deductions'])) ?></td>
            <td class="num"><strong><?= e(money($p['net_pay'])) ?></strong></td>
            <td>
              <?php if ($p['status'] === 'paid'): ?><span class="badge badge-success">Paid</span>
              <?php else: ?><span class="badge badge-warning">Draft</span><?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
