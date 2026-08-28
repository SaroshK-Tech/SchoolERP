<?php
declare(strict_types=1);

class TimetableController
{
    private function currentSession(): int
    {
        return (int)Database::scalar("SELECT id FROM academic_sessions WHERE is_current=1 LIMIT 1") ?: 0;
    }

    public function index(): void
    {
        require_login();
        $sessionId = (int)($_GET['session_id'] ?? $this->currentSession());
        $classId = (int)($_GET['class_id'] ?? 0);
        $sectionId = (int)($_GET['section_id'] ?? 0);
        $teacherId = (int)($_GET['teacher_id'] ?? 0);

        // Consolidate into section views if a class/section selected, else teacher filter
        $filters = [
            'sessions' => Database::all("SELECT * FROM academic_sessions ORDER BY is_current DESC, start_date DESC"),
            'classes' => Database::all("SELECT * FROM classes ORDER BY numeric_rank, name"),
            'sections' => $classId ? Database::all("SELECT * FROM sections WHERE class_id=? ORDER BY name", [$classId]) : [],
            'teachers' => Database::all("SELECT id, first_name, last_name FROM staff WHERE role='teacher' AND is_active=1 ORDER BY first_name"),
        ];

        $timetable = [];
        $viewType = 'none';
        $label = '';
        $slots = Database::all("SELECT * FROM timetable_slots ORDER BY sort_order");
        $days = [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'];

        $params = [$sessionId];
        $where = "session_id = ?";

        if ($sectionId) {
            $viewType = 'class';
            $where .= " AND section_id = ?";
            $params[] = $sectionId;
            $sec = Database::one("SELECT sec.*, c.name AS class_name FROM sections sec JOIN classes c ON c.id=sec.class_id WHERE sec.id=?", [$sectionId]);
            $label = $sec ? $sec['class_name'] . ' - ' . $sec['name'] : '';
        } elseif ($classId) {
            $viewType = 'class';
            $where .= " AND class_id = ?";
            $params[] = $classId;
            $label = Database::scalar("SELECT name FROM classes WHERE id=?", [$classId]) ?: '';
        } elseif ($teacherId) {
            $viewType = 'teacher';
            $where .= " AND teacher_id = ?";
            $params[] = $teacherId;
            $t = Database::one("SELECT first_name, last_name FROM staff WHERE id=?", [$teacherId]);
            $label = $t ? $t['first_name'] . ' ' . $t['last_name'] : '';
        }

        if ($viewType !== 'none') {
            $entries = Database::all(
                "SELECT te.*, sub.name AS subject_name, sub.code AS subject_code,
                        CONCAT(s.first_name,' ',s.last_name) AS teacher_name,
                        c.name AS class_name, sec.name AS section_name,
                        ts.start_time, ts.end_time, ts.sort_order, ts.name AS slot_name
                 FROM timetable_entries te
                 JOIN subjects sub ON sub.id=te.subject_id
                 LEFT JOIN staff s ON s.id=te.teacher_id
                 JOIN classes c ON c.id=te.class_id
                 LEFT JOIN sections sec ON sec.id=te.section_id
                 JOIN timetable_slots ts ON ts.id=te.slot_id
                 WHERE $where
                 ORDER BY ts.sort_order, te.day_of_week",
                $params
            );
            // Build grid: day -> slot -> entry
            foreach ($entries as $en) {
                $key = ($teacherId && !$sectionId && !$classId) ? 'teacher' : (($en['class_id'] . '-' . ($en['section_id'] ?? '')));
                $timetable[$en['day_of_week']][$en['sort_order']][] = $en;
            }
        }

        view('timetable/index', [
            'filters' => $filters,
            'sessionId' => $sessionId, 'classId' => $classId, 'sectionId' => $sectionId, 'teacherId' => $teacherId,
            'timetable' => $timetable, 'viewType' => $viewType, 'label' => $label,
            'slots' => $slots, 'days' => $days,
            'title' => 'Timetable', 'page' => 'timetable',
        ]);
    }

    public function slots(): void
    {
        require_login();
        $slots = Database::all("SELECT * FROM timetable_slots ORDER BY sort_order");
        view('timetable/slots', [
            'slots' => $slots, 'title' => 'Time Slots', 'page' => 'timetable',
        ]);
    }

    public function slotsCreate(): void
    {
        require_role(['admin']);
        csrf_check();
        $name = trim($_POST['name'] ?? '');
        $start = $_POST['start_time'] ?? '';
        $end = $_POST['end_time'] ?? '';
        $order = (int)($_POST['sort_order'] ?? 0);
        if ($name === '' || $start === '' || $end === '') {
            flash_set('danger', 'Name, start and end times are required.');
            redirect('timetable/slots');
        }
        Database::execute("INSERT INTO timetable_slots (name, start_time, end_time, sort_order) VALUES (?,?,?,?)", [$name, $start, $end, $order]);
        flash_set('success', 'Time slot added.');
        redirect('timetable/slots');
    }

    public function edit(): void
    {
        require_role(['admin', 'accountant']);
        $sessionId = (int)($_GET['session_id'] ?? $this->currentSession());
        $classId = (int)($_GET['class_id'] ?? 0);
        $sectionId = (int)($_GET['section_id'] ?? 0);

        if (!$classId && !$sectionId) {
            view('timetable/edit_pick', [
                'classId' => $classId, 'sectionId' => $sectionId, 'sessionId' => $sessionId,
                'sessions' => Database::all("SELECT * FROM academic_sessions ORDER BY is_current DESC"),
                'classes' => Database::all("SELECT * FROM classes ORDER BY numeric_rank, name"),
                'sections' => $classId ? Database::all("SELECT * FROM sections WHERE class_id=? ORDER BY name", [$classId]) : [],
                'title' => 'Edit Timetable', 'page' => 'timetable',
            ]);
            return;
        }

        $slots = Database::all("SELECT * FROM timetable_slots ORDER BY sort_order");
        $subjects = Database::all("SELECT * FROM subjects ORDER BY name");
        $teachers = Database::all("SELECT id, first_name, last_name FROM staff WHERE role='teacher' AND is_active=1 ORDER BY first_name");

        $where = "session_id=? AND class_id=?";
        $params = [$sessionId, $classId];
        if ($sectionId) { $where .= " AND section_id=?"; $params[] = $sectionId; }
        else { $where .= " AND section_id IS NULL"; }

        $entries = [];
        foreach (Database::all(
            "SELECT * FROM timetable_entries WHERE $where ORDER BY day_of_week, slot_id", $params
        ) as $en) {
            $entries[$en['day_of_week'] . ':' . $en['slot_id']] = $en;
        }

        view('timetable/edit', [
            'sessionId' => $sessionId, 'classId' => $classId, 'sectionId' => $sectionId,
            'slots' => $slots, 'subjects' => $subjects, 'teachers' => $teachers, 'entries' => $entries,
            'days' => [1=>'Monday',2=>'Tuesday',3=>'Wednesday',4=>'Thursday',5=>'Friday',6=>'Saturday',7=>'Sunday'],
            'title' => 'Edit Timetable', 'page' => 'timetable',
        ]);
    }

    public function save(): void
    {
        require_role(['admin', 'accountant']);
        csrf_check();
        $sessionId = (int)($_POST['session_id'] ?? 0);
        $classId = (int)($_POST['class_id'] ?? 0);
        $sectionId = $_POST['section_id'] !== '' ? (int)$_POST['section_id'] : null;

        if ($sessionId === 0 || $classId === 0) {
            flash_set('danger', 'Invalid selection.');
            redirect('timetable/edit');
        }

        $grid = $_POST['grid'] ?? [];   // day -> slot -> subject_id,teacher_id

        // Remove existing entries for this scope
        $where = "session_id=? AND class_id=?";
        $params = [$sessionId, $classId];
        if ($sectionId) { $where .= " AND section_id=?"; $params[] = $sectionId; }
        else { $where .= " AND section_id IS NULL"; }
        Database::execute("DELETE FROM timetable_entries WHERE $where", $params);

        $count = 0;
        foreach ($grid as $day => $slots) {
            foreach ($slots as $slotId => $val) {
                $subjectId = (int)($val['subject_id'] ?? 0);
                $teacherId = (int)($val['teacher_id'] ?? 0);
                if ($subjectId === 0 || $teacherId === 0) continue;
                Database::execute(
                    "INSERT INTO timetable_entries (session_id, class_id, section_id, day_of_week, slot_id, subject_id, teacher_id, room)
                     VALUES (?,?,?,?,?,?,?,?)",
                    [$sessionId, $classId, $sectionId, (int)$day, (int)$slotId, $subjectId, $teacherId, null]
                );
                $count++;
            }
        }
        $scope = Database::scalar("SELECT name FROM classes WHERE id=?", [$classId]);
        flash_set('success', "Timetable updated ($count periods) for $scope" . ($sectionId ? ' - Section' : ''));
        redirect('timetable/edit?session_id=' . $sessionId . '&class_id=' . $classId . ($sectionId ? '&section_id=' . $sectionId : ''));
    }

    public function printView(): void
    {
        require_login();
        // Same rendering as index but forces a printable layout (index already has print CSS with query param handled by index)
        $this->index();
    }
}
