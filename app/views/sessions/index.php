<?php /** @var array $sessions */ ?>
<?php require_role(['admin','accountant']); ?>

<div class="row" style="align-items:flex-start;">
  <div class="card" style="flex:1.4; min-width:340px;">
    <div class="card-head"><h2>Academic Sessions</h2>
      <span class="badge badge-brand"><?= count($sessions) ?> total</span>
    </div>
    <div class="card-body flush">
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Name</th><th>Start</th><th>End</th><th>Status</th>
              <th>Students</th><th>Exams</th><th>Payments</th><th></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($sessions as $s): ?>
            <tr>
              <td><strong><?= e($s['name']) ?></strong></td>
              <td><?= e(fmt_date($s['start_date']) ?: '—') ?></td>
              <td><?= e(fmt_date($s['end_date']) ?: '—') ?></td>
              <td>
                <?php if ((int)$s['is_current'] === 1): ?>
                  <span class="badge badge-success">Active</span>
                <?php else: ?>
                  <span class="badge badge-muted">Inactive</span>
                <?php endif; ?>
              </td>
              <td><?= (int)$s['student_count'] ?></td>
              <td><?= (int)$s['exam_count'] ?></td>
              <td><?= (int)$s['payment_count'] ?></td>
              <td class="text-right" style="white-space:nowrap;">
                <?php if ((int)$s['is_current'] !== 1): ?>
                  <form method="post" action="<?= e(App::url('sessions/' . $s['id'] . '/set-current')) ?>" style="display:inline;">
                    <?= csrf_field() ?>
                    <button class="btn btn-outline btn-sm" title="Make this the active session">Set Active</button>
                  </form>
                <?php endif; ?>
                <a class="btn btn-outline btn-sm" href="<?= e(App::url('sessions/edit/' . $s['id'])) ?>">Edit</a>
                <form method="post" action="<?= e(App::url('sessions/delete/' . $s['id'])) ?>" style="display:inline;">
                  <?= csrf_field() ?>
                  <button class="btn btn-danger btn-sm" data-confirm="Delete this session and ALL its enrolments, exams, fees & timetable data?" <?= (Auth::user()['role']!=='admin' || (int)$s['is_current']===1) ? 'disabled title="Admins only; cannot delete the active session"' : '' ?>>Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$sessions): ?>
            <tr><td colspan="8"><div class="empty"><div class="big">📅</div>No academic sessions yet. Create your first one below.</div></td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div style="flex:1; min-width:280px;">
    <div class="card">
      <div class="card-head"><h2>New Session</h2></div>
      <div class="card-body">
        <form method="post" action="<?= e(App::url('sessions/create')) ?>">
          <?= csrf_field() ?>
          <div class="form-row"><label>Session Name *</label>
            <input type="text" name="name" placeholder="e.g. 2025-2026" required></div>
          <div class="form-grid">
            <div class="form-row"><label>Start Date</label>
              <input type="date" name="start_date"></div>
            <div class="form-row"><label>End Date</label>
              <input type="date" name="end_date"></div>
          </div>
          <div class="form-row">
            <label style="font-weight:400; display:flex; align-items:center; gap:6px;">
              <input type="checkbox" name="is_current" value="1" <?= !$sessions ? 'checked' : '' ?>>
              Make this the active session
            </label>
          </div>
          <button class="btn btn-primary" type="submit">Create Session</button>
        </form>
      </div>
    </div>
  </div>
</div>
