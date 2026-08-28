<?php
declare(strict_types=1);

class StaffController
{
    public function index(): void
    {
        require_role(['admin', 'accountant']);

        $q = trim($_GET['q'] ?? '');
        $role = trim($_GET['role'] ?? '');
        $active = $_GET['status'] ?? '';

        $where = [];
        $params = [];
        if ($q !== '') {
            $where[] = "(first_name LIKE ? OR last_name LIKE ? OR employee_no LIKE ? OR email LIKE ?)";
            array_push($params, "%$q%", "%$q%", "%$q%", "%$q%");
        }
        if ($role !== '') {
            $where[] = "role = ?";
            $params[] = $role;
        }
        if ($active === '1' || $active === '0') {
            $where[] = "is_active = ?";
            $params[] = (int)$active;
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Pagination
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $total = (int)Database::scalar("SELECT COUNT(*) FROM staff $whereSql", $params);
        $pages = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $staff = Database::all(
            "SELECT * FROM staff $whereSql ORDER BY first_name, last_name LIMIT $perPage OFFSET $offset",
            $params
        );

        view('staff/index', [
            'staff' => $staff,
            'q' => $q,
            'role' => $role,
            'active' => $active,
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'title' => 'Staff Management',
            'page' => 'staff',
        ]);
    }

    public function create(): void
    {
        require_role(['admin', 'accountant']);
        view('staff/form', [
            'staff' => [],
            'title' => 'Add Staff Member',
            'page' => 'staff',
        ]);
    }

    public function store(): void
    {
        require_role(['admin', 'accountant']);
        csrf_check();

        $data = $this->payload();
        if (!$this->validate($data, 0, $errors)) {
            flash_set('danger', implode(' ', $errors));
            set_old($_POST);
            redirect('staff/create');
        }

        $id = Database::insert(
            "INSERT INTO staff
             (employee_no, first_name, last_name, gender, dob, nationality, phone, email, address,
              designation, department, role, join_date, leave_date, is_active, profile_photo)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            [
                $data['employee_no'], $data['first_name'], $data['last_name'], $data['gender'],
                $data['dob'] ?: null, $data['nationality'] ?: null, $data['phone'] ?: null,
                $data['email'] ?: null, $data['address'] ?: null, $data['designation'],
                $data['department'] ?: null, $data['role'], $data['join_date'] ?: null,
                $data['leave_date'] ?: null, $data['is_active'], $data['profile_photo'] ?: null,
            ]
        );

        $this->saveSalaryBasis($id, $data);

        flash_set('success', "Staff member \"{$data['first_name']} {$data['last_name']}\" added.");
        redirect('staff');
    }

    public function edit(string $id): void
    {
        require_role(['admin', 'accountant']);
        $staff = Database::one("SELECT * FROM staff WHERE id=?", [(int)$id]);
        if (!$staff) { flash_set('danger', 'Staff member not found.'); redirect('staff'); }

        $basis = Database::one("SELECT * FROM staff_salary_basis WHERE staff_id=?", [(int)$id]);
        $staff['salary_basis'] = $basis;

        view('staff/form', [
            'staff' => $staff,
            'title' => 'Edit Staff Member',
            'page' => 'staff',
        ]);
    }

    public function update(string $id): void
    {
        require_role(['admin', 'accountant']);
        csrf_check();

        $data = $this->payload();
        if (!$this->validate($data, (int)$id, $errors)) {
            flash_set('danger', implode(' ', $errors));
            redirect('staff/edit/' . $id);
        }

        Database::execute(
            "UPDATE staff SET
              employee_no=?, first_name=?, last_name=?, gender=?, dob=?, nationality=?, phone=?, email=?,
              address=?, designation=?, department=?, role=?, join_date=?, leave_date=?, is_active=?, profile_photo=?
             WHERE id=?",
            [
                $data['employee_no'], $data['first_name'], $data['last_name'], $data['gender'],
                $data['dob'] ?: null, $data['nationality'] ?: null, $data['phone'] ?: null,
                $data['email'] ?: null, $data['address'] ?: null, $data['designation'],
                $data['department'] ?: null, $data['role'], $data['join_date'] ?: null,
                $data['leave_date'] ?: null, $data['is_active'], $data['profile_photo'] ?: null,
                (int)$id,
            ]
        );

        $this->saveSalaryBasis((int)$id, $data);
        $this->syncLogin((int)$id, $data);

        flash_set('success', 'Staff member updated.');
        redirect('staff');
    }

    public function show(string $id): void
    {
        require_role(['admin', 'accountant']);
        $staff = Database::one("SELECT * FROM staff WHERE id=?", [(int)$id]);
        if (!$staff) { flash_set('danger', 'Staff member not found.'); redirect('staff'); }
        $basis = Database::one("SELECT * FROM staff_salary_basis WHERE staff_id=?", [(int)$id]);
        $payroll = Database::all(
            "SELECT pe.*, pp.name AS period_name, pp.period_start
             FROM payroll_entries pe JOIN payroll_periods pp ON pp.id=pe.period_id
             WHERE pe.staff_id=? ORDER BY pp.period_start DESC LIMIT 12",
            [(int)$id]
        );

        view('staff/show', [
            'staff' => $staff, 'basis' => $basis, 'payroll' => $payroll,
            'title' => 'Staff Profile', 'page' => 'staff',
        ]);
    }

    public function destroy(string $id): void
    {
        require_role(['admin']);
        csrf_check();
        $existed = Database::one("SELECT id FROM staff WHERE id=?", [(int)$id]);
        if ($existed) {
            Database::execute("DELETE FROM staff_salary_basis WHERE staff_id=?", [(int)$id]);
            Database::execute("DELETE FROM staff WHERE id=?", [(int)$id]);
            flash_set('success', 'Staff member deleted.');
        } else {
            flash_set('danger', 'Staff member not found.');
        }
        redirect('staff');
    }

    // ----- helpers -----

    private function payload(): array
    {
        return [
            'employee_no' => trim($_POST['employee_no'] ?? ''),
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'gender' => $_POST['gender'] ?? 'other',
            'dob' => $_POST['dob'] ?? '',
            'nationality' => trim($_POST['nationality'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'designation' => trim($_POST['designation'] ?? ''),
            'department' => trim($_POST['department'] ?? ''),
            'role' => $_POST['role'] ?? 'staff',
            'join_date' => $_POST['join_date'] ?? '',
            'leave_date' => $_POST['leave_date'] ?? '',
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'profile_photo' => '',
            'basic_salary' => (float)($_POST['basic_salary'] ?? 0),
            'allowances' => (float)($_POST['allowances'] ?? 0),
            'monthly_deductions' => (float)($_POST['monthly_deductions'] ?? 0),
            'bank_account' => trim($_POST['bank_account'] ?? ''),
            'create_login' => isset($_POST['create_login']),
            'username' => trim($_POST['username'] ?? ''),
            'password' => (string)($_POST['password'] ?? ''),
        ];
    }

    private function validate(array $d, int $editId, ?array &$errors): bool
    {
        $errors = [];
        if ($d['first_name'] === '') $errors[] = 'First name is required.';
        if ($d['employee_no'] === '') $errors[] = 'Employee number is required.';
        if ($d['designation'] === '') $errors[] = 'Designation is required.';
        if (!in_array($d['role'], ['admin', 'teacher', 'accountant', 'staff'], true)) $errors[] = 'Invalid role.';

        // uniqueness of employee_no
        $dup = Database::one("SELECT id FROM staff WHERE employee_no=? LIMIT 1", [$d['employee_no']]);
        if ($dup && (int)$dup['id'] !== $editId) {
            $errors[] = 'Employee number already in use.';
        }
        if ($d['email'] !== '') {
            $dupE = Database::one("SELECT id FROM staff WHERE email=? LIMIT 1", [$d['email']]);
            if ($dupE && (int)$dupE['id'] !== $editId) {
                $errors[] = 'Email already in use.';
            }
        }
        if (isset($_POST['create_login']) && ($d['username'] === '' || $d['password'] === '')) {
            $errors[] = 'Username and password are required when creating a login.';
        }
        return count($errors) === 0;
    }

    private function saveSalaryBasis(int $staffId, array $d): void
    {
        $exists = Database::one("SELECT staff_id FROM staff_salary_basis WHERE staff_id=?", [$staffId]);
        if (!$exists) {
            Database::execute(
                "INSERT INTO staff_salary_basis (staff_id, basic_salary, allowances, monthly_deductions, bank_account)
                 VALUES (?,?,?,?,?)",
                [$staffId, $d['basic_salary'], $d['allowances'], $d['monthly_deductions'], $d['bank_account'] ?: null]
            );
        } else {
            Database::execute(
                "UPDATE staff_salary_basis SET basic_salary=?, allowances=?, monthly_deductions=?, bank_account=? WHERE staff_id=?",
                [$d['basic_salary'], $d['allowances'], $d['monthly_deductions'], $d['bank_account'] ?: null, $staffId]
            );
        }
    }

    private function syncLogin(int $staffId, array $d): void
    {
        if (!isset($_POST['create_login']) || $d['username'] === '' || $d['password'] === '') {
            return;
        }
        $existing = Database::one("SELECT id FROM users WHERE staff_id=?", [$staffId]);
        if ($existing) {
            Database::execute(
                "UPDATE users SET username=?, role=?, is_active=?, password_hash=? WHERE staff_id=?",
                [$d['username'], $d['role'], $d['is_active'],
                 password_hash($d['password'], PASSWORD_BCRYPT, ['cost' => (int)App::config('security.password_cost', 12)]),
                 $staffId]
            );
        } else {
            Database::execute(
                "INSERT INTO users (username, password_hash, staff_id, role, is_active)
                 VALUES (?,?,?,?,?)",
                [$d['username'],
                 password_hash($d['password'], PASSWORD_BCRYPT, ['cost' => (int)App::config('security.password_cost', 12)]),
                 $staffId, $d['role'], $d['is_active']]
            );
        }
    }
}
