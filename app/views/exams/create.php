<?php /** @var array $classes @var array $sessions @var int $currentSession @var array $subjects */ ?>

<div class="card">
  <div class="card-head"><h2>Create Exam</h2><a class="btn btn-outline" href="<?= e(App::url('exams')) ?>">Back</a></div>
  <div class="card-body">
    <form method="post" action="<?= e(App::url('exams/create')) ?>">
      <?= csrf_field() ?>
      <div class="form-grid">
        <div class="form-row"><label>Exam Name *</label>
          <input type="text" name="name" placeholder="e.g. Midterm 2025" required></div>
        <div class="form-row"><label>Session *</label>
          <select name="session_id" required>
            <option value="">Select session</option>
            <?php foreach ($sessions as $s): ?>
              <option value="<?= e($s['id']) ?>" <?= $s['id'] == $currentSession ? 'selected' : '' ?>><?= e($s['name']) ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="form-row"><label>Class *</label>
          <select name="class_id" required>
            <option value="">Select class</option>
            <?php foreach ($classes as $c): ?><option value="<?= e($c['id']) ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
          </select></div>
        <div class="form-row"><label>Start Date</label>
          <input type="date" name="start_date"></div>
        <div class="form-row"><label>End Date</label>
          <input type="date" name="end_date"></div>
      </div>
      <div class="mt-2">
        <button class="btn btn-primary" type="submit">Create Exam</button>
        <a class="btn btn-outline" href="<?= e(App::url('exams')) ?>">Cancel</a>
      </div>
    </form>
  </div>
</div>
