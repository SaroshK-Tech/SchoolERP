<?php /** @var array $filters @var int $sessionId @var int $classId @var int $sectionId @var int $teacherId @var array $timetable @var string $viewType @var string $label @var array $slots @var array $days */ ?>
<style>
  .tt { width:100%; border-collapse:collapse; }
  .tt th, .tt td { border:1px solid var(--line); padding:10px; text-align:center; font-size:13px; }
  .tt thead th { background:var(--brand-light); color:var(--brand-dark); text-transform:uppercase; font-size:11.5px; }
  .tt .slot-time { background:#f8fafc; color:var(--muted); font-size:11.5px; white-space:nowrap; }
  .tt .cell { background:#fff; }
  @media print {
    body { background:#fff; }
    .sidebar, .topbar, .btn, .no-print, .filters { display:none !important; }
    .content { padding:0; }
    .tt th, .tt td { border-color:#555; }
    .card { box-shadow:none; border:none; }
  }
</style>

<div class="card">
  <div class="card-head">
    <h2>Timetable</h2>
    <div class="row no-print">
      <a class="btn btn-outline" href="<?= e(App::url('timetable/edit')) ?>">Edit Timetable</a>
      <a class="btn btn-outline" href="<?= e(App::url('timetable/slots')) ?>">Time Slots</a>
      <button class="btn btn-primary" onclick="window.print()">🖨 Print</button>
    </div>
  </div>
  <div class="card-body no-print">
    <form method="get" action="<?= e(App::url('timetable')) ?>">
      <div class="filters">
        <div class="form-row"><label>Session</label>
          <select name="session_id" data-auto-submit>
            <?php foreach ($filters['sessions'] as $s): ?>
              <option value="<?= e($s['id']) ?>" <?= $sessionId == $s['id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="form-row"><label>Class</label>
          <select name="class_id" data-auto-submit>
            <option value="">— All classes —</option>
            <?php foreach ($filters['classes'] as $c): ?>
              <option value="<?= e($c['id']) ?>" <?= $classId == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="form-row"><label>Section</label>
          <select name="section_id" data-auto-submit>
            <option value="">— All sections —</option>
            <?php foreach ($filters['sections'] as $sec): ?>
              <option value="<?= e($sec['id']) ?>" <?= $sectionId == $sec['id'] ? 'selected' : '' ?>><?= e($sec['name']) ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="form-row"><label>Teacher</label>
          <select name="teacher_id" data-auto-submit>
            <option value="">— All teachers —</option>
            <?php foreach ($filters['teachers'] as $t): ?>
              <option value="<?= e($t['id']) ?>" <?= $teacherId == $t['id'] ? 'selected' : '' ?>><?= e($t['first_name'] . ' ' . $t['last_name']) ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="form-row"><button class="btn btn-outline" type="submit">View</button></div>
      </div>
    </form>
  </div>

  <div class="card-body">
    <?php if ($viewType === 'none'): ?>
      <div class="empty no-print"><div class="big">⏰</div>Select a class, section or teacher to view the timetable.</div>
    <?php else: ?>
      <div style="margin-bottom:12px;"><strong><?= e(ucfirst($viewType)) ?>:</strong> <?= e($label) ?></div>
      <div class="table-wrap">
        <table class="tt">
          <thead>
            <tr>
              <th>Period / Day</th>
              <?php foreach ($days as $dn => $dname): ?>
                <th><?= e($dname) ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($slots as $slot): ?>
            <tr>
              <td class="slot-time"><?= e($slot['name']) ?><br><small><?= substr($slot['start_time'],0,5) ?>–<?= substr($slot['end_time'],0,5) ?></small></td>
              <?php foreach ($days as $dn => $dname): ?>
                <td class="cell">
                  <?php $cells = $timetable[$dn][$slot['sort_order']] ?? []; ?>
                  <?php foreach ($cells as $c): ?>
                    <div><strong><?= e($c['subject_code'] ?: $c['subject_name']) ?></strong></div>
                    <div class="text-muted" style="font-size:11px;"><?= e($c['teacher_name']) ?></div>
                    <?php if ($viewType !== 'class' && $c['class_name']): ?><small class="text-muted"><?= e($c['class_name']) ?><?= $c['section_name'] ? '-' . e($c['section_name']) : '' ?></small><?php endif; ?>
                  <?php endforeach; ?>
                  <?php if (!$cells): ?><span class="text-muted">—</span><?php endif; ?>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
