# SchoolERP — Project Handover Report

> **What this is:** One self-contained reference so anyone (including a fresh AI session or a
> new developer) can pick up the project and work on it **without being re-explained anything**.
> It covers what the system is, how to run/install it, all logins, every module/route, roles &
> permissions, the database, and the full history of changes.
>
> Companion files: `updateReport.txt` (day-by-day change log) and `AGENTS.md` (short agent
> instructions). This report is the "everything" version.

---

## 1. What is SchoolERP?

A **production-grade School Management ERP for staff & admin** (NOT a parent/student portal) built
in **plain PHP 8 + MySQL (XAMPP, MariaDB)** with no framework and no Composer. It is a modular
single-root PHP app running through a lightweight custom router/front controller.

All **6 required modules** are implemented and working:

| # | Module | What it does |
|---|--------|--------------|
| 1 | **Staff** | Full CRUD, salary basis, optional login credentials, profile + payroll history |
| 2 | **Students** | Registration, classes & sections, enrolment, **bulk promotion** |
| 3 | **Finance** | Payroll, fee management & collection, petty income/expense, **bulk fee vouchers** |
| 4 | **Exams & Results** | Exams, schedule, results entry, printable report cards, subjects |
| 5 | **Timetable** | Day × period grid, printable, editable, time slots |
| 6 | **Notifications** | WhatsApp & SMS via Twilio (dry-run ON by default); compose, send, logs |

Extra features added on request: Academic Sessions CRUD, **Superadmin** role with full
authorization + a protected account, and a **bulk fee voucher generator by class**.

---

## 2. Tech stack

- **PHP 8** (mysqli + pdo_mysql enabled). XAMPP PHP at `C:\xampp\php\php.exe` (8.2).
  The WinGet PHP 8.4 on PATH also works — its `mysql` extensions were enabled (see §12).
- **MySQL / MariaDB** — the instance on port 3306 is **XAMPP MySQL/MariaDB** (NOT the MySQL80
  Windows service). Root password is **empty**.
- Database name: `school_erp`. **23+ tables** (schema in `database/schema.sql`).
- No framework, no package manager. Custom `Router`, `Database` (mysqli wrapper), `Auth`,
  `Schema`, and view/layout system.

---

## 3. How to run it

```powershell
# Dev server (MUST use the router file so static assets are served):
C:\xampp\php\php.exe -S 127.0.0.1:8000 -t public public\_router.php
```

Then open `http://127.0.0.1:8000/`.

- **First run / fresh DB:** the app redirects to `/install.php` (web) — or run
  `php database/install.php` (CLI). Both create `school_erp` + schema + seed data.
- **Apache:** set document root to `public/` (`.htaccess` provided with mod_rewrite).
- The plain `php` on PATH also works now (WinGet PHP 8.4, mysqli enabled). If you ever see
  "Call to undefined function mysqli_report()", use `C:\xampp\php\php.exe` instead (or re-enable
  `extension=mysqli` in that php.ini). The app also fails gracefully with an explanatory page if
  mysqli is missing.

---

## 4. Logins (default accounts)

| Role      | Username     | Password      | Notes |
|-----------|--------------|---------------|-------|
| **Superadmin** | `superadmin` | `superadmin123` | Full authorization; bypasses every role gate. Its staff record is **protected** — no other role can view/edit/delete it, and it is **hidden** from all other users in Staff. |
| **Admin** | `admin` | `admin123` | Normal admin. |
| **Admin** | `sunny` | `admin123` | Extra admin created on request (linked staff `SUNNY0001`). |

Role hierarchy: `superadmin` > `admin`/`accountant`/`teacher` > `staff`. Superadmin can manage
everything, including deleting (even its own record); other roles cannot touch the superadmin.

---

## 5. Layout & architecture

