<?php /** @var array $student @var array $payments @var float $totalPaid @var array $history */ ?>
<div class="row between">
  <div style="display:flex; align-items:center; gap:16px;">
    <div class="avatar" style="width:56px; height:56px; font-size:22px;"><?= e(strtoupper(substr($student['first_name'], 0, 1))) ?></div>
    <div>
      <div style="font-size:20px; font-weight:700;"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></div>
      <div class="text-muted"><?= e($student['admission_no']) ?>
        · <?= $student['class_name'] ? e($student['class_name']) . ($student['section_name'] ? ' - ' . e($student['section_name']) : '') : 'Not assigned' ?>
        · <?= e($student['session_name']) ?></div>
      <div>
        <?php $map = ['active'=>'success','inactive'=>'muted','promoted'=>'info','graduated'=>'brand','withdrawn'=>'danger']; ?>
        <span class="badge badge-<?= $map[$student['status']] ?? 'muted' ?>"><?= e(ucfirst($student['status'])) ?></span>
      </div>
    </div>
  </div>
  <div class="row">
    <a class="btn btn-outline" href="<?= e(App::url('students/edit/' . $student['id'])) ?>">Edit</a>
    <a class="btn btn-outline" href="<?= e(App::url('students')) ?>">Back</a>
  </div>
</div>

<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:24px; margin-top:16px;">
  <div class="card">
    <div class="card-head"><h2>Personal Details</h2></div>
    <div class="card-body">
      <table class="table">
        <tr><td class="text-muted">Date of Birth</td><td><?= e(fmt_date($student['dob'])) ?></td></tr>
        <tr><td class="text-muted">Gender</td><td><?= e(ucfirst($student['gender'])) ?></td></tr>
        <tr><td class="text-muted">Blood Group</td><td><?= e($student['blood_group'] ?: '—') ?></td></tr>
        <tr><td class="text-muted">Admission Date</td><td><?= e(fmt_date($student['admission_date'])) ?></td></tr>
        <tr><td class="text-muted">Phone</td><td><?= e($student['phone'] ?: '—') ?></td></tr>
        <tr><td class="text-muted">Emergency</td><td><?= e($student['emergency_phone'] ?: '—') ?></td></tr>
        <tr><td class="text-muted">Email</td><td><?= e($student['email'] ?: '—') ?></td></tr>
        <tr><td class="text-muted">Address</td><td><?= e($student['address'] ?: '—') ?></td></tr>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-head"><h2>Guardian</h2></div>
    <div class="card-body">
      <table class="table">
        <tr><td class="text-muted">Name</td><td><?= e($student['guardian_name'] ?: '—') ?></td></tr>
        <tr><td class="text-muted">Relation</td><td><?= e($student['guardian_relation'] ?: '—') ?></td></tr>
        <tr><td class="text-muted">Contact</td><td><?= e($student['guardian_phone'] ?: '—') ?></td></tr>
      </table>
    </div>
    <div class="card-head" style="border-top:1px solid var(--line);"><h2>Total Fees Paid</h2></div>
    <div class="card-body">
      <div style="font-size:26px; font-weight:700; color:var(--success);"><?= e(money($totalPaid)) ?></div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-head"><h2>Fee Payment History</h2><a class="btn btn-outline btn-sm" href="<?= e(App::url('finance/fees')) ?>">Collect Fee</a></div>
  <div class="card-body flush">
    <?php if (!$payments): ?>
      <div class="empty"><div class="big">💳</div>No payments recorded.</div>
    <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Receipt</th><th>Amount</th><th>Date</th><th>Mode</th><th>Ref</th></tr></thead>
        <tbody>
        <?php foreach ($payments as $p): ?>
          <tr>
            <td><?= e($p['receipt_no']) ?></td>
            <td class="num"><?= e(money($p['amount'])) ?></td>
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

<div class="card">
  <div class="card-head"><h2>Enrolment History</h2></div>
  <div class="card-body flush">
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Session</th><th>Class</th><th>Section</th><th>Promoted From</th><th>Enrolled</th></tr></thead>
        <tbody>
        <?php foreach ($history as $h): ?>
          <tr>
            <td><?= e($h['session_name']) ?></td>
            <td><?= e($h['class_name']) ?></td>
            <td><?= e($h['section_name'] ?: '—') ?></td>
            <td>
              <?php $from = $h['promoted_from_class_id'] ? Database::one("SELECT name FROM classes WHERE id=?", [(int)$h['promoted_from_class_id']]) : null; ?>
              <?= $from ? e($from['name']) : '—' ?>
            </td>
            <td><?= e(fmt_date_time($h['enrolled_at'])) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
