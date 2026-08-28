<?php
declare(strict_types=1);

class NotificationController
{
    public function index(): void
    {
        require_login();
        // Quick stats
        $stats = [
            'total' => (int)Database::scalar("SELECT COUNT(*) FROM notification_logs"),
            'sent' => (int)Database::scalar("SELECT COUNT(*) FROM notification_logs WHERE status='sent'"),
            'failed' => (int)Database::scalar("SELECT COUNT(*) FROM notification_logs WHERE status='failed'"),
            'queued' => (int)Database::scalar("SELECT COUNT(*) FROM notification_logs WHERE status='queued'"),
        ];
        $recent = Database::all("SELECT * FROM notification_logs ORDER BY id DESC LIMIT 10");
        view('notifications/index', [
            'stats' => $stats, 'recent' => $recent,
            'title' => 'Notifications', 'page' => 'notifications',
        ]);
    }

    public function compose(): void
    {
        require_login();
        // Recipient sources: guardians/students, staff
        $students = Database::all(
            "SELECT s.id, s.admission_no, s.first_name, s.last_name, s.guardian_phone,
                    s.phone, c.name AS class_name
             FROM students s
             LEFT JOIN student_enrolments ce ON ce.student_id=s.id AND ce.session_id = ?
             LEFT JOIN classes c ON c.id=ce.class_id
             WHERE s.status IN ('active','promoted')
             ORDER BY s.first_name",
            [$this->currentSession()]
        );
        $staff = Database::all(
            "SELECT id, employee_no, first_name, last_name, phone FROM staff WHERE is_active=1 ORDER BY first_name"
        );
        view('notifications/compose', [
            'students' => $students, 'staff' => $staff,
            'dryRun' => (bool)App::config('notifications.dry_run', true),
            'title' => 'Send Notification', 'page' => 'notifications',
        ]);
    }

    public function send(): void
    {
        require_login();
        csrf_check();

        $channel = $_POST['channel'] ?? 'whatsapp';
        $message = trim($_POST['message'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $recipientType = $_POST['recipient_type'] ?? 'students';
        $selected = $_POST['selected'] ?? [];   // array of ids
        $customPhone = trim($_POST['custom_phone'] ?? '');

        if ($message === '' || !in_array($channel, ['whatsapp', 'sms'], true)) {
            flash_set('danger', 'Choose a channel and write a message.');
            redirect('notifications/send');
        }
        if (!is_array($selected)) $selected = [];

        $sent = 0;
        $skipped = 0;

        if ($recipientType === 'custom') {
            if ($customPhone !== '') {
                NotificationService::send($channel, self::normalizePhone($customPhone), $message, $subject ?: null);
                $sent++;
            } else {
                $skipped++;
            }
        } elseif ($recipientType === 'students') {
            foreach ($selected as $sid) {
                $stu = Database::one(
                    "SELECT id, admission_no, CONCAT(first_name,' ',last_name) AS name, guardian_phone, phone
                     FROM students WHERE id=?",
                    [(int)$sid]
                );
                if (!$stu) continue;
                $phone = $stu['guardian_phone'] ?: $stu['phone'];
                if (empty($phone)) { $skipped++; continue; }
                NotificationService::send(
                    $channel, self::normalizePhone($phone), $message, $subject ?: null,
                    'student', (int)$stu['id'], $stu['name']
                );
                $sent++;
            }
        } else { // staff
            foreach ($selected as $sid) {
                $st = Database::one(
                    "SELECT id, employee_no, CONCAT(first_name,' ',last_name) AS name, phone FROM staff WHERE id=?",
                    [(int)$sid]
                );
                if (!$st || empty($st['phone'])) { $skipped++; continue; }
                NotificationService::send(
                    $channel, self::normalizePhone($st['phone']), $message, $subject ?: null,
                    'staff', (int)$st['id'], $st['name']
                );
                $sent++;
            }
        }

        flash_set('success', "$sent notification(s) sent via " . strtoupper($channel) . ($skipped ? " ($skipped skipped - no phone)." : '.'));
        redirect('notifications/logs');
    }

    public function logs(): void
    {
        require_login();
        $channel = $_GET['channel'] ?? '';
        $status = $_GET['status'] ?? '';
        $where = '1=1';
        $params = [];
        if (in_array($channel, ['whatsapp', 'sms'], true)) { $where .= " AND channel=?"; $params[] = $channel; }
        if (in_array($status, ['queued', 'sent', 'failed'], true)) { $where .= " AND status=?"; $params[] = $status; }

        $logs = Database::all("SELECT * FROM notification_logs WHERE $where ORDER BY id DESC LIMIT 300", $params);
        view('notifications/logs', [
            'logs' => $logs, 'channel' => $channel, 'status' => $status,
            'title' => 'Notification Logs', 'page' => 'notifications',
        ]);
    }

    private static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (strlen($digits) === 10) $digits = '92' . $digits;       // default country PK
        elseif (strlen($digits) === 11 && $digits[0] === '0') $digits = '92' . substr($digits, 1);
        elseif (strlen($digits) === 13 && substr($digits, 0, 2) === '92') { /* ok */ }
        return $digits;
    }

    private function currentSession(): int
    {
        return (int)Database::scalar("SELECT id FROM academic_sessions WHERE is_current=1 LIMIT 1") ?: 0;
    }
}
