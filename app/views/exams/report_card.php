<?php /** @var array $exam @var array $schedules @var array $report */ ?>
<style>
  .rc { border:1px solid var(--line); border-radius:12px; margin:16px 0; overflow:hidden; }
  .rc-head { background:var(--brand-light); padding:14px 20px; display:flex; justify-content:space-between; align-items:center; }
  .rc-head h2 { margin:0; font-size:16px; }
  .rc-body { padding:20px; }
  .rc-table { width:100%; border-collapse:collapse; }
  .rc-table th, .rc-table td { border:1px solid var(--line); padding:8px 12px; text-align:center; }
  .rc-table thead th { background:#f8fafc; font-size:12px; text-transform:uppercase; }
  @media print {
    body { background:#fff; }
    .sidebar, .topbar, .btn, .no-print { display:none !important; }
    .content { padding:0; }
    .rc { border-color:#333; }
    .rc-table td, .rc-table th { border-color:#999; }
  }
</style>

<div class="row between no-print" style="margin-bottom:20px;">
  <div>
    <div style="font-size:20px; font-weight:700;">Report Card — <?= e($exam['name']) ?></div>
    <div class="text-muted"><?= e($exam['class_name']) ?> · <?= e($exam['session_name']) ?></div>
  </div>
  <div class="row">
    <button class="btn btn-primary" onclick="window.print()">🖨 Print</button>
    <a class="btn btn-outline" href="<?= e(App::url('exams/' . $exam['id'] . '/results')) ?>">Edit Results</a>
    <a class="btn btn-outline" href="<?= e(App::url('exams')) ?>">Back</a>
  </div>
</div>

<?php if (!$report): ?>
  <div class="alert alert-warning no-print">No students or results yet.</div>
<?php endif; ?>

<?php foreach ($report as $r): $st = $r['student']; ?>
  <div class="rc">
    <div class="rc-head">
      <h2><?= e($st['student_name']) ?> <span class="text-muted" style="font-weight:400;">(<?= e($st['admission_no']) ?>)</span></h2>
      <div>
        <span class="badge <?= $r['passed'] ? 'badge-success' : 'badge-danger' ?>"><?= $r['passed'] ? 'PASS' : 'FAIL' ?></span>
        <strong style="margin-left:12px;"><?= e($r['percentage']) ?>%</strong>
      </div>
    </div>
    <div class="rc-body">
      <table class="rc-table">
        <thead><tr><th>Subject</th><th>Full Marks</th><th>Pass Marks</th><th>Obtained</th><th>Grade</th></tr></thead>
        <tbody>
        <?php foreach ($schedules as $sc): $m = $r['marks'][$sc['id']] ?? null; ?>
          <tr>
            <td style="text-align:left;"><?= e($sc['subject_name']) ?></td>
            <td><?= (int)$sc['full_marks'] ?></td>
            <td><?= (int)$sc['pass_marks'] ?></td>
            <td><strong><?= $m ? e($m['marks_obtained']) : '—' ?></strong></td>
            <td><?= $m ? e($m['grade']) : '—' ?></td>
          </tr>
        <?php endforeach; ?>
        <tr style="background:#f8fafc;">
          <td colspan="3" style="text-align:right;"><strong>Total / Percentage</strong></td>
          <td><strong><?= e($r['obtained']) ?> / <?= e($r['full']) ?></strong></td>
          <td><strong><?= e($r['percentage']) ?>%</strong></td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
<?php endforeach; ?>
