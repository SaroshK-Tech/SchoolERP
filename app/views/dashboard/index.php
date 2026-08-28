<?php /** @var array $stats @var array $recentPayments @var array $upcomingExams */ ?>

<div class="stats">
  <div class="stat">
    <div class="stat-ico" style="background:#6366f1;">👥</div>
    <div>
      <div class="stat-label">Active Staff</div>
      <div class="stat-value"><?= e($stats['staff_total']) ?></div>
      <div class="text-muted" style="font-size:12px;"><?= e($stats['staff_teachers']) ?> teachers</div>
    </div>
  </div>
  <div class="stat">
    <div class="stat-ico" style="background:#0ea5e9;">🎓</div>
    <div>
      <div class="stat-label">Students</div>
      <div class="stat-value"><?= e($stats['students_total']) ?></div>
      <div class="text-muted" style="font-size:12px;"><?= e($stats['students_active']) ?> active</div>
    </div>
  </div>
  <div class="stat">
    <div class="stat-ico" style="background:#f59e0b;">🏫</div>
    <div>
      <div class="stat-label">Classes / Sections</div>
      <div class="stat-value"><?= e($stats['classes']) ?> / <?= e($stats['sections']) ?></div>
    </div>
  </div>
  <div class="stat">
    <div class="stat-ico" style="background:#10b981;">💳</div>
    <div>
      <div class="stat-label">Fees Collected Today</div>
      <div class="stat-value"><?= e(money($stats['today_income'])) ?></div>
    </div>
  </div>
</div>

<div class="row">
  <div class="card" style="flex:1.6; min-width:320px;">
    <div class="card-head">
      <h2>Recent Fee Payments</h2>
      <a class="btn btn-outline btn-sm" href="<?= e(App::url('finance/fees')) ?>">Collect fee</a>
    </div>
    <div class="card-body flush">
      <?php if (!$recentPayments): ?>
        <div class="empty"><div class="big">💳</div>No payments recorded yet.</div>
      <?php else: ?>
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Receipt</th><th>Student</th><th>Amount</th><th>Date</th><th>Mode</th></tr></thead>
          <tbody>
          <?php foreach ($recentPayments as $p): ?>
            <tr>
              <td><?= e($p['receipt_no']) ?></td>
              <td><?= e($p['student_name']) ?> <span class="text-muted">(<?= e($p['admission_no']) ?>)</span></td>
              <td class="num"><?= e(money($p['amount'])) ?></td>
              <td><?= e(fmt_date($p['paid_on'])) ?></td>
              <td><span class="badge badge-info"><?= e(ucfirst($p['mode'])) ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card" style="flex:1; min-width:280px;">
    <div class="card-head">
      <h2>Upcoming Exams</h2>
      <a class="btn btn-outline btn-sm" href="<?= e(App::url('exams/create')) ?>">New</a>
    </div>
    <div class="card-body">
      <?php if (!$upcomingExams): ?>
        <div class="text-muted">No exams planned yet.</div>
      <?php else: ?>
        <?php foreach ($upcomingExams as $ex): ?>
          <div style="border-bottom:1px solid var(--line); padding:10px 0;">
            <div style="font-weight:600;"><?= e($ex['name']) ?></div>
            <div class="text-muted" style="font-size:12.5px;">
              <?= e($ex['class_name']) ?> · starts <?= e(fmt_date($ex['start_date'])) ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
