<?php
declare(strict_types=1);

class FinanceController
{
    // ---------------- Overview ----------------
    public function overview(): void
    {
        require_role(['admin', 'accountant']);

        $month = date('Y-m');
        $stats = [
            'total_students'    => (int)Database::scalar("SELECT COUNT(*) FROM students WHERE status IN ('active','promoted')"),
            'fees_collected'    => (float)Database::scalar("SELECT COALESCE(SUM(amount),0) FROM fee_payments"),
            'month_fees'        => (float)Database::scalar("SELECT COALESCE(SUM(amount),0) FROM fee_payments WHERE DATE_FORMAT(paid_on,'%Y-%m')=?", [$month]),
            'petty_income'      => (float)Database::scalar("SELECT COALESCE(SUM(amount),0) FROM petty_ledger WHERE type='income'"),
            'petty_expense'     => (float)Database::scalar("SELECT COALESCE(SUM(amount),0) FROM petty_ledger WHERE type='expense'"),
            'payroll_paid'      => (float)Database::scalar("SELECT COALESCE(SUM(net_pay),0) FROM payroll_entries WHERE status='paid'"),
            'active_staff'      => (int)Database::scalar("SELECT COUNT(*) FROM staff WHERE is_active=1"),
        ];

        view('finance/overview', [
            'stats' => $stats,
            'title' => 'Finance', 'page' => 'finance',
        ]);
    }

    // ---------------- Payroll ----------------
    public function payrollIndex(): void
    {
        require_role(['admin', 'accountant']);
        $periods = Database::all("SELECT * FROM payroll_periods ORDER BY period_start DESC");
        view('finance/payroll', [
            'periods' => $periods,
            'title' => 'Payroll', 'page' => 'finance',
        ]);
    }

    public function payrollGenerateForm(): void
    {
        require_role(['admin', 'accountant']);
        $staff = Database::all(
            "SELECT s.id, s.employee_no, CONCAT(s.first_name,' ',s.last_name) AS full_name, s.role,
                    sb.basic_salary, sb.allowances, sb.monthly_deductions
             FROM staff s
             LEFT JOIN staff_salary_basis sb ON sb.staff_id=s.id
             WHERE s.is_active=1 ORDER BY s.first_name"
        );
        view('finance/payroll_generate', [
            'staff' => $staff,
            'title' => 'Generate Payroll', 'page' => 'finance',
        ]);
    }

    public function payrollGenerate(): void
    {
        require_role(['admin', 'accountant']);
        csrf_check();

        $name = trim($_POST['name'] ?? '');
        $start = $_POST['period_start'] ?? '';
        $end = $_POST['period_end'] ?? '';
        $staffIds = $_POST['staff_ids'] ?? [];

        if ($name === '' || $start === '' || $end === '' || !is_array($staffIds) || !$staffIds) {
            flash_set('danger', 'Provide a period name, dates, and select at least one staff member.');
            redirect('finance/payroll/generate');
        }
        $staffIds = array_map('intval', $staffIds);

        $dupe = Database::one("SELECT id FROM payroll_periods WHERE name=?", [$name]);
        if ($dupe) { flash_set('danger', 'A payroll period with that name already exists.'); redirect('finance/payroll/generate'); }

        $periodId = Database::insert(
            "INSERT INTO payroll_periods (name, period_start, period_end) VALUES (?,?,?)",
            [$name, $start, $end]
        );

        $created = 0;
        foreach ($staffIds as $sid) {
            $basis = Database::one("SELECT * FROM staff_salary_basis WHERE staff_id=?", [$sid]);
            if (!$basis) continue;
            $basic = (float)$basis['basic_salary'];
            $allow = (float)$basis['allowances'];
            $ded = (float)$basis['monthly_deductions'];
            $net = $basic + $allow - $ded;
            Database::execute(
                "INSERT INTO payroll_entries (period_id, staff_id, basic_salary, allowances, deductions, net_pay)
                 VALUES (?,?,?,?,?,?)",
                [$periodId, $sid, $basic, $allow, $ded, $net]
            );
            $created++;
        }

        flash_set('success', "Payroll period created with $created entries.");
        redirect('finance/payroll');
    }

