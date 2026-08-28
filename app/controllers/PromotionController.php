<?php
declare(strict_types=1);

class PromotionController
{
    public function index(): void
    {
        require_role(['admin', 'accountant']);

        $sessionId = (int)($_GET['session_id'] ?? $this->currentSession());

        // Classes ordered by rank; find next-class mapping for promotion
        $classes = Database::all("SELECT * FROM classes ORDER BY numeric_rank, name");
        $byId = [];
        foreach ($classes as $c) { $byId[$c['id']] = $c; }

        // Students in current session enrolment, grouped to know their class
        $students = Database::all(
            "SELECT s.id, s.admission_no, s.first_name, s.last_name, s.status,
                    c.id AS class_id, c.name AS class_name, c.numeric_rank AS class_rank,
                    sec.name AS section_name, sec.id AS section_id
             FROM students s
             JOIN student_enrolments ce ON ce.student_id=s.id AND ce.session_id=?
             JOIN classes c ON c.id=ce.class_id
             LEFT JOIN sections sec ON sec.id=ce.section_id
             WHERE s.status IN ('active','promoted')
             ORDER BY c.numeric_rank, s.first_name",
            [$sessionId]
        );

        view('promotion/index', [
            'sessionId' => $sessionId,
            'students' => $students,
            'classes' => $classes,
            'byId' => $byId,
            'sessions' => Database::all("SELECT * FROM academic_sessions WHERE is_current=1 OR id != ? ORDER BY is_current DESC, start_date DESC", [$sessionId]),
            'title' => 'Bulk Promotion', 'page' => 'students',
        ]);
    }

    public function process(): void
    {
        require_role(['admin', 'accountant']);
        csrf_check();

        $targetSessionId = (int)($_POST['target_session_id'] ?? 0);
        $students = $_POST['student_ids'] ?? [];   // array of student ids
        $option = $_POST['option'] ?? 'next_class'; // next_class | same_class | graduate

        if ($targetSessionId === 0 || !is_array($students) || count($students) === 0) {
            flash_set('danger', 'Please select students and a target session.');
            redirect('promotion');
        }
        $students = array_map('intval', $students);

        $nextClassByRank = $this->nextClassMap();

        $moved = 0;
        foreach ($students as $sid) {
            $enrol = Database::one(
                "SELECT ce.*, c.numeric_rank AS class_rank FROM student_enrolments ce
                 JOIN classes c ON c.id=ce.class_id
                 WHERE ce.student_id=? AND ce.session_id = ? LIMIT 1",
                [$sid, (int)($_POST['source_session_id'] ?? $this->currentSession())]
            );
            if (!$enrol) continue;

            $targetClassId = null;
            if ($option === 'next_class') {
                $targetClassId = $nextClassByRank[$enrol['class_rank']] ?? null;
            } elseif ($option === 'same_class') {
                $targetClassId = (int)$enrol['class_id'];
            } elseif ($option === 'graduate') {
                Database::execute("UPDATE students SET status='graduated' WHERE id=?", [$sid]);
                $moved++;
                continue;
            }

            if (!$targetClassId) {
                // No next class exists -> mark graduated
                Database::execute("UPDATE students SET status='graduated' WHERE id=?", [$sid]);
                $moved++;
                continue;
            }

            // Check if already enrolled in target session
            $exists = Database::one(
                "SELECT id FROM student_enrolments WHERE student_id=? AND session_id=? AND class_id=?",
                [$sid, $targetSessionId, $targetClassId]
            );
            if ($exists) continue;

            Database::insert(
                "INSERT INTO student_enrolments (student_id, session_id, class_id, section_id, promoted_from_class_id)
                 VALUES (?,?,?,?,?)",
                [$sid, $targetSessionId, $targetClassId, null, (int)$enrol['class_id']]
            );
            Database::execute("UPDATE students SET status='promoted' WHERE id=?", [$sid]);
            $moved++;
        }

        flash_set('success', "$moved student(s) promoted to the new session.");
        redirect('promotion');
    }

    private function nextClassMap(): array
    {
        // rank -> next higher rank's class id
        $classes = Database::all("SELECT id, numeric_rank FROM classes ORDER BY numeric_rank");
        $map = [];
        for ($i = 0; $i < count($classes) - 1; $i++) {
            $map[$classes[$i]['numeric_rank']] = (int)$classes[$i + 1]['id'];
        }
        return $map;
    }

    private function currentSession(): int
    {
        return (int)Database::scalar("SELECT id FROM academic_sessions WHERE is_current=1 LIMIT 1") ?: 0;
    }
}
