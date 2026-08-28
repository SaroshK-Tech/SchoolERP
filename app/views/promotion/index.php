<?php /** @var int $sessionId @var array $students @var array $classes @var array $byId @var array $sessions */ ?>
<?php require_role(['admin','accountant']); ?>

<div class="card">
  <div class="card-head">
    <h2>Bulk Promotion</h2>
    <span class="badge badge-info"><?= count($students) ?> students in current session</span>
  </div>
  <div class="card-body">
    <form method="get" action="<?= e(App::url('promotion')) ?>">
      <div class="filters">
        <div class="form-row"><label>Source Session</label>
          <select name="session_id" data-auto-submit>
            <?php foreach ($sessions as $s): ?>
              <option value="<?= e($s['id']) ?>" <?= $sessionId == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </form>
    <p class="text-muted mb-2" style="margin-top:10px;">
      Select students to promote. Choose how they should move, then run promotion.
      Students with no next class (highest grade) are marked <strong>Graduated</strong>.
    </p>
  </div>

  <form method="post" action="<?= e(App::url('promotion/process')) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="source_session_id" value="<?= e($sessionId) ?>">
    <div class="card-body" style="padding-top:0;">
      <div class="filters">
        <div class="form-row"><label>Target Session</label>
          <select name="target_session_id" required>
            <option value="">Select target session</option>
            <?php foreach ($sessions as $s): ?>
              <option value="<?= e($s['id']) ?>"><?= e($s['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row"><label>Promotion Option</label>
          <select name="option">
            <option value="next_class">Promote to next class</option>
            <option value="same_class">Keep in same class</option>
            <option value="graduate">Mark as graduated</option>
          </select>
        </div>
        <div class="form-row">
          <button class="btn btn-primary" type="submit">Run Promotion</button>
        </div>
      </div>
    </div>

    <div class="card-body flush">
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th><input type="checkbox" data-check-all="#promoAll input.student-cb"></th>
              <th>Admission No.</th><th>Student</th><th>Current Class</th><th>Section</th><th>Promote To</th>
            </tr>
          </thead>
          <tbody id="promoAll">
          <?php foreach ($students as $st): ?>
            <?php
              $nextId = null;
              foreach ($classes as $c) {
                  if ((int)$c['numeric_rank'] === (int)$st['class_rank'] + 1) { $nextId = $c['id']; $nextName = $c['name']; break; }
              }
              $promoteTo = $nextId ? ($nextName) : 'Graduate';
            ?>
            <tr>
              <td><input type="checkbox" class="student-cb" name="student_ids[]" value="<?= e($st['id']) ?>"></td>
              <td><?= e($st['admission_no']) ?></td>
              <td><?= e($st['first_name'] . ' ' . $st['last_name']) ?></td>
              <td><?= e($st['class_name']) ?></td>
              <td><?= e($st['section_name'] ?: '—') ?></td>
              <td>
                <?php if ($nextId): ?>
                  <span class="badge badge-brand"><?= e($promoteTo) ?></span>
                <?php else: ?>
                  <span class="badge badge-success">Graduate</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$students): ?>
            <tr><td colspan="6"><div class="empty"><div class="big">⬆️</div>No students found in the selected session.</div></td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </form>
</div>
