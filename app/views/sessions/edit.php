<?php /** @var array $session */ ?>
<?php require_role(['admin','accountant']); ?>

<div class="row">
  <div class="card" style="max-width:480px;">
    <div class="card-head"><h2>Edit Session — <?= e($session['name']) ?></h2></div>
    <div class="card-body">
      <form method="post" action="<?= e(App::url('sessions/edit/' . $session['id'])) ?>">
        <?= csrf_field() ?>
        <div class="form-row"><label>Session Name *</label>
          <input type="text" name="name" value="<?= e($session['name']) ?>" required></div>
        <div class="form-grid">
          <div class="form-row"><label>Start Date</label>
            <input type="date" name="start_date" value="<?= e($session['start_date']) ?>"></div>
          <div class="form-row"><label>End Date</label>
            <input type="date" name="end_date" value="<?= e($session['end_date']) ?>"></div>
        </div>
        <div class="form-row">
          <label style="font-weight:400; display:flex; align-items:center; gap:6px;">
            <input type="checkbox" name="is_current" value="1" <?= (int)$session['is_current']===1 ? 'checked' : '' ?>>
            Make this the active session
          </label>
        </div>
        <div class="form-row" style="margin-top:14px;">
          <button class="btn btn-primary" type="submit">Save Changes</button>
          <a class="btn btn-outline" href="<?= e(App::url('sessions')) ?>">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
