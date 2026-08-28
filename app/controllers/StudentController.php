<?php
declare(strict_types=1);

class StudentController
{
    public function index(): void
    {
        require_login();

        $q = trim($_GET['q'] ?? '');
        $classId = trim($_GET['class_id'] ?? '');
        $sectionId = trim($_GET['section_id'] ?? '');
        $sessionId = trim($_GET['session_id'] ?? (string)$this->currentSession());

        $where = ["1=1"];
        $params = [];
        if ($q !== '') {
            $where[] = "(s.first_name LIKE ? OR s.last_name LIKE ? OR s.admission_no LIKE ?)";
            array_push($params, "%$q%", "%$q%", "%$q%");
        }
        if ($classId !== '') {
            $where[] = "ce.class_id = ?";
            $params[] = (int)$classId;
        }
        if ($sectionId !== '') {
            $where[] = "ce.section_id = ?";
            $params[] = (int)$sectionId;
        }
        if ($sessionId !== '') {
            $where[] = "ce.session_id = ?";
            $params[] = (int)$sessionId;
        }

        $whereSql = implode(' AND ', $where);

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;
        $total = (int)Database::scalar(
            "SELECT COUNT(DISTINCT s.id) FROM students s
             LEFT JOIN student_enrolments ce ON ce.student_id = s.id
             WHERE $whereSql", $params);
        $pages = (int)ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;

        $students = Database::all(
            "SELECT s.*, ce.session_id, ce.class_id, ce.section_id,
                    c.name AS class_name, sec.name AS section_name
             FROM students s
             LEFT JOIN student_enrolments ce ON ce.student_id = s.id AND ce.session_id = ?
             LEFT JOIN classes c ON c.id = ce.class_id
             LEFT JOIN sections sec ON sec.id = ce.section_id
             WHERE $whereSql
             ORDER BY s.first_name, s.last_name
             LIMIT $perPage OFFSET $offset",
            array_merge([$sessionId !== '' ? (int)$sessionId : $this->currentSession()], $params)
        );

        $classes = Database::all("SELECT * FROM classes ORDER BY numeric_rank, name");
        $sections = $classId !== ''
            ? Database::all("SELECT * FROM sections WHERE class_id=? ORDER BY name", [(int)$classId])
            : [];

        view('students/index', [
            'students' => $students, 'q' => $q, 'classId' => $classId, 'sectionId' => $sectionId,
            'sessionId' => $sessionId, 'classes' => $classes, 'sections' => $sections,
            'sessions' => $this->sessions(), 'total' => $total, 'page' => $page, 'pages' => $pages,
            'title' => 'Students', 'page' => 'students',
        ]);
    }

    public function create(): void
    {
        require_login();
        view('students/form', [
            'student' => [], 'enrol' => [], 'classes' => $this->classesWithSections(),
            'sessions' => $this->sessions(), 'currentSession' => $this->currentSession(),
            'title' => 'Register Student', 'page' => 'students',
        ]);
    }

    public function store(): void
    {
        require_login();
        csrf_check();

        $data = $this->payload();
        if (!$this->validate($data, 0, $errors)) {
            flash_set('danger', implode(' ', $errors));
            set_old($_POST);
            redirect('students/create');
        }

        $studentId = Database::insert(
            "INSERT INTO students
             (admission_no, first_name, last_name, gender, dob, phone, emergency_phone, email,
              address, blood_group, admission_date, guardian_name, guardian_relation, guardian_phone, status)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
            [
                $data['admission_no'], $data['first_name'], $data['last_name'], $data['gender'],
                $data['dob'] ?: null, $data['phone'] ?: null, $data['emergency_phone'] ?: null,
                $data['email'] ?: null, $data['address'] ?: null, $data['blood_group'] ?: null,
                $data['admission_date'] ?: null, $data['guardian_name'] ?: null,
                $data['guardian_relation'] ?: null, $data['guardian_phone'] ?: null, 'active',
            ]
        );
        $this->enrol($studentId, $data);

        flash_set('success', "Student \"{$data['first_name']} {$data['last_name']}\" registered.");
        redirect('students');
    }

    public function edit(string $id): void
    {
        require_login();
        $student = Database::one("SELECT * FROM students WHERE id=?", [(int)$id]);
        if (!$student) { flash_set('danger', 'Student not found.'); redirect('students'); }
        $enrol = Database::one("SELECT * FROM student_enrolments WHERE student_id=? ORDER BY id DESC LIMIT 1", [(int)$id]);

        view('students/form', [
            'student' => $student, 'enrol' => $enrol, 'classes' => $this->classesWithSections(),
            'sessions' => $this->sessions(), 'currentSession' => $this->currentSession(),
            'title' => 'Edit Student', 'page' => 'students',
        ]);
    }

    public function update(string $id): void
    {
        require_login();
        csrf_check();
        $data = $this->payload();
        if (!$this->validate($data, (int)$id, $errors)) {
            flash_set('danger', implode(' ', $errors));
            redirect('students/edit/' . $id);
        }

        Database::execute(
            "UPDATE students SET
              admission_no=?, first_name=?, last_name=?, gender=?, dob=?, phone=?, emergency_phone=?, email=?,
              address=?, blood_group=?, admission_date=?, guardian_name=?, guardian_relation=?, guardian_phone=?
             WHERE id=?",
            [
                $data['admission_no'], $data['first_name'], $data['last_name'], $data['gender'],
                $data['dob'] ?: null, $data['phone'] ?: null, $data['emergency_phone'] ?: null,
                $data['email'] ?: null, $data['address'] ?: null, $data['blood_group'] ?: null,
                $data['admission_date'] ?: null, $data['guardian_name'] ?: null,
                $data['guardian_relation'] ?: null, $data['guardian_phone'] ?: null,
                (int)$id,
            ]
        );
        // Update current enrolment if provided
        if (($data['class_id'] ?? '') !== '' && isset($data['section_id'])) {
            $existing = Database::one("SELECT id FROM student_enrolments WHERE student_id=? ORDER BY id DESC LIMIT 1", [(int)$id]);
            if ($existing) {
                Database::execute(
                    "UPDATE student_enrolments SET class_id=?, section_id=? WHERE id=?",
                    [(int)$data['class_id'], $data['section_id'] ? (int)$data['section_id'] : null, (int)$existing['id']]
                );
            } else {
                $this->enrol((int)$id, $data);
            }
        }

        flash_set('success', 'Student updated.');
        redirect('students/view/' . $id);
    }

    public function show(string $id): void
    {
        require_login();
        $student = Database::one(
            "SELECT s.*, ce.class_id, ce.section_id, c.name AS class_name, sec.name AS section_name,
                    sess.name AS session_name
             FROM students s
             LEFT JOIN student_enrolments ce ON ce.student_id = s.id AND ce.session_id = ?
             LEFT JOIN classes c ON c.id = ce.class_id
             LEFT JOIN sections sec ON sec.id = ce.section_id
             LEFT JOIN academic_sessions sess ON sess.id = ce.session_id
             WHERE s.id=?",
            [$this->currentSession(), (int)$id]
        );
        if (!$student) { flash_set('danger', 'Student not found.'); redirect('students'); }

        $payments = Database::all(
            "SELECT * FROM fee_payments WHERE student_id=? ORDER BY paid_on DESC LIMIT 15",
            [(int)$id]
        );
        $totalPaid = (float)Database::scalar("SELECT COALESCE(SUM(amount),0) FROM fee_payments WHERE student_id=?", [(int)$id]);

        // Enrolment history
        $history = Database::all(
            "SELECT ce.*, c.name AS class_name, sec.name AS section_name, sess.name AS session_name
             FROM student_enrolments ce
             JOIN classes c ON c.id=ce.class_id
             LEFT JOIN sections sec ON sec.id=ce.section_id
             JOIN academic_sessions sess ON sess.id=ce.session_id
             WHERE ce.student_id=? ORDER BY ce.enrolled_at DESC",
            [(int)$id]
        );

        view('students/show', [
            'student' => $student, 'payments' => $payments, 'totalPaid' => $totalPaid, 'history' => $history,
            'title' => 'Student Profile', 'page' => 'students',
        ]);
    }

    public function destroy(string $id): void
    {
        require_role(['admin']);
        csrf_check();
        Database::execute("DELETE FROM students WHERE id=?", [(int)$id]);
        flash_set('success', 'Student deleted.');
        redirect('students');
    }

    // ----- helpers -----

    private function payload(): array
    {
        return [
            'admission_no' => trim($_POST['admission_no'] ?? ''),
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'gender' => $_POST['gender'] ?? 'other',
            'dob' => $_POST['dob'] ?? '',
            'phone' => trim($_POST['phone'] ?? ''),
            'emergency_phone' => trim($_POST['emergency_phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'blood_group' => trim($_POST['blood_group'] ?? ''),
            'admission_date' => $_POST['admission_date'] ?? '',
            'guardian_name' => trim($_POST['guardian_name'] ?? ''),
            'guardian_relation' => trim($_POST['guardian_relation'] ?? ''),
            'guardian_phone' => trim($_POST['guardian_phone'] ?? ''),
            'class_id' => $_POST['class_id'] ?? '',
            'section_id' => $_POST['section_id'] ?? '',
            'session_id' => $_POST['session_id'] ?? '',
        ];
    }

    private function validate(array $d, int $editId, ?array &$errors): bool
    {
        $errors = [];
        if ($d['first_name'] === '') $errors[] = 'First name is required.';
        if ($d['admission_no'] === '') $errors[] = 'Admission number is required.';
        $dup = Database::one("SELECT id FROM students WHERE admission_no=? LIMIT 1", [$d['admission_no']]);
        if ($dup && (int)$dup['id'] !== $editId) $errors[] = 'Admission number already in use.';
        if ($d['class_id'] === '' && $editId === 0) $errors[] = 'Please assign a class.';
        return count($errors) === 0;
    }

    private function enrol(int $studentId, array $d): void
    {
        $classId = (int)$d['class_id'];
        $sectionId = $d['section_id'] !== '' ? (int)$d['section_id'] : null;
        $sessionId = $d['session_id'] !== '' ? (int)$d['session_id'] : $this->currentSession();

        $exists = Database::one(
            "SELECT id FROM student_enrolments WHERE student_id=? AND session_id=? AND class_id=? AND section_id<=>?",
            [$studentId, $sessionId, $classId, $sectionId]
        );
        if ($exists) return;
        Database::execute(
            "INSERT INTO student_enrolments (student_id, session_id, class_id, section_id) VALUES (?,?,?,?)",
            [$studentId, $sessionId, $classId, $sectionId]
        );
    }

    private function currentSession(): int
    {
        return (int)Database::scalar("SELECT id FROM academic_sessions WHERE is_current=1 LIMIT 1") ?: 0;
    }

    private function sessions(): array
    {
        return Database::all("SELECT * FROM academic_sessions ORDER BY is_current DESC, start_date DESC");
    }

    private function classesWithSections(): array
    {
        $rows = Database::all("SELECT * FROM classes ORDER BY numeric_rank, name");
        foreach ($rows as &$c) {
            $c['sections'] = Database::all("SELECT * FROM sections WHERE class_id=? ORDER BY name", [(int)$c['id']]);
        }
        return $rows;
    }
}
