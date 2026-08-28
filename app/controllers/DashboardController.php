<?php
declare(strict_types=1);

class DashboardController
{
    public function index(): void
    {
        require_login();

        $stats = [
            'staff_total'   => (int)Database::scalar("SELECT COUNT(*) FROM staff WHERE is_active=1"),
            'staff_teachers' => (int)Database::scalar("SELECT COUNT(*) FROM staff WHERE role='teacher' AND is_active=1"),
            'students_total'=> (int)Database::scalar("SELECT COUNT(*) FROM students WHERE status IN ('active','promoted')"),
            'students_active'=> (int)Database::scalar("SELECT COUNT(*) FROM students WHERE status='active'"),
            'classes'       => (int)Database::scalar("SELECT COUNT(*) FROM classes"),
            'sections'      => (int)Database::scalar("SELECT COUNT(*) FROM sections"),
            'today_income'  => Database::scalar("SELECT COALESCE(SUM(amount),0) FROM fee_payments WHERE paid_on=CURDATE()"),
            'today_expense_a'=>Database::scalar("SELECT COALESCE(SUM(amount),0) FROM petty_ledger WHERE type='expense' AND entry_date=CURDATE()"),
        ];

        // Recent fee payments
        $recentPayments = Database::all(
            "SELECT fp.*, CONCAT(s.first_name,' ',s.last_name) AS student_name, s.admission_no
             FROM fee_payments fp
             JOIN students s ON s.id=fp.student_id
             ORDER BY fp.paid_on DESC, fp.id DESC LIMIT 8"
        );

        // Staff leaving lineup count
        $staffCount = $stats['staff_total'];

        // Exams upcoming
        $upcomingExams = Database::all(
            "SELECT e.*, c.name AS class_name
             FROM exams e JOIN classes c ON c.id=e.class_id
             WHERE e.end_date IS NULL OR e.end_date >= CURDATE()
             ORDER BY e.start_date ASC LIMIT 6"
        );

        view('dashboard/index', [
            'stats' => $stats,
            'recentPayments' => $recentPayments,
            'upcomingExams' => $upcomingExams,
            'title' => 'Dashboard',
            'page' => 'dashboard',
        ]);
    }
}
