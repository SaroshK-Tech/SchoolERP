<?php /** @var array $students @var string $q @var string $classId @var string $sectionId @var string $sessionId @var array $classes @var array $sections @var array $sessions @var int $total @var int $page @var int $pages */ ?>

<div class="card">
  <div class="card-head">
    <h2>Students (<?= e($total) ?>)</h2>
    <div class="row">
      <a class="btn btn-outline" href="<?= e(App::url('promotion')) ?>">⬆ Bulk Promotion</a>
      <a class="btn btn-primary" href="<?= e(App::url('students/create')) ?>">+ Register Student</a>
    </div>
  </div>
  <div class="card-body">
    <form method="get" action="<?= e(App::url('students')) ?>">
      <div class="filters">
        <div class="form-row"><label>Search</label>
          <input type="text" name="q" value="<?= e($q) ?>" placeholder="Name or admission no..."></div>
        <div class="form-row"><label>Session</label>
          <select name="session_id" data-auto-submit>
            <?php foreach ($sessions as $s): ?>
              <option value="<?= e($s['id']) ?>" <?= $sessionId == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row"><label>Class</label>
          <select name="class_id" data-auto-submit>
            <option value="">All classes</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?= e($c['id']) ?>" <?= $classId == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row"><label>Section</label>
          <select name="section_id" data-auto-submit>
            <option value="">All sections</option>
            <?php foreach ($sections as $sec): ?>
              <option value="<?= e($sec['id']) ?>" <?= $sectionId == $sec['id'] ? 'selected' : '' ?>><?= e($sec['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row"><button class="btn btn-outline" type="submit">Filter</button></div>
      </div>
    </form>
  </div>
  <div class="card-body flush">
    <?php if (!$students): ?>
      <div class="empty"><div class="big">🎓</div>No students found.</div>
    <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Admission No.</th><th>Name</th><th>Class / Section</th><th>Gender</th><th>Contact</th><th>Guardian</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($students as $st): ?>
          <tr>
            <td><?= e($st['admission_no']) ?></td>
            <td><a href="<?= e(App::url('students/view/' . $st['id'])) ?>"><strong><?= e($st['first_name'] . ' ' . $st['last_name']) ?></strong></a></td>
            <td>
              <?php if ($st['class_name']): ?>
                <?= e($st['class_name']) ?><?= $st['section_name'] ? ' - ' . e($st['section_name']) : '' ?>
              <?php else: ?><span class="text-muted">Not assigned</span><?php endif; ?>
            </td>
            <td><?= e(ucfirst($st['gender'])) ?></td>
            <td><?= e($st['phone'] ?: '—') ?></td>
            <td><?= e($st['guardian_name'] ?: '—') ?></td>
            <td>
              <?php $map = ['active'=>'success','inactive'=>'muted','promoted'=>'info','graduated'=>'brand','withdrawn'=>'danger']; ?>
              <span class="badge badge-<?= $map[$st['status']] ?? 'muted' ?>"><?= e(ucfirst($st['status'])) ?></span>
            </td>
            <td class="text-right">
              <a class="btn btn-outline btn-sm" href="<?= e(App::url('students/view/' . $st['id'])) ?>">View</a>
              <a class="btn btn-outline btn-sm" href="<?= e(App::url('students/edit/' . $st['id'])) ?>">Edit</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
  <?php if ($pages > 1): ?>
    <div class="pagination">
      <?php for ($i = 1; $i <= $pages; $i++): ?>
        <?php $qs = http_build_query(array_merge($_GET, ['page' => $i])); ?>
        <?php if ($i === $page): ?><span class="current"><?= $i ?></span>
        <?php else: ?><a href="?<?= e($qs) ?>"><?= $i ?></a><?php endif; ?>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</div>
