<?php
declare(strict_types=1);

class ExamController
{
    private function currentSession(): int
    {
        return (int)Database::scalar("SELECT id FROM academic_sessions WHERE is_current=1 LIMIT 1") ?: 0;
    }

    public function index(): void
    {
        require_role(['admin', 'accountant', 'teacher']);
        $exams = Database::all(
            "SELECT e.*, c.name AS class_name, sess.name AS session_name
             FROM exams e
             JOIN classes c ON c.id=e.class_id
             LEFT JOIN academic_sessions sess ON sess.id=e.session_id
             ORDER BY e.start_date DESC"
        );
        view('exams/index', [
            'exams' => $exams,
            'title' => 'Exams & Results', 'page' => 'exams',
        ]);
    }

    public function create(): void
    {
        require_role(['admin', 'accountant']);
        view('exams/create', [
            'classes' => Database::all("SELECT * FROM classes ORDER BY numeric_rank, name"),
            'sessions' => Database::all("SELECT * FROM academic_sessions ORDER BY is_current DESC, start_date DESC"),
            'currentSession' => $this->currentSession(),
            'subjects' => $this->subjects(),
            'title' => 'Create Exam', 'page' => 'exams',
        ]);
    }

    public function store(): void
    {
        require_role(['admin', 'accountant']);
        csrf_check();
        $name = trim($_POST['name'] ?? '');
        $sessionId = (int)($_POST['session_id'] ?? 0);
        $classId = (int)($_POST['class_id'] ?? 0);
        $start = $_POST['start_date'] ?? '';
        $end = $_POST['end_date'] ?? '';

        if ($name === '' || $sessionId === 0 || $classId === 0) {
            flash_set('danger', 'Exam name, session and class are required.');
            redirect('exams/create');
        }
        $examId = Database::insert(
            "INSERT INTO exams (name, session_id, class_id, start_date, end_date) VALUES (?,?,?,?,?)",
            [$name, $sessionId, $classId, $start ?: null, $end ?: null]
        );
        flash_set('success', "Exam \"$name\" created. Now add subjects to its schedule.");
        redirect('exams/' . $examId . '/schedule');
    }

    public function show(string $id): void
    {
        require_role(['admin', 'accountant', 'teacher']);
        $exam = Database::one(
            "SELECT e.*, c.name AS class_name, sess.name AS session_name
             FROM exams e JOIN classes c ON c.id=e.class_id
             LEFT JOIN academic_sessions sess ON sess.id=e.session_id WHERE e.id=?",
            [(int)$id]
        );
        if (!$exam) { flash_set('danger', 'Exam not found.'); redirect('exams'); }

        $schedules = Database::all(
            "SELECT es.*, sub.name AS subject_name, sub.code AS subject_code
             FROM exam_schedules es JOIN subjects sub ON sub.id=es.subject_id
             WHERE es.exam_id=? ORDER BY es.exam_date, es.start_time",
            [(int)$id]
        );
        $students = Database::all(
            "SELECT s.id, s.admission_no, CONCAT(s.first_name,' ',s.last_name) AS student_name, s.status
             FROM students s
             JOIN student_enrolments ce ON ce.student_id=s.id AND ce.session_id=? AND ce.class_id=?
             ORDER BY s.first_name",
            [(int)$exam['session_id'], (int)$exam['class_id']]
        );

        view('exams/show', [
            'exam' => $exam, 'schedules' => $schedules, 'students' => $students,
            'title' => $exam['name'], 'page' => 'exams',
        ]);
    }

    public function scheduleForm(string $id): void
    {
        require_role(['admin', 'accountant']);
        $exam = Database::one("SELECT * FROM exams WHERE id=?", [(int)$id]);
        if (!$exam) { flash_set('danger', 'Exam not found.'); redirect('exams'); }
        $schedules = Database::all(
            "SELECT es.*, sub.name AS subject_name FROM exam_schedules es JOIN subjects sub ON sub.id=es.subject_id
             WHERE es.exam_id=? ORDER BY es.exam_date, es.start_time",
            [(int)$id]
        );
        view('exams/schedule', [
            'exam' => $exam, 'schedules' => $schedules, 'subjects' => $this->subjects(),
            'title' => 'Schedule — ' . $exam['name'], 'page' => 'exams',
        ]);
    }

