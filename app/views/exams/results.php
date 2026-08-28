<?php /** @var array $exam @var array $schedules @var array $students @var array $existing */ ?>
<div class="row between" style="margin-bottom:20px;">
  <div>
    <div style="font-size:20px; font-weight:700;">Enter Results — <?= e($exam['name']) ?></div>
    <div class="text-muted"><?= e($exam['class_name']) ?></div>
  </div>
  <div class="row">
    <a class="btn btn-outline" href="<?= e(App::url('exams/' . $exam['id'] . '/report-card')) ?>">View Report Card</a>
    <a class="btn btn-outline" href="<?= e(App::url('exams')) ?>">Back</a>
  </div>
</div>

<?php if (!$schedules): ?>
  <div class="alert alert-warning">No subjects scheduled. <a href="<?= e(App::url('exams/' . $exam['id'] . '/schedule')) ?>">Add subjects first</a>.</div>
<?php else: ?>
<form method="post" action="<?= e(App::url('exams/' . $exam['id'] . '/results')) ?>">
  <?= csrf_field() ?>
  <?php foreach ($schedules as $si => $sc): ?>
  <div class="card">
    <div class="card-head"><h2><?= e($sc['subject_name']) ?> <span class="text-muted" style="font-weight:400;">(Full: <?= (int)$sc['full_marks'] ?> · Pass: <?= (int)$sc['pass_marks'] ?>)</span></h2></div>
    <div class="card-body flush">
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Admission</th><th>Student</th><th>Marks</th><th>Grade</th><th>Remarks</th></tr></thead>
          <tbody>
          <?php foreach ($students as $st): ?>
            <?php $key = $sc['id'] . ':' . $st['id']; $row = $existing[$key] ?? null; ?>
            <tr>
              <td><?= e($st['admission_no']) ?></td>
              <td><?= e($st['student_name']) ?></td>
              <td style="min-width:110px;">
                <input type="number" step="0.01" min="0" max="<?= (int)$sc['full_marks'] ?>"
                       name="marks[<?= (int)$sc['id'] ?>][<?= (int)$st['id'] ?>]"
                       value="<?= e($row['marks_obtained'] ?? '') ?>"
                       placeholder="—" style="width:100px;">
              </td>
              <td><?= e($row['grade'] ?? '') ?></td>
              <td><input type="text" name="remarks[<?= (int)$sc['id'] ?>][<?= (int)$st['id'] ?>]" value="<?= e($row['remarks'] ?? '') ?>" style="min-width:180px;"></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$students): ?>
            <tr><td colspan="5"><div class="empty"><div class="big">🎓</div>No students enrolled in this class.</div></td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  <div class="card"><div class="card-body"><button class="btn btn-primary" type="submit">Save All Results</button></div></div>
</form>
<?php endif; ?>
