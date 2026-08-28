<?php /** @var array $staff */ ?>
<?php require_role(['admin','accountant']); ?>
<?php $editing = !empty($staff); ?>

<form method="post" action="<?= e(App::url($editing ? 'staff/edit/' . $staff['id'] : 'staff/create')) ?>">
  <?= csrf_field() ?>
  <?php if ($editing): ?><input type="hidden" name="id" value="<?= e($staff['id']) ?>"><?php endif; ?>

  <div class="card">
    <div class="card-head"><h2>Personal Information</h2></div>
    <div class="card-body">
      <div class="form-grid">
        <div class="form-row">
          <label>Employee No. *</label>
          <input type="text" name="employee_no" value="<?= e(old('employee_no', $staff['employee_no'] ?? '')) ?>" required>
        </div>
        <div class="form-row">
          <label>First Name *</label>
          <input type="text" name="first_name" value="<?= e(old('first_name', $staff['first_name'] ?? '')) ?>" required>
        </div>
        <div class="form-row">
          <label>Last Name</label>
          <input type="text" name="last_name" value="<?= e(old('last_name', $staff['last_name'] ?? '')) ?>">
        </div>
        <div class="form-row">
          <label>Gender</label>
          <select name="gender">
            <?php foreach (['male','female','other'] as $g): ?>
              <option value="<?= e($g) ?>" <?= ($staff['gender'] ?? '') === $g ? 'selected' : '' ?>><?= e(ucfirst($g)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row">
          <label>Date of Birth</label>
          <input type="date" name="dob" value="<?= e(old('dob', $staff['dob'] ?? '')) ?>">
        </div>
        <div class="form-row">
          <label>Nationality</label>
          <input type="text" name="nationality" value="<?= e(old('nationality', $staff['nationality'] ?? '')) ?>">
        </div>
        <div class="form-row">
          <label>Phone</label>
          <input type="text" name="phone" value="<?= e(old('phone', $staff['phone'] ?? '')) ?>">
        </div>
        <div class="form-row">
          <label>Email</label>
          <input type="email" name="email" value="<?= e(old('email', $staff['email'] ?? '')) ?>">
        </div>
        <div class="form-row" style="grid-column:1/-1;">
          <label>Address</label>
          <textarea name="address" rows="2"><?= e(old('address', $staff['address'] ?? '')) ?></textarea>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-head"><h2>Employment Details</h2></div>
    <div class="card-body">
      <div class="form-grid">
        <div class="form-row">
          <label>Designation *</label>
          <input type="text" name="designation" value="<?= e(old('designation', $staff['designation'] ?? '')) ?>" required>
        </div>
        <div class="form-row">
          <label>Department</label>
          <input type="text" name="department" value="<?= e(old('department', $staff['department'] ?? '')) ?>">
        </div>
        <div class="form-row">
          <label>Role</label>
          <select name="role">
            <?php foreach (['staff','teacher','accountant','admin'] as $r): ?>
              <option value="<?= e($r) ?>" <?= ($staff['role'] ?? '') === $r ? 'selected' : '' ?>><?= e(ucfirst($r)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row">
          <label>Join Date</label>
          <input type="date" name="join_date" value="<?= e(old('join_date', $staff['join_date'] ?? '')) ?>">
        </div>
        <div class="form-row">
          <label>Leave Date</label>
          <input type="date" name="leave_date" value="<?= e(old('leave_date', $staff['leave_date'] ?? '')) ?>">
        </div>
        <div class="form-row">
          <label>&nbsp;</label>
          <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
            <input type="checkbox" name="is_active" value="1" style="width:auto;" <?= !isset($staff['is_active']) || $staff['is_active'] ? 'checked' : '' ?>>
            Active
          </label>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-head"><h2>Salary Basis</h2></div>
    <div class="card-body">
      <div class="form-grid">
        <div class="form-row">
          <label>Basic Salary</label>
          <input type="number" step="0.01" name="basic_salary" value="<?= e(old('basic_salary', $staff['salary_basis']['basic_salary'] ?? '')) ?>">
        </div>
        <div class="form-row">
          <label>Monthly Allowances</label>
          <input type="number" step="0.01" name="allowances" value="<?= e(old('allowances', $staff['salary_basis']['allowances'] ?? '')) ?>">
        </div>
        <div class="form-row">
          <label>Monthly Deductions</label>
          <input type="number" step="0.01" name="monthly_deductions" value="<?= e(old('monthly_deductions', $staff['salary_basis']['monthly_deductions'] ?? '')) ?>">
        </div>
        <div class="form-row">
          <label>Bank Account</label>
          <input type="text" name="bank_account" value="<?= e(old('bank_account', $staff['salary_basis']['bank_account'] ?? '')) ?>">
        </div>
      </div>
    </div>
  </div>

  <?php if (!$editing): ?>
  <div class="card">
    <div class="card-head"><h2>Login Credentials (optional)</h2></div>
    <div class="card-body">
      <label style="display:flex; align-items:center; gap:8px; margin-bottom:14px; cursor:pointer;">
        <input type="checkbox" name="create_login" value="1" style="width:auto;">
        Create a login for this staff member
      </label>
      <div class="form-grid">
        <div class="form-row">
          <label>Username</label>
          <input type="text" name="username">
        </div>
        <div class="form-row">
          <label>Password</label>
          <input type="password" name="password">
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body">
      <div class="row">
        <button type="submit" class="btn btn-primary"><?= $editing ? 'Update Staff' : 'Add Staff' ?></button>
        <a class="btn btn-outline" href="<?= e(App::url('staff')) ?>">Cancel</a>
      </div>
    </div>
  </div>
</form>
