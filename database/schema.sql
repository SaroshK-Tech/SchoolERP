-- ============================================================
-- SchoolERP Database Schema
-- MySQL 8.x (XAMPP compatible)
-- Covers: Staff, Students, Classes/Sections, Finance,
--         Exam & Results, Timetable, Notifications
-- ============================================================

CREATE DATABASE IF NOT EXISTS school_erp
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE school_erp;

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- Core lookups: academic sessions
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS academic_sessions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(64) NOT NULL,                    -- e.g. "2024-2025"
  start_date DATE NULL,
  end_date DATE NULL,
  is_current TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_session_name (name)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Staff Management
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS staff (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  employee_no VARCHAR(32) NOT NULL,
  first_name VARCHAR(80) NOT NULL,
  last_name VARCHAR(80) NOT NULL,
  gender ENUM('male','female','other') NOT NULL DEFAULT 'other',
  dob DATE NULL,
  nationality VARCHAR(64) NULL,
  phone VARCHAR(20) NULL,
  email VARCHAR(120) NULL UNIQUE,
  address TEXT NULL,
  designation VARCHAR(100) NOT NULL,
  department VARCHAR(100) NULL,
  role ENUM('admin','teacher','accountant','staff') NOT NULL DEFAULT 'staff',
  join_date DATE NULL,
  leave_date DATE NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  profile_photo VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_emp_no (employee_no)
) ENGINE=InnoDB;