```
public/index.php        Front controller + router bootstrap; DB/install guard; check_mysqli()
public/_router.php      php -S router file (serves static assets)
public/install.php      Web installer (creates DB + schema + seed)
public/.htaccess        Apache rewrite (docroot = public/)
app/App.php             Boot, config, paths, redirects, url()
app/Router.php          GET/POST router with {param} patterns, CSRF/JSON hooks
app/Database.php        mysqli prepared-statement wrapper: conn/run/all/one/scalar/insert/execute/quote
app/Auth.php            Login, session, Auth::user(), roles
app/helpers.php         e(), redirect(), flash_*, csrf_*, require_role/is_admin/is_superadmin/
                        is_protected_staff/can_manage_staff, check_mysqli(), fmt_date, money
app/view.php            view() + partial() (layout rendering)
app/routes.php          All routes + controller/service autoloader
app/controllers/*.php   Auth, Dashboard, Staff, Student, Session, Class, Promotion,
                        Finance, Exam, Timetable, Notification
app/services/*.php      (NotificationService.php — Twilio sender)
app/views/*             Templates; layouts/app.php shell + per-module views
database/schema.sql     Full schema + seed data
database/install.php    CLI installer
config/config.php       App/DB/notification config
config/local.php        Local overrides (git-ignored; holds live DB creds)
```

Routing convention: `register_routes(Router $route)` in `app/routes.php` maps
`$route->get('path', callable)` / `$route->post(...)`. Route patterns are **slash-less** but
matched with an optional leading slash (`#^/?<pattern>/?$#`) — keep them slash-less.

---

## 6. All routes & access

Every route below. Access = which roles can use it (superadmin always bypasses).

### Auth
| Method | Path | Access | Purpose |
|--------|------|--------|---------|
| GET/POST | `login` | public | Login |
| GET | `logout` | any login | Sign out |

### Dashboard
`GET /` and `GET dashboard` — any login. Live stats + recent payments + upcoming exams.

### Staff `app/controllers/StaffController.php`
| Method | Path | Access |
|--------|------|--------|
| GET | `staff` | admin, accountant |
| GET/POST | `staff/create` (POST=`store`) | admin, accountant |
| GET/POST | `staff/edit/{id}` | admin, accountant (superadmin protected) |
| GET | `staff/view/{id}` | admin, accountant (superadmin protected) |
| POST | `staff/delete/{id}` | admin (superadmin protected) |

Features: search, role/status filters, pagination, salary basis, optional login creation,
profile with payroll history. `superadmin` role only assignable by superadmin; superadmin records
are protected and hidden from other roles.

### Students `app/controllers/StudentController.php`
| Method | Path | Access |
|--------|------|--------|
| GET | `students` | any login |
| GET/POST | `students/create` | admin, accountant |
| GET/POST | `students/edit/{id}` | admin, accountant |
| GET | `students/view/{id}` | any login |
| POST | `students/delete/{id}` | admin |

Registration (personal + guardian + enrolment), list with session/class/section filters, profile
with fee + enrolment history.

### Academic Sessions `app/controllers/SessionController.php`
`GET sessions`, `POST sessions/create`, `GET|POST sessions/edit/{id}`,
`POST sessions/{id}/set-current`, `POST sessions/delete/{id}`. Guards: current/set/edit any;
delete admin-only. Cannot delete last or active session; first session auto-current; duplicates
blocked.

### Classes & Sections `app/controllers/ClassController.php`
`GET classes`, `GET|POST classes/create`, `POST classes/delete/{id}`,
`POST classes/section/create`, `POST classes/section/{id}/delete`. Access: admin, accountant
(delete = admin). Add class with multi-section creation on the index page.

### Promotion `app/controllers/PromotionController.php`
`GET promotion`, `POST promotion/process`. Bulk promote: next class / same class / graduate.

