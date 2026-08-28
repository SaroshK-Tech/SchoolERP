<?php /** @var array $exam @var array $schedules @var array $subjects */ ?>
<div class="row between" style="margin-bottom:20px;">
  <div>
    <div style="font-size:20px; font-weight:700;">Schedule — <?= e($exam['name']) ?></div>
    <div class="text-muted"><?= e($exam['class_id'] ? Database::scalar("SELECT name FROM classes WHERE id=?", [(int)$exam['class_id']]) : '') ?></div>
  </div>
  <div class="row"><a class="btn btn-outline" href="<?= e(App::url('exams/' . $exam['id'])) ?>">Back to Exam</a></div>
</div>

<div class="card">
  <div class="card-head"><h2>Add Subject to Schedule</h2></div>
  <div class="card-body">
    <form method="post" action="<?= e(App::url('exams/' . $exam['id'] . '/schedule')) ?>">
      <?= csrf_field() ?>
      <div class="form-grid">
        <div class="form-row"><label>Subject *</label>
          <select name="subject_id" required>
            <option value="">Select subject</option>
            <?php foreach ($subjects as $sub): ?><option value="<?= e($sub['id']) ?>"><?= e($sub['name']) ?></option><?php endforeach; ?>
          </select></div>
        <div class="form-row"><label>Date</label>
          <input type="date" name="exam_date"></div>
        <div class="form-row"><label>Start Time</label>
          <input type="time" name="start_time"></div>
        <div class="form-row"><label>End Time</label>
          <input type="time" name="end_time"></div>
        <div class="form-row"><label>Full Marks</label>
          <input type="number" name="full_marks" value="100"></div>
        <div class="form-row"><label>Pass Marks</label>
          <input type="number" name="pass_marks" value="40"></div>
      </div>
      <button class="btn btn-primary mt-2" type="submit">Add to Schedule</button>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-head"><h2>Current Schedule</h2></div>
  <div class="card-body flush">
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Subject</th><th>Date</th><th>Time</th><th>Full</th><th>Pass</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($schedules as $sc): ?>
          <tr>
            <td><strong><?= e($sc['subject_name']) ?></strong></td>
            <td><?= e(fmt_date($sc['exam_date'])) ?></td>
            <td><?= $sc['start_time'] ? substr($sc['start_time'],0,5) . '–' . substr($sc['end_time'],0,5) : '—' ?></td>
            <td class="num"><?= (int)$sc['full_marks'] ?></td>
            <td class="num"><?= (int)$sc['pass_marks'] ?></td>
            <td class="text-right">
              <form method="post" action="<?= e(App::url('exams/' . $exam['id'] . '/schedule')) ?>" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="remove_schedule_id" value="<?= e($sc['id']) ?>">
                <input type="hidden" name="subject_id" value="0">
                <button class="btn btn-danger btn-sm" data-confirm="Remove this schedule entry?">Remove</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$schedules): ?>
          <tr><td colspan="6"><div class="empty"><div class="big">📅</div>No schedule entries.</div></td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
