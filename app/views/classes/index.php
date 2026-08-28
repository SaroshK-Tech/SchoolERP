<?php /** @var array $classes @var array $sections @var array $studentCounts @var array $teachers */ ?>
<?php require_role(['admin','accountant']); ?>

<div class="row" style="align-items:flex-start;">
  <div class="card" style="flex:1.3; min-width:320px;">
    <div class="card-head"><h2>Sections & Classes</h2></div>
    <div class="card-body flush">
      <div class="table-wrap">
        <table class="table">
          <thead><tr><th>Class</th><th>Section</th><th>Room</th><th>Class Teacher</th><th>Students</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($sections as $sec): ?>
            <tr>
              <td><strong><?= e($sec['class_name']) ?></strong></td>
              <td><span class="badge badge-brand"><?= e($sec['name']) ?></span></td>
              <td><?= e($sec['room'] ?: '—') ?></td>
              <td><?= e($sec['teacher_name'] ?: '—') ?></td>
              <td><?= (int)($studentCounts[$sec['id']] ?? 0) ?></td>
              <td class="text-right">
                <form method="post" action="<?= e(App::url('classes/section/' . $sec['id'] . '/delete')) ?>" style="display:inline;">
                  <?= csrf_field() ?>
                  <button class="btn btn-danger btn-sm" data-confirm="Delete this section?" <?= Auth::user()['role']!=='admin'?'disabled title="Admins only"':'' ?>>Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$sections): ?>
            <tr><td colspan="6"><div class="empty"><div class="big">🏫</div>No classes/sections yet.</div></td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div style="flex:1; min-width:280px; display:flex; flex-direction:column; gap:16px;">
    <div class="card">
      <div class="card-head"><h2>Add Class</h2></div>
      <div class="card-body">
        <form method="post" action="<?= e(App::url('classes/create')) ?>">
          <?= csrf_field() ?>
          <div class="form-row"><label>Class Name *</label>
            <input type="text" name="name" placeholder="e.g. Grade 9" required></div>
          <div class="form-row"><label>Code *</label>
            <input type="text" name="code" placeholder="e.g. G9" required></div>
          <div class="form-row"><label>Rank (for promotion order)</label>
            <input type="number" name="numeric_rank" value="0"></div>
          <div class="form-row"><label>Sections (space or comma separated)</label>
            <input type="text" name="sections" placeholder="e.g. A B C"></div>
          <div class="form-row"><label>Description</label>
            <textarea name="description" rows="2"></textarea></div>
          <button class="btn btn-primary" type="submit">Add Class</button>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-head"><h2>Add Section</h2></div>
      <div class="card-body">
        <form method="post" action="<?= e(App::url('classes/section/create')) ?>">
          <?= csrf_field() ?>
          <div class="form-row"><label>Class *</label>
            <select name="class_id" required>
              <option value="">Select class</option>
              <?php foreach ($classes as $c): ?><option value="<?= e($c['id']) ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="form-row"><label>Section Name *</label>
            <input type="text" name="name" placeholder="e.g. A" required></div>
          <div class="form-grid">
            <div class="form-row"><label>Room</label><input type="text" name="room"></div>
            <div class="form-row"><label>Capacity</label><input type="number" name="capacity"></div>
          </div>
          <div class="form-row"><label>Class Teacher</label>
            <select name="teacher_id">
              <option value="">— None —</option>
              <?php foreach ($teachers as $t): ?><option value="<?= e($t['id']) ?>"><?= e($t['first_name'] . ' ' . $t['last_name']) ?></option><?php endforeach; ?>
            </select>
          </div>
          <button class="btn btn-primary" type="submit">Add Section</button>
        </form>
      </div>
    </div>
  </div>
</div>