    public function payrollMarkPaid(string $periodId): void
    {
        require_role(['admin', 'accountant']);
        csrf_check();
        Database::execute(
            "UPDATE payroll_entries SET status='paid', paid_on=CURDATE() WHERE period_id=? AND status='draft'",
            [(int)$periodId]
        );
        Database::execute("UPDATE payroll_periods SET is_paid=1 WHERE id=?", [(int)$periodId]);
        flash_set('success', 'Payroll marked as paid.');
        redirect('finance/payroll');
    }

    // ---------------- Fee structures & collection ----------------
    public function feeStructures(): void
    {
        require_login();
        $structures = Database::all(
            "SELECT fs.*, c.name AS class_name
             FROM fee_structures fs JOIN classes c ON c.id=fs.class_id
             WHERE fs.is_active=1 ORDER BY c.name, fs.fee_type"
        );
        $classes = Database::all("SELECT * FROM classes ORDER BY numeric_rank, name");
        $students = Database::all(
            "SELECT s.id, s.admission_no, CONCAT(s.first_name,' ',s.last_name) AS student_name,
                    c.name AS class_name, sec.name AS section_name
             FROM students s
             JOIN student_enrolments ce ON ce.student_id=s.id AND ce.session_id = ?
             LEFT JOIN classes c ON c.id=ce.class_id
             LEFT JOIN sections sec ON sec.id=ce.section_id
             WHERE s.status IN ('active','promoted')
             ORDER BY s.first_name",
            [$this->currentSession()]
        );
        view('finance/fees', [
            'structures' => $structures, 'classes' => $classes, 'students' => $students,
            'title' => 'Fee Management', 'page' => 'finance',
        ]);
    }

    public function feeStructureCreate(): void
    {
        require_role(['admin', 'accountant']);
        csrf_check();
        $classId = (int)($_POST['class_id'] ?? 0);
        $type = trim($_POST['fee_type'] ?? '');
        $amount = (float)($_POST['amount'] ?? 0);
        $freq = $_POST['frequency'] ?? 'monthly';

        if ($classId === 0 || $type === '' || $amount <= 0) {
            flash_set('danger', 'Class, fee type and amount are required.');
            redirect('finance/fees');
        }
        if (!in_array($freq, ['monthly','term','yearly','one-time'], true)) $freq = 'monthly';

        $dup = Database::one("SELECT id FROM fee_structures WHERE class_id=? AND fee_type=? AND is_active=1", [$classId, $type]);
        if ($dup) { flash_set('danger', 'That fee type already exists for this class.'); redirect('finance/fees'); }

        Database::execute(
            "INSERT INTO fee_structures (class_id, fee_type, amount, frequency) VALUES (?,?,?,?)",
            [$classId, $type, $amount, $freq]
        );
        flash_set('success', "Fee structure \"$type\" added.");
        redirect('finance/fees');
    }

    public function feeStructureDelete(string $id): void
    {
        require_role(['admin', 'accountant']);
        csrf_check();
        Database::execute("UPDATE fee_structures SET is_active=0 WHERE id=?", [(int)$id]);
        flash_set('success', 'Fee structure removed.');
        redirect('finance/fees');
    }

