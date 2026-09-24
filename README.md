# IT Issue Resolution System System

A Laravel-based IT Issue Resolution System for an IT department, built around the 5-step workflow:

**Submit (Ministry User) → Review & Assign (IT Head) → Assign Technical Officer (Assign Officer) → Resolve (Technical Officer) → Verify & Close (Ministry User)**

This project was hand-built as a complete Laravel skeleton (migrations, models, controllers, routes, Blade views) but **has not had `composer install` run on it**, because this build environment can't reach Packagist. You'll need to run the setup steps below on your own machine, where you have normal internet access.

## Requirements

- PHP 8.2+
- Composer
- MySQL (or SQLite, which is preconfigured as the default for a zero-config quick start)
- Node.js (only if you want to customize the frontend build — not required, since the UI uses Bootstrap 5 via CDN)

## Setup

```bash
# 1. Install PHP dependencies
composer install

# 2. Copy environment file and generate app key
cp .env.example .env
php artisan key:generate

# 3. Database — SQLite is the default (already configured, database/database.sqlite exists as an empty file)
#    If you'd rather use MySQL (matches the architecture diagram's tech stack):
#      - create the database: mysql -u root -p -e "CREATE DATABASE IT Issue Resolution System"
#      - in .env, comment out DB_CONNECTION=sqlite and uncomment the DB_CONNECTION=mysql block, fill in credentials

# 4. Run migrations + seed demo data (roles, departments, categories, demo users)
php artisan migrate --seed

# 5. Link storage (so uploaded attachment images/PDFs are publicly viewable)
php artisan storage:link

# 6. Serve
php artisan serve
```

Visit `http://localhost:8000` — you'll land on the login page.

## Demo accounts

All seeded with password `password123`:

| Role | Username |
|---|---|
| IT Head (Admin) | `ithead` |
| Assign Officer | `assignofficer` |
| Technical Officer | `techofficer` (specialty: Hardware & Network) |
| Technical Officer | `techofficerb` (specialty: Software & Printers) |
| Ministry User (Accounts dept) | `deptuser` |
| Ministry User (HR dept) | `hruser` |

## How the workflow maps to the code

1. **Submit Request** — `deptuser` logs in → Submit Request → `BreakdownRequestController@store`. Creates a `breakdown_requests` row with status `New`, request number like `BRK-2026-0001`, and optional attachments.
2. **Review & Assign** — `ithead` logs in → opens the request → assigns to an Assign Officer → `AssignmentController@store`. Creates an `assignments` row, status becomes `Assigned`.
3. **Assign Technical Officer** — `assignofficer` logs in → opens the request → picks a Technical Officer + optional due date → `OfficerAssignmentController@store`. Creates an `officer_assignments` row, and the request's `assigned_to` is updated.
4. **Resolve Breakdown** — `techofficer` logs in → opens the job → files a work report (problem identified, work performed, parts used, Done/Not Done, optional photos) → `WorkReportController@store`. Request status becomes `Resolved` if marked Done, or stays `In Progress` otherwise.
5. **Verify & Close** — `deptuser` logs in → sees the resolved request → confirms Yes (closes) or No (reopens, sends back to the Technical Officer) → `DepartmentConfirmationController@store`.

Every step writes to `activity_logs`, visible in the request detail page's timeline.

## What's included vs. what you'll want to add

**Included:**
- Role-based access via a lightweight `role` middleware (no external package — checks `$user->role->code`)
- Full CRUD for Users and Departments (IT Head only)
- Filterable "All Requests" / "My Requests" list with status, priority, department, and search filters
- Basic Reports page (counts by department, status, priority, category, technician)
- File attachments on both the original request and the work report (polymorphic `attachments` table)
- Status flow: New → Assigned → In Progress → Resolved → Closed, with Reopened as a branch back into the cycle

**Not included yet — worth adding as you build this out further:**
- **Email/SMTP notifications** — the `notifications` table and Laravel's `Notification` system are ready to wire up; you'd add `Notification::send()` calls at each workflow transition (e.g. notify the Assign Officer when IT Head assigns them).
- **PDF/Excel export** — install `barryvdh/laravel-dompdf` and `maatwebsite/excel`, then hook them into `ReportController`.
- **Role/permission package** — if you outgrow the simple middleware approach, `spatie/laravel-permission` is the standard choice.
- **Real charts on the dashboard** — currently just count cards; Chart.js (CDN) would be a light way to add the "status counts & charts" feature from your original spec.
- **Tests** — none written yet; Laravel's default PHPUnit/Pest setup is in place under `tests/`.

## Project structure highlights

```
app/Models/              Role, Department, User, Category, BreakdownRequest,
                          Assignment, OfficerAssignment, WorkReport,
                          DepartmentConfirmation, Attachment, ActivityLog
app/Http/Controllers/     DashboardController (role-routed), BreakdownRequestController,
                          AssignmentController, OfficerAssignmentController,
                          WorkReportController, DepartmentConfirmationController,
                          UserController, DepartmentController, ReportController
app/Http/Middleware/      EnsureRole.php
database/migrations/      11 migrations, in dependency order
database/seeders/         RoleSeeder, DepartmentSeeder, CategorySeeder, UserSeeder
resources/views/          layouts/app.blade.php (sidebar shell),
                          dashboard/{it_head,assign_officer,technical_officer,ministry_user}.blade.php,
                          requests/{index,create,show}.blade.php,
                          users/*, departments/*, reports/index.blade.php
routes/web.php            All routes, grouped by role middleware
```