-- Employee document / qualification records
CREATE TABLE IF NOT EXISTS staff_documents (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  staff_id INT UNSIGNED NOT NULL,
  title VARCHAR(120) NOT NULL,
  file_path VARCHAR(255) NULL,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Auth: users (ties to staff + admin login)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  staff_id INT UNSIGNED NULL,
  role ENUM('admin','teacher','accountant','staff') NOT NULL DEFAULT 'staff',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Classes & Sections
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS classes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL,                     -- e.g. "Grade 8", "Class X"
  code VARCHAR(20) NOT NULL UNIQUE,
  numeric_rank INT NOT NULL DEFAULT 0,           -- ordering for promotion
  description TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sections (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  class_id INT UNSIGNED NOT NULL,
  name VARCHAR(20) NOT NULL,                     -- e.g. "A", "B"
  room VARCHAR(40) NULL,
  capacity SMALLINT UNSIGNED NULL,
  teacher_id INT UNSIGNED NULL,                  -- class teacher (staff)
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_class_section (class_id, name),
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  FOREIGN KEY (teacher_id) REFERENCES staff(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Students
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS students (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admission_no VARCHAR(32) NOT NULL UNIQUE,
  first_name VARCHAR(80) NOT NULL,
  last_name VARCHAR(80) NOT NULL,
  gender ENUM('male','female','other') NOT NULL DEFAULT 'other',
  dob DATE NULL,
  phone VARCHAR(20) NULL,
  emergency_phone VARCHAR(20) NULL,
  email VARCHAR(120) NULL,
  address TEXT NULL,
  blood_group VARCHAR(8) NULL,
  admission_date DATE NULL,
  guardian_name VARCHAR(120) NULL,
  guardian_relation VARCHAR(60) NULL,
  guardian_phone VARCHAR(20) NULL,
  photo VARCHAR(255) NULL,
  status ENUM('active','inactive','promoted','graduated','withdrawn') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Enrolment: which student in which class/section for which session
CREATE TABLE IF NOT EXISTS student_enrolments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  session_id INT UNSIGNED NOT NULL,
  class_id INT UNSIGNED NOT NULL,
  section_id INT UNSIGNED NULL,
  promoted_from_class_id INT UNSIGNED NULL,
  enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_enrol (student_id, session_id, class_id, section_id),
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (session_id) REFERENCES academic_sessions(id) ON DELETE CASCADE,
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,
  FOREIGN KEY (promoted_from_class_id) REFERENCES classes(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Finance: fee structure & payments
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS fee_structures (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  class_id INT UNSIGNED NOT NULL,
  fee_type VARCHAR(60) NOT NULL,                 -- tuition, transport, etc.
  amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  frequency ENUM('monthly','term','yearly','one-time') NOT NULL DEFAULT 'monthly',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS fee_payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id INT UNSIGNED NOT NULL,
  session_id INT UNSIGNED NOT NULL,
  receipt_no VARCHAR(40) NOT NULL UNIQUE,
  amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  paid_on DATE NOT NULL,
  mode ENUM('cash','bank','card','online','other') NOT NULL DEFAULT 'cash',
  ref_no VARCHAR(80) NULL,
  notes TEXT NULL,
  recorded_by INT UNSIGNED NULL,                 -- staff who recorded
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (session_id) REFERENCES academic_sessions(id) ON DELETE CASCADE,
  FOREIGN KEY (recorded_by) REFERENCES staff(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Finance: payroll
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS payroll_periods (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,                     -- e.g. "January 2025"
  period_start DATE NOT NULL,
  period_end DATE NOT NULL,
  is_paid TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payroll_entries (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  period_id INT UNSIGNED NOT NULL,
  staff_id INT UNSIGNED NOT NULL,
  basic_salary DECIMAL(12,2) NOT NULL DEFAULT 0,
  allowances DECIMAL(12,2) NOT NULL DEFAULT 0,
  earnings DECIMAL(12,2) NOT NULL DEFAULT 0,     -- overtime/bonus
  deductions DECIMAL(12,2) NOT NULL DEFAULT 0,   -- tax/loan
  net_pay DECIMAL(12,2) NOT NULL DEFAULT 0,
  status ENUM('draft','paid') NOT NULL DEFAULT 'draft',
  paid_on DATE NULL,
  notes TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_payroll (period_id, staff_id),
  FOREIGN KEY (period_id) REFERENCES payroll_periods(id) ON DELETE CASCADE,
  FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS staff_salary_basis (
  staff_id INT UNSIGNED NOT NULL PRIMARY KEY,
  basic_salary DECIMAL(12,2) NOT NULL DEFAULT 0,
  allowances DECIMAL(12,2) NOT NULL DEFAULT 0,
  monthly_deductions DECIMAL(12,2) NOT NULL DEFAULT 0,
  bank_account VARCHAR(40) NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Finance: petty income & expense ledger
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS petty_ledger (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  entry_date DATE NOT NULL,
  type ENUM('income','expense') NOT NULL,
  category VARCHAR(80) NOT NULL,
  description TEXT NULL,
  amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  ref_no VARCHAR(40) NULL,
  recorded_by INT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (recorded_by) REFERENCES staff(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Exam & Results
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS exams (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,                    -- e.g. "Midterm 2025"
  session_id INT UNSIGNED NOT NULL,
  class_id INT UNSIGNED NOT NULL,
  start_date DATE NULL,
  end_date DATE NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (session_id) REFERENCES academic_sessions(id) ON DELETE CASCADE,
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS subjects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  code VARCHAR(20) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS exam_schedules (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  exam_id INT UNSIGNED NOT NULL,
  subject_id INT UNSIGNED NOT NULL,
  exam_date DATE NULL,
  start_time TIME NULL,
  end_time TIME NULL,
  full_marks INT UNSIGNED NOT NULL DEFAULT 100,
  pass_marks INT UNSIGNED NOT NULL DEFAULT 40,
  FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS exam_results (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  schedule_id INT UNSIGNED NOT NULL,
  student_id INT UNSIGNED NOT NULL,
  marks_obtained DECIMAL(7,2) NULL,
  grade VARCHAR(5) NULL,
  remarks VARCHAR(200) NULL,
  entered_by INT UNSIGNED NULL,
  entered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_result (schedule_id, student_id),
  FOREIGN KEY (schedule_id) REFERENCES exam_schedules(id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (entered_by) REFERENCES staff(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Teacher <-> subject assignment (used by timetable & exams)
CREATE TABLE IF NOT EXISTS teacher_subjects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  staff_id INT UNSIGNED NOT NULL,
  subject_id INT UNSIGNED NOT NULL,
  UNIQUE KEY uq_ts (staff_id, subject_id),
  FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Timetable
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS timetable_slots (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(40) NOT NULL,                     -- e.g. "Period 1"
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS timetable_entries (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  session_id INT UNSIGNED NOT NULL,
  class_id INT UNSIGNED NOT NULL,
  section_id INT UNSIGNED NULL,
  day_of_week TINYINT NOT NULL,                  -- 1=Mon ... 7=Sun
  slot_id INT UNSIGNED NOT NULL,
  subject_id INT UNSIGNED NOT NULL,
  teacher_id INT UNSIGNED NOT NULL,
  room VARCHAR(40) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_timetable (session_id, class_id, section_id, day_of_week, slot_id),
  FOREIGN KEY (session_id) REFERENCES academic_sessions(id) ON DELETE CASCADE,
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,
  FOREIGN KEY (slot_id) REFERENCES timetable_slots(id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
  FOREIGN KEY (teacher_id) REFERENCES staff(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Notifications (WhatsApp & SMS) - outbound log + queue
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notification_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  channel ENUM('whatsapp','sms') NOT NULL,
  to_phone VARCHAR(20) NOT NULL,
  subject VARCHAR(120) NULL,
  message TEXT NOT NULL,
  status ENUM('queued','sent','failed') NOT NULL DEFAULT 'queued',
  provider_ref VARCHAR(120) NULL,
  error TEXT NULL,
  related_type VARCHAR(40) NULL,                 -- e.g. 'fee','result'
  related_id INT UNSIGNED NULL,
  recipient_name VARCHAR(120) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  sent_at DATETIME NULL,
  INDEX idx_status (status),
  INDEX idx_channel (channel)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Generic settings / configuration (webhooks, credentials keys)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
  k VARCHAR(100) NOT NULL PRIMARY KEY,
  v TEXT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- Seed data: default academic session, admin, sample classes
-- ------------------------------------------------------------
INSERT INTO academic_sessions (name, start_date, end_date, is_current)
SELECT '2024-2025', '2024-09-01', '2025-06-30', 1
WHERE NOT EXISTS (SELECT 1 FROM academic_sessions);

-- Default admin user (username: admin / password: admin123)
INSERT INTO staff (employee_no, first_name, last_name, role, designation, department, is_active)
SELECT 'ADMIN0001', 'System', 'Administrator', 'admin', 'Administrator', 'Administration', 1
WHERE NOT EXISTS (SELECT 1 FROM staff WHERE role='admin');

INSERT INTO users (username, password_hash, staff_id, role, is_active)
SELECT 'admin', '$2y$12$1aLXn1yVAB8jDqt7quZb4eH9sMeFRzkBE.p3VwpoaUuFWTOWtpFZ2', id, 'admin', 1
FROM staff WHERE role='admin' LIMIT 1;

INSERT INTO timetable_slots (name, start_time, end_time, sort_order) VALUES
('Period 1', '08:00:00', '08:45:00', 1),
('Period 2', '08:45:00', '09:30:00', 2),
('Period 3', '09:40:00', '10:25:00', 3),
('Period 4', '10:25:00', '11:10:00', 4),
('Period 5', '11:20:00', '12:05:00', 5),
('Period 6', '12:05:00', '12:50:00', 6);
