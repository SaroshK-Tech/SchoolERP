<?php /** @var array $staff @var string $q @var string $role @var string $active @var int $total @var int $page @var int $pages */ ?>
<?php require_role(['admin','accountant']); ?>

<div class="card">
  <div class="card-head">
    <h2>Staff Members (<?= e($total) ?>)</h2>
    <a class="btn btn-primary" href="<?= e(App::url('staff/create')) ?>">+ Add Staff</a>
  </div>
  <div class="card-body">
    <form method="get" action="<?= e(App::url('staff')) ?>">
      <div class="filters">
        <div class="form-row">
          <label>Search</label>
          <input type="text" name="q" value="<?= e($q) ?>" placeholder="Name, ID, email...">
        </div>
        <div class="form-row">
          <label>Role</label>
          <select name="role" data-auto-submit>
            <option value="">All roles</option>
            <?php foreach (['admin','teacher','accountant','staff'] as $r): ?>
              <option value="<?= e($r) ?>" <?= $role===$r?'selected':'' ?>><?= e(ucfirst($r)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row">
          <label>Status</label>
          <select name="status" data-auto-submit>
            <option value="">Any</option>
            <option value="1" <?= $active==='1'?'selected':'' ?>>Active</option>
            <option value="0" <?= $active==='0'?'selected':'' ?>>Inactive</option>
          </select>
        </div>
        <div class="form-row">
          <button type="submit" class="btn btn-outline">Filter</button>
        </div>
      </div>
    </form>
  </div>
  <div class="card-body flush">
    <?php if (!$staff): ?>
      <div class="empty"><div class="big">👥</div>No staff members found.</div>
    <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Employee No.</th><th>Name</th><th>Designation</th><th>Department</th>
            <th>Role</th><th>Contact</th><th>Status</th><th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($staff as $s): ?>
          <tr>
            <td><?= e($s['employee_no']) ?></td>
            <td><a href="<?= e(App::url('staff/view/' . $s['id'])) ?>"><strong><?= e($s['first_name'] . ' ' . $s['last_name']) ?></strong></a></td>
            <td><?= e($s['designation']) ?></td>
            <td><?= e($s['department'] ?: '—') ?></td>
            <td><span class="badge badge-brand"><?= e(ucfirst($s['role'])) ?></span></td>
            <td><?= e($s['phone'] ?: '—') ?><br><span class="text-muted" style="font-size:12px;"><?= e($s['email'] ?: '') ?></span></td>
            <td>
              <?php if ($s['is_active']): ?>
                <span class="badge badge-success">Active</span>
              <?php else: ?>
                <span class="badge badge-danger">Inactive</span>
              <?php endif; ?>
            </td>
            <td class="text-right">
              <a class="btn btn-outline btn-sm" href="<?= e(App::url('staff/view/' . $s['id'])) ?>">View</a>
              <a class="btn btn-outline btn-sm" href="<?= e(App::url('staff/edit/' . $s['id'])) ?>">Edit</a>
              <form method="post" action="<?= e(App::url('staff/delete/' . $s['id'])) ?>" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger btn-sm" data-confirm="Delete this staff member?" <?= Auth::user()['role'] !== 'admin' ? 'disabled title="Admins only"' : '' ?>>Delete</button>
              </form>
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
        <?php if ($i === $page): ?>
          <span class="current"><?= $i ?></span>
        <?php else: ?>
          <a href="?<?= e($qs) ?>"><?= $i ?></a>
        <?php endif; ?>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</div>