    public function feeCollect(): void
    {
        require_role(['admin', 'accountant']);
        csrf_check();
        $studentId = (int)($_POST['student_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $paidOn = $_POST['paid_on'] ?? date('Y-m-d');
        $mode = $_POST['mode'] ?? 'cash';
        $refNo = trim($_POST['ref_no'] ?? '');
        $notes = trim($_POST['notes'] ?? '');

        if ($studentId === 0 || $amount <= 0) {
            flash_set('danger', 'Select a student and enter a valid amount.');
            redirect('finance/fees');
        }
        if (!in_array($mode, ['cash','bank','card','online','other'], true)) $mode = 'cash';

        $receipt = 'RCP-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        // ensure unique
        while (Database::one("SELECT id FROM fee_payments WHERE receipt_no=?", [$receipt])) {
            $receipt = 'RCP-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        }

        Database::insert(
            "INSERT INTO fee_payments (student_id, session_id, receipt_no, amount, paid_on, mode, ref_no, notes, recorded_by)
             VALUES (?,?,?,?,?,?,?,?,?)",
            [$studentId, $this->currentSession(), $receipt, $amount, $paidOn, $mode, $refNo ?: null, $notes ?: null, $this->currentStaffId()]
        );

        flash_set('success', "Payment collected. Receipt: $receipt");
        redirect('finance/fees');
    }

    public function feePayments(): void
    {
        require_login();
        $q = trim($_GET['q'] ?? '');
        $where = '1=1';
        $params = [];
        if ($q !== '') {
            $where = "(fp.receipt_no LIKE ? OR CONCAT(s.first_name,' ',s.last_name) LIKE ? OR s.admission_no LIKE ?)";
            $params = ["%$q%", "%$q%", "%$q%"];
        }
        $records = Database::all(
            "SELECT fp.*, CONCAT(s.first_name,' ',s.last_name) AS student_name, s.admission_no,
                    sess.name AS session_name
             FROM fee_payments fp
             JOIN students s ON s.id=fp.student_id
             LEFT JOIN academic_sessions sess ON sess.id=fp.session_id
             WHERE $where ORDER BY fp.paid_on DESC, fp.id DESC LIMIT 200",
            $params
        );
        view('finance/fee_payments', [
            'records' => $records, 'q' => $q,
            'title' => 'Fee Payments', 'page' => 'finance',
        ]);
    }

    // ---------------- Petty income/expense ----------------
    public function pettyLedger(): void
    {
        require_role(['admin', 'accountant']);
        $type = $_GET['type'] ?? '';
        $where = $type === 'income' || $type === 'expense' ? "WHERE type=?" : '1=1';
        $params = $where === 'WHERE type=?' ? [$type] : [];

        $records = Database::all("SELECT * FROM petty_ledger $where ORDER BY entry_date DESC, id DESC LIMIT 300", $params);
        $totals = [
            'income' => (float)Database::scalar("SELECT COALESCE(SUM(amount),0) FROM petty_ledger WHERE type='income'"),
            'expense' => (float)Database::scalar("SELECT COALESCE(SUM(amount),0) FROM petty_ledger WHERE type='expense'"),
        ];
        view('finance/petty', [
            'records' => $records, 'totals' => $totals, 'type' => $type,
            'title' => 'Petty Income & Expense', 'page' => 'finance',
        ]);
    }

    public function pettyCreate(): void
    {
        require_role(['admin', 'accountant']);
        csrf_check();
        $type = $_POST['type'] ?? '';
        $category = trim($_POST['category'] ?? '');
        $amount = (float)($_POST['amount'] ?? 0);
        $date = $_POST['entry_date'] ?? date('Y-m-d');
        $description = trim($_POST['description'] ?? '');
        $refNo = trim($_POST['ref_no'] ?? '');

        if (!in_array($type, ['income','expense'], true) || $category === '' || $amount <= 0) {
            flash_set('danger', 'Type, category and a positive amount are required.');
            redirect('finance/petty');
        }
        Database::execute(
            "INSERT INTO petty_ledger (entry_date, type, category, description, amount, ref_no, recorded_by)
             VALUES (?,?,?,?,?,?,?)",
            [$date, $type, $category, $description ?: null, $amount, $refNo ?: null, $this->currentStaffId()]
        );
        flash_set('success', ucfirst($type) . ' recorded.');
        redirect('finance/petty');
    }

    // ---------------- helpers ----------------
    private function currentSession(): int
    {
        return (int)Database::scalar("SELECT id FROM academic_sessions WHERE is_current=1 LIMIT 1") ?: 0;
    }

    private function currentStaffId(): ?int
    {
        $u = Auth::user();
        return isset($u['staff_id']) && $u['staff_id'] ? (int)$u['staff_id'] : null;
    }
}