    public function scheduleStore(string $id): void
    {
        require_role(['admin', 'accountant']);
        csrf_check();

        // Add a new schedule row (subject, date, time, marks)
        $subjectId = (int)($_POST['subject_id'] ?? 0);
        $date = $_POST['exam_date'] ?? '';
        $st = $_POST['start_time'] ?? '';
        $et = $_POST['end_time'] ?? '';
        $full = (int)($_POST['full_marks'] ?? 100);
        $pass = (int)($_POST['pass_marks'] ?? 40);

        if ($subjectId === 0) { flash_set('danger', 'Select a subject.'); redirect('exams/' . $id . '/schedule'); }
        Database::execute(
            "INSERT INTO exam_schedules (exam_id, subject_id, exam_date, start_time, end_time, full_marks, pass_marks)
             VALUES (?,?,?,?,?,?,?)",
            [(int)$id, $subjectId, $date ?: null, $st ?: null, $et ?: null, $full, $pass]
        );

        // Handle removal of a schedule if provided
        if (($removeId = (int)($_POST['remove_schedule_id'] ?? 0)) > 0) {
            Database::execute("DELETE FROM exam_results WHERE schedule_id=?", [$removeId]);
            Database::execute("DELETE FROM exam_schedules WHERE id=?", [$removeId]);
        }

        flash_set('success', 'Schedule updated.');
        redirect('exams/' . $id . '/schedule');
    }

    public function resultsForm(string $id): void
    {
        require_role(['admin', 'accountant', 'teacher']);
        $exam = Database::one(
            "SELECT e.*, c.name AS class_name FROM exams e JOIN classes c ON c.id=e.class_id WHERE e.id=?",
            [(int)$id]
        );
        if (!$exam) { flash_set('danger', 'Exam not found.'); redirect('exams'); }

        $schedules = Database::all(
            "SELECT es.*, sub.name AS subject_name FROM exam_schedules es
             JOIN subjects sub ON sub.id=es.subject_id
             WHERE es.exam_id=? ORDER BY es.exam_date, es.start_time",
            [(int)$id]
        );
        $students = Database::all(
            "SELECT s.id, s.admission_no, CONCAT(s.first_name,' ',s.last_name) AS student_name
             FROM students s
             JOIN student_enrolments ce ON ce.student_id=s.id AND ce.session_id=? AND ce.class_id=?
             ORDER BY s.first_name",
            [(int)$exam['session_id'], (int)$exam['class_id']]
        );

        // Load existing results keyed by schedule_id + student_id
        $existing = [];
        foreach (Database::all("SELECT * FROM exam_results WHERE schedule_id IN (SELECT id FROM exam_schedules WHERE exam_id=?)", [(int)$id]) as $r) {
            $existing[$r['schedule_id'] . ':' . $r['student_id']] = $r;
        }

        view('exams/results', [
            'exam' => $exam, 'schedules' => $schedules, 'students' => $students, 'existing' => $existing,
            'title' => 'Results — ' . $exam['name'], 'page' => 'exams',
        ]);
    }

    public function resultsSave(string $id): void
    {
        require_role(['admin', 'accountant', 'teacher']);
        csrf_check();

        $marks = $_POST['marks'] ?? [];       // schedule_id -> student_id -> marks
        foreach ($marks as $scheduleId => $students) {
            $scheduleId = (int)$scheduleId;
            foreach ($students as $studentId => $value) {
                $studentId = (int)$studentId;
                $val = trim((string)$value);
                $remarks = trim($_POST['remarks'][$scheduleId][$studentId] ?? '');
                if ($val === '') {
                    // delete empty result row
                    Database::execute("DELETE FROM exam_results WHERE schedule_id=? AND student_id=?", [$scheduleId, $studentId]);
                    continue;
                }
                $marksN = (float)$val;
                $grade = $this->gradeFor($marksN, $scheduleId);
                $exists = Database::one("SELECT id FROM exam_results WHERE schedule_id=? AND student_id=?", [$scheduleId, $studentId]);
                if ($exists) {
                    Database::execute(
                        "UPDATE exam_results SET marks_obtained=?, grade=?, remarks=?, entered_by=? WHERE id=?",
                        [$marksN, $grade, $remarks ?: null, $this->currentStaffId(), (int)$exists['id']]
                    );
                } else {
                    Database::execute(
                        "INSERT INTO exam_results (schedule_id, student_id, marks_obtained, grade, remarks, entered_by)
                         VALUES (?,?,?,?,?,?)",
                        [$scheduleId, $studentId, $marksN, $grade, $remarks ?: null, $this->currentStaffId()]
                    );
                }
            }
        }
        flash_set('success', 'Results saved.');
        redirect('exams/' . $id . '/report-card');
    }

