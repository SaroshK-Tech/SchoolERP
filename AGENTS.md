# AGENTS.md

## Project status
This repo is a **blank slate**. It currently contains only `projectDetails.txt` — the requirements/spec for a "SchoolERP" (a production-grade School Management ERP, not a student/parent portal). No code, no build tooling, no git repo, no dependencies exist yet. There is nothing to lint/typecheck/test until the project is scaffolded.

## Source of truth
- `projectDetails.txt` is the product spec. Read it before building or adding features.

## Mandatory constraints (from spec)
- **Language/stack:** PHP, running on the local XAMPP install (MySQL via XAMPP).
- **Scope:** ERP for school staff/admin — NOT a student/parent portal.
- **Required modules:** Staff Management; Student Information (registration, class/section management, bulk promotion); Finance (payroll, fee management, petty income/expense); Exam & Result system; Timetable (class + teacher filters, print options); WhatsApp & SMS notifications.
- **Database:** create schema in MySQL.
- **Project location:** build the whole project **in this folder** — do NOT drop it under `xampp/htdocs`.
- Any feature beyond the listed six may only be added after confirming with the user first.

## Notes for agents
- Nothing is scaffolded yet; if you begin, first choose a PHP layout consistent with XAMPP (e.g., document root or routed entrypoint) and confirm before adding scope beyond the spec.
- No commit/PR/release conventions established — there is no git repo initialized.
