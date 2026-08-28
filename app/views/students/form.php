<?php /** @var array $student @var array $enrol @var array $classes @var array $sessions @var int $currentSession */ ?>
<?php $editing = !empty($student); ?>

<form method="post" action="<?= e(App::url($editing ? 'students/edit/' . $student['id'] : 'students/create')) ?>">
  <?= csrf_field() ?>
  <?php if ($editing): ?><input type="hidden" name="id" value="<?= e($student['id']) ?>"><?php endif; ?>

  <div class="card">
    <div class="card-head"><h2>Student Information</h2></div>
    <div class="card-body">
      <div class="form-grid">
        <div class="form-row">
          <label>Admission No. *</label>
          <input type="text" name="admission_no" value="<?= e(old('admission_no', $student['admission_no'] ?? '')) ?>" required>
        </div>
        <div class="form-row">
          <label>First Name *</label>
          <input type="text" name="first_name" value="<?= e(old('first_name', $student['first_name'] ?? '')) ?>" required>
        </div>
        <div class="form-row">
          <label>Last Name</label>
          <input type="text" name="last_name" value="<?= e(old('last_name', $student['last_name'] ?? '')) ?>">
        </div>
        <div class="form-row">
          <label>Gender</label>
          <select name="gender">
            <?php foreach (['male','female','other'] as $g): ?>
              <option value="<?= e($g) ?>" <?= ($student['gender'] ?? '') === $g ? 'selected' : '' ?>><?= e(ucfirst($g)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row">
          <label>Date of Birth</label>
          <input type="date" name="dob" value="<?= e(old('dob', $student['dob'] ?? '')) ?>">
        </div>
        <div class="form-row">
          <label>Admission Date</label>
          <input type="date" name="admission_date" value="<?= e(old('admission_date', $student['admission_date'] ?? '')) ?>">
        </div>
        <div class="form-row">
          <label>Phone</label>
          <input type="text" name="phone" value="<?= e(old('phone', $student['phone'] ?? '')) ?>">
        </div>
        <div class="form-row">
          <label>Emergency Phone</label>
          <input type="text" name="emergency_phone" value="<?= e(old('emergency_phone', $student['emergency_phone'] ?? '')) ?>">
        </div>
        <div class="form-row">
          <label>Email</label>
          <input type="email" name="email" value="<?= e(old('email', $student['email'] ?? '')) ?>">
        </div>
        <div class="form-row">
          <label>Blood Group</label>
          <select name="blood_group">
            <option value="">— Select —</option>
            <?php foreach (['A+','A-','B+','B-','O+','O-','AB+','AB-'] as $b): ?>
              <option value="<?= e($b) ?>" <?= ($student['blood_group'] ?? '') === $b ? 'selected' : '' ?>><?= e($b) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row" style="grid-column:1/-1;">
          <label>Address</label>
          <textarea name="address" rows="2"><?= e(old('address', $student['address'] ?? '')) ?></textarea>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-head"><h2>Guardian Information</h2></div>
    <div class="card-body">
      <div class="form-grid">
        <div class="form-row">
          <label>Guardian Name</label>
          <input type="text" name="guardian_name" value="<?= e(old('guardian_name', $student['guardian_name'] ?? '')) ?>">
        </div>
        <div class="form-row">
          <label>Relation</label>
          <input type="text" name="guardian_relation" value="<?= e(old('guardian_relation', $student['guardian_relation'] ?? '')) ?>">
        </div>
        <div class="form-row">
          <label>Guardian Phone</label>
          <input type="text" name="guardian_phone" value="<?= e(old('guardian_phone', $student['guardian_phone'] ?? '')) ?>">
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-head"><h2>Enrolment</h2></div>
    <div class="card-body">
      <div class="form-grid">
        <div class="form-row">
          <label>Session</label>
          <select name="session_id">
            <?php foreach ($sessions as $s): ?>
              <?php $sel = ($enrol['session_id'] ?? $currentSession) == $s['id']; ?>
              <option value="<?= e($s['id']) ?>" <?= $sel ? 'selected' : '' ?>><?= e($s['name']) ?><?= $s['is_current'] ? ' (current)' : '' ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row">
          <label>Class *</label>
          <select name="class_id" required>
            <option value="">Select class</option>
            <?php foreach ($classes as $c): ?>
              <option value="<?= e($c['id']) ?>" <?= ($enrol['class_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row">
          <label>Section</label>
          <select name="section_id">
            <option value="">— None —</option>
            <?php foreach ($classes as $c): ?>
              <?php foreach ($c['sections'] as $sec): ?>
                <option value="<?= e($sec['id']) ?>" <?= ($enrol['section_id'] ?? '') == $sec['id'] ? 'selected' : '' ?>>
                  <?= e($c['name'] . ' - ' . $sec['name']) ?>
                </option>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="row">
        <button class="btn btn-primary" type="submit"><?= $editing ? 'Update Student' : 'Register Student' ?></button>
        <a class="btn btn-outline" href="<?= e(App::url('students')) ?>">Cancel</a>
      </div>
    </div>
  </div>
</form>
