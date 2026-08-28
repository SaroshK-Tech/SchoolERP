<?php /** @var int $sessionId @var int $classId @var int $sectionId @var array $slots @var array $subjects @var array $teachers @var array $entries @var array $days */ ?>
<?php require_role(['admin','accountant']); ?>
<?php $className = Database::scalar("SELECT name FROM classes WHERE id=?", [$classId]) ?: 'Class'; ?>

<div class="row between" style="margin-bottom:20px;">
  <div>
    <div style="font-size:20px; font-weight:700;">Edit Timetable — <?= e($className) ?><?= $sectionId ? ' - ' . e(Database::scalar("SELECT name FROM sections WHERE id=?", [$sectionId])) : '' ?></div>
    <div class="text-muted">Assign a subject + teacher to each period. Leave a period blank to remove it.</div>
  </div>
  <div class="row no-print">
    <a class="btn btn-outline" href="<?= e(App::url('timetable/edit')) ?>">Change Scope</a>
    <a class="btn btn-outline" href="<?= e(App::url('timetable')) ?>">Cancel</a>
  </div>
</div>

<form method="post" action="<?= e(App::url('timetable/edit')) ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="session_id" value="<?= e($sessionId) ?>">
  <input type="hidden" name="class_id" value="<?= e($classId) ?>">
  <input type="hidden" name="section_id" value="<?= e($sectionId) ?>">
  <div class="card">
    <div class="card-body flush">
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Period</th><?php foreach ($days as $dn => $dname): ?><th><?= e($dname) ?></th><?php endforeach; ?></tr></thead>
          <tbody>
          <?php foreach ($slots as $slot): ?>
            <tr>
              <td class="text-muted"><strong><?= e($slot['name']) ?></strong><br><small><?= substr($slot['start_time'],0,5) ?>–<?= substr($slot['end_time'],0,5) ?></small></td>
              <?php foreach ($days as $dn => $dname): ?>
                <?php $key = $dn . ':' . $slot['id']; $en = $entries[$key] ?? null; ?>
                <td style="min-width:150px; vertical-align:top;">
                  <select name="grid[<?= $dn ?>][<?= $slot['id'] ?>][subject_id]" style="margin-bottom:4px;">
                    <option value="0">— Subject —</option>
                    <?php foreach ($subjects as $sub): ?>
                      <option value="<?= e($sub['id']) ?>" <?= ($en['subject_id'] ?? 0) == $sub['id'] ? 'selected' : '' ?>><?= e($sub['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <select name="grid[<?= $dn ?>][<?= $slot['id'] ?>][teacher_id]">
                    <option value="0">— Teacher —</option>
                    <?php foreach ($teachers as $t): ?>
                      <option value="<?= e($t['id']) ?>" <?= ($en['teacher_id'] ?? 0) == $t['id'] ? 'selected' : '' ?>><?= e($t['first_name'] . ' ' . $t['last_name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-body no-print">
      <button class="btn btn-primary" type="submit">Save Timetable</button>
    </div>
  </div>
</form>
