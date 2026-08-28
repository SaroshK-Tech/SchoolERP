<?php
declare(strict_types=1);

class ClassController
{
    public function index(): void
    {
        require_role(['admin', 'accountant']);
        $classes = Database::all("SELECT * FROM classes ORDER BY numeric_rank, name");
        $sections = Database::all(
            "SELECT sec.*, c.name AS class_name, CONCAT(s.first_name,' ',s.last_name) AS teacher_name
             FROM sections sec
             JOIN classes c ON c.id=sec.class_id
             LEFT JOIN staff s ON s.id=sec.teacher_id
             ORDER BY c.numeric_rank, c.name, sec.name"
        );
        $studentCounts = [];
        foreach (Database::all(
            "SELECT sec.id AS section_id, COUNT(ce.id) AS cnt
             FROM sections sec
             LEFT JOIN student_enrolments ce ON ce.section_id=sec.id AND ce.session_id = ?
             GROUP BY sec.id",
            [$this->currentSession()]
        ) as $r) {
            $studentCounts[$r['section_id']] = (int)$r['cnt'];
        }

        view('classes/index', [
            'classes' => $classes, 'sections' => $sections, 'studentCounts' => $studentCounts,
            'teachers' => Database::all("SELECT id, first_name, last_name, employee_no FROM staff WHERE role='teacher' AND is_active=1 ORDER BY first_name"),
            'title' => 'Classes & Sections', 'page' => 'classes',
        ]);
    }

    public function create(): void
    {
        require_role(['admin', 'accountant']);
        csrf_check();

        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '');
        $rank = (int)($_POST['numeric_rank'] ?? 0);
        $description = trim($_POST['description'] ?? '');

        if ($name === '' || $code === '') {
            flash_set('danger', 'Class name and code are required.');
            redirect('classes');
        }
        $dup = Database::one("SELECT id FROM classes WHERE code=?", [$code]);
        if ($dup) {
            flash_set('danger', 'Class code already exists.');
            redirect('classes');
        }
        Database::execute(
            "INSERT INTO classes (name, code, numeric_rank, description) VALUES (?,?,?,?)",
            [$name, $code, $rank, $description ?: null]
        );
        $classId = (int)Database::conn()->insert_id;

        // Create sections from comma/line-separated names or letter list
        $sectionsRaw = trim($_POST['sections'] ?? '');
        if ($sectionsRaw !== '') {
            $names = preg_split('/[\s,]+/', $sectionsRaw, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($names as $n) {
                if (!preg_match('/^[A-Za-z0-9]{1,20}$/', $n)) continue;
                Database::execute("INSERT INTO sections (class_id, name) VALUES (?,?)", [$classId, strtoupper($n)]);
            }
        }

        flash_set('success', "Class \"$name\" created.");
        redirect('classes');
    }

    public function destroy(string $id): void
    {
        require_role(['admin']);
        csrf_check();
        // Remove enrolments in this class first (avoid FK errors), then class (cascades sections)
        Database::execute("DELETE FROM student_enrolments WHERE class_id=?", [(int)$id]);
        Database::execute("DELETE FROM classes WHERE id=?", [(int)$id]);
        flash_set('success', 'Class deleted.');
        redirect('classes');
    }

    public function sectionCreate(): void
    {
        require_role(['admin', 'accountant']);
        csrf_check();
        $classId = (int)($_POST['class_id'] ?? 0);
        $name = strtoupper(trim($_POST['name'] ?? ''));
        $room = trim($_POST['room'] ?? '');
        $capacity = (int)($_POST['capacity'] ?? 0);
        $teacherId = $_POST['teacher_id'] !== '' ? (int)$_POST['teacher_id'] : null;

        if ($classId === 0 || $name === '') {
            flash_set('danger', 'Section name is required.');
            redirect('classes');
        }
        $dup = Database::one("SELECT id FROM sections WHERE class_id=? AND name=?", [$classId, $name]);
        if ($dup) { flash_set('danger', 'That section already exists for this class.'); redirect('classes'); }
        Database::execute(
            "INSERT INTO sections (class_id, name, room, capacity, teacher_id) VALUES (?,?,?,?,?)",
            [$classId, $name, $room ?: null, $capacity ?: null, $teacherId]
        );
        flash_set('success', "Section \"$name\" added.");
        redirect('classes');
    }

    public function sectionDelete(string $id): void
    {
        require_role(['admin']);
        csrf_check();
        Database::execute("UPDATE student_enrolments SET section_id=NULL WHERE section_id=?", [(int)$id]);
        Database::execute("DELETE FROM sections WHERE id=?", [(int)$id]);
        flash_set('success', 'Section deleted.');
        redirect('classes');
    }

    private function currentSession(): int
    {
        return (int)Database::scalar("SELECT id FROM academic_sessions WHERE is_current=1 LIMIT 1") ?: 0;
    }
}
