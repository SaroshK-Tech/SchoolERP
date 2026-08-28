<?php /** @var array $students @var array $staff @var bool $dryRun */ ?>

<div class="card">
  <div class="card-head">
    <h2>Send Notification</h2>
    <span class="badge <?= $dryRun ? 'badge-warning' : 'badge-success' ?>"><?= $dryRun ? 'DRY-RUN' : 'LIVE' ?></span>
  </div>
  <div class="card-body">
    <?php if ($dryRun): ?>
      <div class="alert alert-info">Dry-run mode is ON: nothing is actually sent. Messages are logged only. Add Twilio credentials in <code>config/config.php</code> (or <code>config/local.php</code>) and set <code>dry_run=false</code> to enable live delivery.</div>
    <?php endif; ?>

    <form method="post" action="<?= e(App::url('notifications/send')) ?>">
      <?= csrf_field() ?>
      <div class="form-grid">
        <div class="form-row"><label>Channel *</label>
          <select name="channel">
            <option value="whatsapp">WhatsApp</option>
            <option value="sms">SMS</option>
          </select></div>
        <div class="form-row"><label>Subject (optional)</label>
          <input type="text" name="subject" placeholder="e.g. Fee Reminder"></div>
      </div>
      <div class="form-row"><label>Message *</label>
        <textarea name="message" rows="4" required placeholder="Type your message..."></textarea></div>

      <div class="form-row"><label>Recipients *</label>
        <select name="recipient_type" id="recipientType" style="max-width:240px;">
          <option value="students">Students (guardian phone)</option>
          <option value="staff">Staff</option>
          <option value="custom">Custom phone number</option>
        </select></div>

      <div id="studentsBlock" class="card" style="border:1px solid var(--line); margin-top:8px;">
        <div class="card-head">
          <h2>Select Students</h2>
          <span class="text-muted" style="font-size:12.5px;"><input type="checkbox" data-check-all="#notifStudents input.stu-cb"> Select all</span>
        </div>
        <div class="card-body flush">
          <div class="table-wrap">
            <table class="table">
              <thead><tr><th></th><th>Admission</th><th>Name</th><th>Class</th><th>Phone (guardian/self)</th></tr></thead>
              <tbody id="notifStudents">
              <?php foreach ($students as $st): ?>
                <tr>
                  <td><input type="checkbox" class="stu-cb" name="selected[]" value="<?= e($st['id']) ?>"></td>
                  <td><?= e($st['admission_no']) ?></td>
                  <td><?= e($st['first_name'] . ' ' . $st['last_name']) ?></td>
                  <td><?= e($st['class_name'] ?: '—') ?></td>
                  <td><?= e($st['guardian_phone'] ?: $st['phone'] ?: '—') ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div id="staffBlock" class="card" style="border:1px solid var(--line); margin-top:8px; display:none;">
        <div class="card-head">
          <h2>Select Staff</h2>
          <span class="text-muted" style="font-size:12.5px;"><input type="checkbox" data-check-all="#notifStaff input.staff-cb"> Select all</span>
        </div>
        <div class="card-body flush">
          <div class="table-wrap">
            <table class="table">
              <thead><tr><th></th><th>Employee</th><th>Name</th><th>Phone</th></tr></thead>
              <tbody id="notifStaff">
              <?php foreach ($staff as $st): ?>
                <tr>
                  <td><input type="checkbox" class="staff-cb" name="selected[]" value="<?= e($st['id']) ?>"></td>
                  <td><?= e($st['employee_no']) ?></td>
                  <td><?= e($st['first_name'] . ' ' . $st['last_name']) ?></td>
                  <td><?= e($st['phone'] ?: '—') ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div id="customBlock" style="display:none;">
        <div class="form-row"><label>Custom Phone Number</label>
          <input type="text" name="custom_phone" placeholder="e.g. +92 3XX XXXXXXX"></div>
      </div>

      <button class="btn btn-primary" type="submit">Send Notification</button>
      <a class="btn btn-outline" href="<?= e(App::url('notifications')) ?>">Cancel</a>
    </form>
  </div>
</div>

<script>
(function () {
  var type = document.getElementById('recipientType');
  var blocks = {
    students: document.getElementById('studentsBlock'),
    staff: document.getElementById('staffBlock'),
    custom: document.getElementById('customBlock')
  };
  type.addEventListener('change', function () {
    Object.keys(blocks).forEach(function (k) { blocks[k].style.display = (k === type.value) ? '' : 'none'; });
  });
})();
</script>