### Finance `app/controllers/FinanceController.php`
| Method | Path | Access |
|--------|------|--------|
| GET | `finance` (overview) | admin, accountant |
| GET | `finance/payroll` | admin, accountant |
| GET/POST | `finance/payroll/generate` | admin, accountant |
| POST | `finance/payroll/{periodId}/mark-paid` | admin, accountant |
| GET | `finance/fees` (structures) | any login |
| POST | `finance/fees/create` | admin, accountant |
| POST | `finance/fees/{id}/delete` | admin, accountant |
| POST | `finance/fees/collect` | admin, accountant |
| GET | `finance/fee-payments` | any login |
| GET | `finance/vouchers` (generator + list) | any login |
| POST | `finance/vouchers/generate` | admin, accountant |
| GET | `finance/vouchers/{id}` (print) | any login |
| POST | `finance/vouchers/{id}/delete` | admin |
| GET | `finance/petty` | admin, accountant |
| POST | `finance/petty/create` | admin, accountant |

Fee vouchers: select class(es) → create a saved voucher for **every** enrolled active student in
the current session, itemizing that class's fee structures, with a printable single-voucher page.

### Exams `app/controllers/ExamController.php`
`GET exams`, `GET|POST exams/create`, `GET exams/{id}`, `GET|POST exams/{id}/schedule`,
`GET|POST exams/{id}/results`, `GET exams/{id}/report-card`, `GET subjects`,
`GET|POST subjects/manage`. Create/schedule/results: admin, accountant, teacher; create/subjects:
admin, accountant. Results = grid of students × subjects, auto-grades, printable report cards.

### Timetable `app/controllers/TimetableController.php`
`GET timetable`, `GET timetable/slots`, `POST timetable/slots/create`, `GET|POST timetable/edit`,
`GET timetable/print`. View = any login; edit = admin, accountant; slots add = admin.

### Notifications `app/controllers/NotificationController.php`
`GET notifications`, `GET|POST notifications/send`, `GET notifications/logs`. Any login. Twilio
SMS + WhatsApp, **dry_run ON by default**.

---

## 7. Roles & permissions

- **Roles** (ENUM on `staff` and `users`): `staff`, `teacher`, `accountant`, `admin`, `superadmin`.
- **Guards** (in `app/helpers.php`):
  - `require_login()`, `require_role([...])`, `require_superadmin()`.
  - `is_admin()` (admin or superadmin), `is_superadmin()`.
  - **Superadmin bypasses every `require_role` gate** (full authorization).
- **Superadmin protection** (`app/controllers/StaffController.php::guardProtected`): only the
  superadmin may view/edit/update/delete a superadmin staff record; forged `superadmin` role
  submissions are rejected in `validate()`.
- **Superadmin hidden**: in the Staff list, non-superadmins never see the superadmin record
  (`StaffController::index()` filters `role != 'superadmin'`) and the filter dropdown hides the
  superadmin option.
- Views escape with `e()`; all POSTs call `csrf_check()`.

---

## 8. Database summary

Schema lives in `database/schema.sql` (DDL + seed). DB name `school_erp`, charset utf8mb4.

**Key tables:**
- `academic_sessions` — sessions; `is_current` marks the active one.
- `staff`, `staff_documents`, `staff_salary_basis`, `users` (login tied to staff).
- `classes`, `sections`, `students`, `student_enrolments` (student ↔ session/class/section).
- `fee_structures` (fee type + amount per class), `fee_payments` (collected receipts),
  `fee_vouchers` + `fee_voucher_items` (bulk-generated demand notes),
  `payroll_periods`, `payroll_entries`, `petty_ledger`.
- `exams`, `subjects`, `exam_schedules`, `exam_results`, `teacher_subjects`.
- `timetable_slots`, `timetable_entries`.
- `notification_logs`, `settings`.

**Seed data:** default session `2024-2025` (current), `admin` user, `superadmin` user
(`SUPER0001`), 6 timetable slots. `config/local.php` (git-ignored) holds live DB credentials
(target: `root` / empty password on XAMPP MySQL/MariaDB port 3306).

---

## 9. How a new AI/developer should proceed safely

