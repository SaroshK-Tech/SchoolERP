<?php /** @var array $exams */ ?>

<div class="row between" style="margin-bottom:20px;">
  <div class="row">
    <a class="btn btn-primary" href="<?= e(App::url('exams/create')) ?>">+ Create Exam</a>
    <a class="btn btn-outline" href="<?= e(App::url('subjects/manage')) ?>">Manage Subjects</a>
  </div>
</div>

<div class="card">
  <div class="card-head"><h2>Exams</h2></div>
  <div class="card-body flush">
    <?php if (!$exams): ?>
      <div class="empty"><div class="big">📝</div>No exams created yet.</div>
    <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Exam</th><th>Class</th><th>Session</th><th>Start</th><th>End</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($exams as $ex): ?>
          <tr>
            <td><a href="<?= e(App::url('exams/' . $ex['id'])) ?>"><strong><?= e($ex['name']) ?></strong></a></td>
            <td><?= e($ex['class_name']) ?></td>
            <td><?= e($ex['session_name']) ?></td>
            <td><?= e(fmt_date($ex['start_date'])) ?></td>
            <td><?= e(fmt_date($ex['end_date'])) ?></td>
            <td class="text-right">
              <a class="btn btn-outline btn-sm" href="<?= e(App::url('exams/' . $ex['id'] . '/schedule')) ?>">Schedule</a>
              <a class="btn btn-outline btn-sm" href="<?= e(App::url('exams/' . $ex['id'] . '/results')) ?>">Results</a>
              <a class="btn btn-outline btn-sm" href="<?= e(App::url('exams/' . $ex['id'] . '/report-card')) ?>">Report</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
