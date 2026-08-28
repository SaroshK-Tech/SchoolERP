<?php /** @var array $exam @var array $schedules @var array $students */ ?>
<div class="row between" style="margin-bottom:20px;">
  <div>
    <div style="font-size:20px; font-weight:700;"><?= e($exam['name']) ?></div>
    <div class="text-muted"><?= e($exam['class_name']) ?> · <?= e($exam['session_name']) ?><?= $exam['start_date'] ? ' · ' . e(fmt_date($exam['start_date'])) . ' → ' . e(fmt_date($exam['end_date'])) : '' ?></div>
  </div>
  <div class="row">
    <a class="btn btn-outline" href="<?= e(App::url('exams/' . $exam['id'] . '/schedule')) ?>">Edit Schedule</a>
    <a class="btn btn-outline" href="<?= e(App::url('exams/' . $exam['id'] . '/results')) ?>">Enter Results</a>
    <a class="btn btn-outline" href="<?= e(App::url('exams/' . $exam['id'] . '/report-card')) ?>">Report Card</a>
    <a class="btn btn-outline" href="<?= e(App::url('exams')) ?>">Back</a>
  </div>
</div>

<div class="row" style="align-items:flex-start;">
  <div class="card" style="flex:1.4; min-width:320px;">
    <div class="card-head"><h2>Exam Schedule</h2></div>
    <div class="card-body flush">
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Subject</th><th>Date</th><th>Time</th><th>Full</th><th>Pass</th></tr></thead>
          <tbody>
          <?php foreach ($schedules as $sc): ?>
            <tr>
              <td><strong><?= e($sc['subject_name']) ?></strong></td>
              <td><?= e(fmt_date($sc['exam_date'])) ?></td>
              <td><?= $sc['start_time'] ? substr($sc['start_time'], 0, 5) . '–' . substr($sc['end_time'], 0, 5) : '—' ?></td>
              <td class="num"><?= (int)$sc['full_marks'] ?></td>
              <td class="num"><?= (int)$sc['pass_marks'] ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$schedules): ?>
            <tr><td colspan="5"><div class="empty"><div class="big">📅</div>No subjects scheduled yet.</div></td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="card" style="flex:1; min-width:280px;">
    <div class="card-head"><h2>Enrolled Students (<?= count($students) ?>)</h2></div>
    <div class="card-body flush">
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Admission</th><th>Name</th></tr></thead>
          <tbody>
          <?php foreach ($students as $st): ?>
            <tr><td><?= e($st['admission_no']) ?></td><td><?= e($st['student_name']) ?></td></tr>
          <?php endforeach; ?>
          <?php if (!$students): ?>
            <tr><td colspan="2"><div class="empty"><div class="big">🎓</div>No students enrolled.</div></td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
