# AGENTS.md

## Project status
SchoolERP — a production-grade **School Management ERP** for staff/admin (not a parent portal),
built in **plain PHP 8 + MySQL (XAMPP)**. All 6 required modules are implemented:
Staff, Students (+classes/sections/bulk promotion), Finance (payroll/fees/petty),
Exams & Results, Timetable, WhatsApp/SMS Notifications.

## Run it
```
# dev server (must use the router file so static assets are served)
C:\xampp\php\php.exe -S 127.0.0.1:8000 -t public public\_router.php
```
- Plain `php` on PATH (WinGet PHP 8.4) also works now: its `php.ini` had
  `extension=mysqli`/`pdo_mysql` commented out, they've been enabled. If a fatal
  "Call to undefined function mysqli_report()" ever appears again, either run
  `C:\xampp\php\php.exe` instead, or re-enable `extension=mysqli` in that
  php.ini. The app also fails gracefully with an explanatory page instead of
  a fatal when mysqli is missing.
- First run redirects to `/install.php` (web installer) or use `php database/install.php` (CLI).
- Default admin login: `admin` / `admin123`.
- Apache: set document root to `public/`; `.htaccess` provided with mod_rewrite.

## Layout & architecture
- `public/index.php` — front controller + router bootstrap.
- `app/Router.php` — lightweight GET/POST router with `{param}` patterns and CSRF/JSON hooks.
- `app/controllers/*` — per-module controllers, lazily autoloaded.
- `app/views/*` — templates; `layouts/app.php` shell + reusable partials.
- `app/services/NotificationService.php` — Twilio sender; **dry_run is ON by default**.
- `app/Database.php` — mysqli prepared-statement wrapper (`Database::all/one/run/insert`).
- `database/schema.sql` — full schema + seed data.
- `config/config.php` — app/DB/notifications config; `config/local.php` (git-ignored) overrides.

## Conventions / gotchas
- DB credentials live in `config/config.php` or override in `config/local.php` (copy
  `config/local.php.example`). Do NOT commit `config/local.php`.
- Config targets **root/empty** currently. The running MySQL on port 3306 is the **XAMPP
  MySQL/MariaDB** (not the MySQL80 service) and root has an empty password. If MySQL80's root
  password ever returns, see `updateReport.txt` for reset steps.
- All response data passed through `e()` (HTML-escape) in views; controllers call `csrf_check()`
  on POSTs. Auth/role guards: `require_role([...])` (admin/accountant/teacher); `superadmin`
  bypasses every role gate. Superadmin staff records are **protected** — other roles cannot
  view/edit/delete them (`is_protected_staff()` / `can_manage_staff()`). Default logins:
  `admin`/`admin123` and `superadmin`/`superadmin123`.
- Router gotcha: route patterns are stored without a leading slash but matched with an optional
  one (`#^/?<pattern>/?$#`) — keep route paths slash-less (e.g. `staff/edit/{id}`).
- Summaries/changes are tracked in `updateReport.txt` at repo root.

## Verification
- Lint every PHP file: `php -l <file>`. There is no test suite yet.
- Smoke-test: start the dev server above and confirm login + a few module pages render
  (all 21 module routes were verified 200 on the WinGet PHP after enabling mysqli).