    public function reportCard(string $id): void
    {
        require_role(['admin', 'accountant', 'teacher']);
        $exam = Database::one(
            "SELECT e.*, c.name AS class_name, sess.name AS session_name
             FROM exams e JOIN classes c ON c.id=e.class_id
             LEFT JOIN academic_sessions sess ON sess.id=e.session_id WHERE e.id=?",
            [(int)$id]
        );
        if (!$exam) { flash_set('danger', 'Exam not found.'); redirect('exams'); }

        $schedules = Database::all(
            "SELECT es.id, sub.name AS subject_name, sub.code AS subject_code, es.full_marks, es.pass_marks
             FROM exam_schedules es JOIN subjects sub ON sub.id=es.subject_id
             WHERE es.exam_id=? ORDER BY es.exam_date, es.start_time",
            [(int)$id]
        );
        $students = Database::all(
            "SELECT s.id, s.admission_no, CONCAT(s.first_name,' ',s.last_name) AS student_name
             FROM students s
             JOIN student_enrolments ce ON ce.student_id=s.id AND ce.session_id=? AND ce.class_id=?
             ORDER BY s.first_name",
            [(int)$exam['session_id'], (int)$exam['class_id']]
        );

        // Build per-student summary
        $report = [];
        foreach ($students as $st) {
            $row = ['student' => $st, 'marks' => [], 'obtained' => 0, 'full' => 0, 'passed' => true];
            foreach ($schedules as $sch) {
                $r = Database::one(
                    "SELECT marks_obtained, grade FROM exam_results WHERE schedule_id=? AND student_id=?",
                    [(int)$sch['id'], (int)$st['id']]
                );
                $row['marks'][$sch['id']] = $r;
                if ($r) {
                    $row['obtained'] += (float)$r['marks_obtained'];
                    $row['full'] += (int)$sch['full_marks'];
                    if ((float)$r['marks_obtained'] < (float)$sch['pass_marks']) $row['passed'] = false;
                }
            }
            $row['percentage'] = $row['full'] > 0 ? round($row['obtained'] / $row['full'] * 100, 1) : 0;
            $report[] = $row;
        }
        // Sort by percentage descending
        usort($report, fn($a, $b) => $b['percentage'] <=> $a['percentage']);

        view('exams/report_card', [
            'exam' => $exam, 'schedules' => $schedules, 'report' => $report,
            'title' => 'Report Card — ' . $exam['name'], 'page' => 'exams',
            'print' => true,
        ]);
    }

    public function subjectsIndex(): void
    {
        require_role(['admin', 'accountant', 'teacher']);
        redirect('subjects/manage');
    }

    public function subjectsManage(): void
    {
        require_role(['admin', 'accountant']);
        $subjects = $this->subjects();
        $assignments = Database::all(
            "SELECT ts.id, ts.staff_id, ts.subject_id,
                    CONCAT(s.first_name,' ',s.last_name) AS teacher_name, sub.name AS subject_name
             FROM teacher_subjects ts
             JOIN staff s ON s.id=ts.staff_id
             JOIN subjects sub ON sub.id=ts.subject_id
             ORDER BY sub.name, s.first_name"
        );
        view('exams/subjects', [
            'subjects' => $subjects, 'assignments' => $assignments,
            'teachers' => Database::all("SELECT id, first_name, last_name FROM staff WHERE role='teacher' AND is_active=1 ORDER BY first_name"),
            'title' => 'Subjects', 'page' => 'exams',
        ]);
    }

    public function subjectsManageStore(): void
    {
        require_role(['admin', 'accountant']);
        csrf_check();

        // Add subject
        $name = trim($_POST['subject_name'] ?? '');
        $code = trim($_POST['subject_code'] ?? '');
        if ($name !== '' && $code !== '') {
            $dup = Database::one("SELECT id FROM subjects WHERE code=?", [$code]);
            if (!$dup) {
                Database::execute("INSERT INTO subjects (name, code) VALUES (?,?)", [$name, $code]);
            }
        }

        // Add assignment (teacher-subject)
        $teacherId = (int)($_POST['teacher_id'] ?? 0);
        $subjectId = (int)($_POST['assign_subject_id'] ?? 0);
        if ($teacherId && $subjectId) {
            $dup = Database::one("SELECT id FROM teacher_subjects WHERE staff_id=? AND subject_id=?", [$teacherId, $subjectId]);
            if (!$dup) {
                Database::execute("INSERT INTO teacher_subjects (staff_id, subject_id) VALUES (?,?)", [$teacherId, $subjectId]);
            }
        }

        // Remove assignment if requested
        if (($remove = (int)($_POST['remove_assignment_id'] ?? 0)) > 0) {
            Database::execute("DELETE FROM teacher_subjects WHERE id=?", [$remove]);
        }

        flash_set('success', 'Subjects updated.');
        redirect('subjects/manage');
    }

    // ----- helpers -----
    private function subjects(): array
    {
        return Database::all("SELECT * FROM subjects ORDER BY name");
    }

    private function currentStaffId(): ?int
    {
        $u = Auth::user();
        return isset($u['staff_id']) && $u['staff_id'] ? (int)$u['staff_id'] : null;
    }

    private function gradeFor(float $marks, int $scheduleId): string
    {
        $sch = Database::one("SELECT full_marks, pass_marks FROM exam_schedules WHERE id=?", [$scheduleId]);
        $full = (int)($sch['full_marks'] ?? 100);
        $pct = $full > 0 ? $marks / $full : 0;
        if ($pct >= 0.90) return 'A+';
        if ($pct >= 0.80) return 'A';
        if ($pct >= 0.70) return 'B';
        if ($pct >= 0.60) return 'C';
        if ($pct >= 0.50) return 'D';
        return 'F';
    }
}
