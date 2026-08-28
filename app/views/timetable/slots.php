<?php /** @var array $slots */ ?>

<div class="card">
  <div class="card-head"><h2>Time Slots</h2><a class="btn btn-outline" href="<?= e(App::url('timetable')) ?>">Back to Timetable</a></div>
  <div class="card-body flush">
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>Period</th><th>Start</th><th>End</th><th>Sort</th></tr></thead>
        <tbody>
        <?php foreach ($slots as $s): ?>
          <tr><td><strong><?= e($s['name']) ?></strong></td><td><?= e(substr($s['start_time'],0,5)) ?></td><td><?= e(substr($s['end_time'],0,5)) ?></td><td><?= (int)$s['sort_order'] ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$slots): ?><tr><td colspan="4"><div class="empty"><div class="big">⏰</div>No time slots. Add one below.</div></td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-head"><h2>Add Time Slot</h2></div>
  <div class="card-body">
    <form method="post" action="<?= e(App::url('timetable/slots/create')) ?>">
      <?= csrf_field() ?>
      <div class="form-grid">
        <div class="form-row"><label>Period Name *</label>
          <input type="text" name="name" placeholder="e.g. Period 7" required></div>
        <div class="form-row"><label>Start</label><input type="time" name="start_time" required></div>
        <div class="form-row"><label>End</label><input type="time" name="end_time" required></div>
        <div class="form-row"><label>Sort Order</label><input type="number" name="sort_order" value="0"></div>
      </div>
      <button class="btn btn-primary mt-2" type="submit">Add Slot</button>
    </form>
  </div>
</div>
