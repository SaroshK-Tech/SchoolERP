<?php /** @var int $classId @var int $sectionId @var int $sessionId @var array $sessions @var array $classes @var array $sections */ ?>
<?php require_role(['admin','accountant']); ?>

<div class="card">
  <div class="card-head"><h2>Edit Timetable — Select Scope</h2><a class="btn btn-outline" href="<?= e(App::url('timetable')) ?>">View Timetable</a></div>
  <div class="card-body">
    <form method="get" action="<?= e(App::url('timetable/edit')) ?>">
      <div class="form-grid">
        <div class="form-row"><label>Session</label>
          <select name="session_id" required>
            <?php foreach ($sessions as $s): ?><option value="<?= e($s['id']) ?>" <?= $sessionId == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option><?php endforeach; ?>
          </select></div>
        <div class="form-row"><label>Class *</label>
          <select name="class_id" required id="editClassSel">
            <option value="">Select class</option>
            <?php foreach ($classes as $c): ?><option value="<?= e($c['id']) ?>" <?= $classId == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
          </select></div>
        <div class="form-row"><label>Section</label>
          <select name="section_id" id="editSectionSel">
            <option value="">— Whole class —</option>
            <?php foreach ($sections as $sec): ?><option value="<?= e($sec['id']) ?>" <?= $sectionId == $sec['id'] ? 'selected' : '' ?>><?= e($sec['name']) ?></option><?php endforeach; ?>
          </select></div>
      </div>
      <button class="btn btn-primary mt-2" type="submit">Continue</button>
    </form>
  </div>
</div>