1. Read this report first (then `AGENTS.md`, then `updateReport.txt` for the detailed history).
2. `Run it` (see §3). Confirm login works with `superadmin`/`superadmin123` or `admin`/`admin123`.
3. Before editing:
   - Read the relevant controller + views to match conventions.
   - Lint every PHP file you touch: `C:\xampp\php\php.exe -l <file>` (or `php -l <file>`).
   - There is **no test suite**; verify via HTTP smoke tests against the dev server (login →
     check pages return 200 → exercise the POST flow).
4. For DB schema changes: update `database/schema.sql` **and** apply the same DDL to the live
   `school_erp` DB (the app does not auto-migrate beyond checking for the `users` table).
5. Never commit `config/local.php` (secrets). It is git-ignored.
6. After each change, append a milestone to `updateReport.txt`, commit, and push to `origin/main`.

**Known gotchas:**
- **MariaDB** does not support `LIMIT` inside an `IN (subquery)` (hit in the voucher feature) —
  use `LEFT JOIN` or derived tables instead.
- Route patterns must stay **slash-less**.
- Only one MySQL service should hold port 3306 (XAMPP vs MySQL80) — XAMPP is the one in use.
- `root` has an **empty** password on the running XAMPP MySQL.

---

## 10. Git / repo

- Remote: `https://github.com/SaroshK-Tech/SchoolERP.git`, branch `main`.
- `config/local.php` is git-ignored (secrets not committed). `error.txt` is also git-ignored.
- Commit history ~ milestones 1–21 (see `updateReport.txt`).

---

## 11. Feature timeline (milestones 1–21)

1. **Scaffold** — router, Database, Auth, helpers, schema, seed, admin UI.
2. **Staff management** — CRUD, salary basis, login creation, profile.
3. **Students + Classes/Sections + Bulk promotion**.
4. **Finance** — payroll, fees, petty.
5. **Exams & Results** — schedule, results, report cards, subjects.
6. **Timetable** — grid, edit, slots.
7. **Notifications** — Twilio SMS/WhatsApp, dry-run.
8. **Installer/DB guard/router** — `install.php`, Schema guard, `_router.php`, router leading-slash fix.
9. **DB provisioned** + admin password fixed to `admin123`; verified all routes.
10–12. **mysqli** "Call to undefined function mysqli_report()" — defensive `check_mysqli()` added,
    then root-caused and **enabled mysqli/pdo_mysql in the WinGet PHP 8.4**.
13. **Switched to XAMPP MySQL (MariaDB)** — root/empty; DB reinstalled there.
14. **Academic Sessions** — full CRUD + set-current.
15. **Superadmin role** — full authorization + protected account.
16. **Logout button** in topbar.
17. **`sunny` admin login** created.
18. **Superadmin hidden** from other users.
19. **Fix**: "Add Staff with login" wasn't creating the login (`store()` never called `syncLogin()`).
20. **Fix**: "read error" on class create (POST route called undefined `store()`).
21. **Bulk fee voucher generator by classes** (new tables, printable saved vouchers).

---

## 12. Environment troubleshooting notes

- **`Call to undefined function mysqli_report()`:** the WinGet PHP 8.4 on PATH previously had
  `extension=mysqli`/`pdo_mysql` commented out in its php.ini; they are now enabled. If it recurs,
  either run `C:\xampp\php\php.exe` or re-enable those extensions. The app also shows a styled
  error page instead of a fatal when mysqli is missing.
- **Port 3306 conflict:** keep only XAMPP MySQL running; if MySQL80 starts it re-binds 3306.
  (If MySQL80's root password ever returns, see `updateReport.txt` Milestone 8/9 for reset steps.)
- **Login "invalid username or password"** usually means the user account doesn't exist (e.g.
  Milestone 19 case) — check `users` table.
- **MariaDB `LIMIT ... IN` subquery** errors → rewrite with joins.

---

_Generated 2026-08-29. Keep this report up to date as the project evolves — append to the
"Feature timeline" and refresh any changed logins/routes._