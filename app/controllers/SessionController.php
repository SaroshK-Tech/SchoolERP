<?php
declare(strict_types=1);

class SessionController
{
    public function index(): void
    {
        require_role(['admin', 'accountant']);

        $rows = Database::all(
            "SELECT asess.*,
                    (SELECT COUNT(*) FROM student_enrolments ce WHERE ce.session_id = asess.id) AS student_count,
                    (SELECT COUNT(*) FROM exams e WHERE e.session_id = asess.id) AS exam_count,
                    (SELECT COUNT(*) FROM fee_payments fp WHERE fp.session_id = asess.id) AS payment_count
             FROM academic_sessions asess
             ORDER BY asess.is_current DESC, asess.start_date DESC, asess.id DESC"
        );

        view('sessions/index', [
            'sessions' => $rows,
            'title' => 'Academic Sessions',
            'page' => 'sessions',
        ]);
    }

    public function create(): void
    {
        require_role(['admin', 'accountant']);
        csrf_check();

        $name = trim($_POST['name'] ?? '');
        $start = trim($_POST['start_date'] ?? '') ?: null;
        $end = trim($_POST['end_date'] ?? '') ?: null;
        $makeCurrent = isset($_POST['is_current']) && $_POST['is_current'] === '1';

        if ($name === '') {
            flash_set('danger', 'Session name is required.');
            redirect('sessions');
        }

        $dup = Database::one("SELECT id FROM academic_sessions WHERE name=?", [$name]);
        if ($dup) {
            flash_set('danger', "A session named \"$name\" already exists.");
            redirect('sessions');
        }

        if ($start && $end && strtotime($end) < strtotime($start)) {
            flash_set('danger', 'End date cannot be before the start date.');
            redirect('sessions');
        }

        $only = Database::scalar("SELECT COUNT(*) FROM academic_sessions") === 0;
        $isCurrent = $makeCurrent || $only ? 1 : 0;

        Database::run(
            "INSERT INTO academic_sessions (name, start_date, end_date, is_current) VALUES (?,?,?,?)",
            [$name, $start, $end, $isCurrent]
        );

        if ($isCurrent) {
            $this->clearCurrent((int)Database::conn()->insert_id);
        }

        flash_set('success', "Session \"$name\" created.");
        redirect('sessions');
    }

    public function edit(string $id): void
    {
        require_role(['admin', 'accountant']);
        $session = Database::one("SELECT * FROM academic_sessions WHERE id=?", [(int)$id]);
        if (!$session) {
            flash_set('danger', 'Session not found.');
            redirect('sessions');
        }
        view('sessions/edit', [
            'session' => $session,
            'title' => 'Edit Session',
            'page' => 'sessions',
        ]);
    }

    public function update(string $id): void
    {
        require_role(['admin', 'accountant']);
        csrf_check();

        $id = (int)$id;
        $session = Database::one("SELECT * FROM academic_sessions WHERE id=?", [$id]);
        if (!$session) {
            flash_set('danger', 'Session not found.');
            redirect('sessions');
        }

        $name = trim($_POST['name'] ?? '');
        $start = trim($_POST['start_date'] ?? '') ?: null;
        $end = trim($_POST['end_date'] ?? '') ?: null;
        $makeCurrent = isset($_POST['is_current']) && $_POST['is_current'] === '1';

        if ($name === '') {
            flash_set('danger', 'Session name is required.');
            redirect('sessions');
        }

        $dup = Database::one("SELECT id FROM academic_sessions WHERE name=? AND id<>?", [$name, $id]);
        if ($dup) {
            flash_set('danger', "A session named \"$name\" already exists.");
            redirect('sessions');
        }

        if ($start && $end && strtotime($end) < strtotime($start)) {
            flash_set('danger', 'End date cannot be before the start date.');
            redirect('sessions');
        }

        Database::execute(
            "UPDATE academic_sessions SET name=?, start_date=?, end_date=? WHERE id=?",
            [$name, $start, $end, $id]
        );

        if ($makeCurrent) {
            $this->clearCurrent($id);
        } elseif ((int)$session['is_current'] === 1) {
            Database::execute("UPDATE academic_sessions SET is_current=1 WHERE id=?", [$id]);
        }

        flash_set('success', "Session \"$name\" updated.");
        redirect('sessions');
    }

    public function setCurrent(string $id): void
    {
        require_role(['admin', 'accountant']);
        csrf_check();

        $id = (int)$id;
        $session = Database::one("SELECT id FROM academic_sessions WHERE id=?", [$id]);
        if (!$session) {
            flash_set('danger', 'Session not found.');
            redirect('sessions');
        }

        $this->clearCurrent($id);
        flash_set('success', 'Active session set to "' . $session['name'] . '".');
        redirect('sessions');
    }

    public function destroy(string $id): void
    {
        require_role(['admin']);
        csrf_check();

        $id = (int)$id;
        $session = Database::one("SELECT * FROM academic_sessions WHERE id=?", [$id]);
        if (!$session) {
            flash_set('danger', 'Session not found.');
            redirect('sessions');
        }

        $total = (int)Database::scalar("SELECT COUNT(*) FROM academic_sessions");
        if ($total <= 1) {
            flash_set('danger', 'You cannot delete the last remaining session.');
            redirect('sessions');
        }

        if ((int)$session['is_current'] === 1) {
            flash_set('danger', 'You cannot delete the active session. Set another session as active first.');
            redirect('sessions');
        }

        // Related records cascade via FK (enrolments, exams, fee_payments, etc.)
        Database::execute("DELETE FROM academic_sessions WHERE id=?", [$id]);
        flash_set('success', "Session \"{$session['name']}\" deleted.");
        redirect('sessions');
    }

    /** Set only the given session id as current (unset all others first). */
    private function clearCurrent(int $exceptId): void
    {
        Database::execute("UPDATE academic_sessions SET is_current=0");
        Database::execute("UPDATE academic_sessions SET is_current=1 WHERE id=?", [$exceptId]);
    }
}
