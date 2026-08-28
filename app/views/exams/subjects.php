<?php /** @var array $subjects @var array $assignments @var array $teachers */ ?>

<div class="card">
  <div class="card-head"><h2>Subjects</h2><a class="btn btn-outline" href="<?= e(App::url('exams')) ?>">Back to Exams</a></div>
  <div class="card-body">
    <form method="post" action="<?= e(App::url('subjects/manage')) ?>">
      <?= csrf_field() ?>
      <div class="form-grid">
        <div class="form-row"><label>New Subject Name</label>
          <input type="text" name="subject_name" placeholder="e.g. Mathematics"></div>
        <div class="form-row"><label>Subject Code</label>
          <input type="text" name="subject_code" placeholder="e.g. MATH"></div>
      </div>
      <div class="form-row" style="margin-top:4px;"><label>Assign Teacher to Subject</label>
        <div class="form-grid">
          <select name="teacher_id">
            <option value="">— Teacher —</option>
            <?php foreach ($teachers as $t): ?><option value="<?= e($t['id']) ?>"><?= e($t['first_name'] . ' ' . $t['last_name']) ?></option><?php endforeach; ?>
          </select>
          <select name="assign_subject_id">
            <option value="">— Subject —</option>
            <?php foreach ($subjects as $sub): ?><option value="<?= e($sub['id']) ?>"><?= e($sub['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
      </div>
      <button class="btn btn-primary mt-2" type="submit">Save</button>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-head"><h2>Teacher–Subject Assignments</h2></div>
  <div class="card-body flush">
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Subject</th><th>Teacher</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($assignments as $a): ?>
          <tr>
            <td><strong><?= e($a['subject_name']) ?></strong></td>
            <td><?= e($a['teacher_name']) ?></td>
            <td class="text-right">
              <form method="post" action="<?= e(App::url('subjects/manage')) ?>" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="remove_assignment_id" value="<?= e($a['id']) ?>">
                <button class="btn btn-danger btn-sm" data-confirm="Remove this assignment?">Remove</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$assignments): ?>
          <tr><td colspan="3"><div class="empty"><div class="big">📚</div>No assignments yet.</div></td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
