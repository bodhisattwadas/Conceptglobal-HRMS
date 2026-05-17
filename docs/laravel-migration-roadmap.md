# Horilla HRMS Django to Laravel Migration Roadmap

Generated from the current repository scan on 2026-05-16.

## Executive Summary

This codebase is a large Django 4.2 monolith with server-rendered templates, HTMX partials, a Django REST Framework API, background schedulers, audit/history hooks, dynamic model fields, email automation, and many HR domain modules.

This should be rebuilt in Laravel as a domain-by-domain migration, not mechanically converted file-by-file. The safest path is to preserve the existing database semantics and user workflows first, then modernize implementation details after parity is proven.

Recommended target:

- Laravel 11 or 12, PHP 8.3+
- MySQL 8.0+ as the primary database
- Blade + HTMX for the first parity build, because the current UI is already server-rendered and HTMX-heavy
- Laravel Sanctum for first-party/API authentication, or Passport only if third-party OAuth API clients are required
- Laravel Policies/Gates mapped from Django permissions
- Laravel Queues and Scheduler for async jobs
- Spatie packages for permissions and activity/audit logs
- Laravel Excel, DomPDF/Snappy, Flysystem/S3, and Notifications for equivalent integrations

## Current System Inventory

### Application Shape

The repository contains roughly:

- 1,871 relevant Python/HTML/JS files, excluding generated static build/media areas
- About 200 Django model classes
- More than 1,300 Django route entries across web and API URL files
- Hundreds of function-based views, with the largest files being:
  - `base/views.py`
  - `leave/views.py`
  - `pms/views.py`
  - `employee/views.py`
  - `recruitment/views/views.py`
- Minimal meaningful tests: most `tests.py` files are placeholders with only 0-4 lines

### Main Django Modules

Core modules:

- `base`: companies, departments, jobs, shifts, work types, announcements, email config, holidays, approval conditions, company leave, notification sound, common settings
- `employee`: employee profiles, work info, bank info, notes, policies, disciplinary actions, bonus points, profile edit settings
- `attendance`: clock in/out, attendance records, requests, overtime, late/early tracking, validation rules, work records, allowed IPs
- `leave`: leave types, balances, requests, allocation requests, holidays, company leaves, restrictions, compensatory leave
- `payroll`: contracts, allowances, deductions, payslips, loans, reimbursements, tax brackets/filing status, auto generation settings
- `recruitment`: recruitments, stages, candidates, surveys, skill zones, interviews, LinkedIn, resume matching
- `onboarding` and `offboarding`: candidate onboarding pipeline, tasks, portal, resignation, exit tasks
- `pms`: objectives, key results, feedback, meetings, bonus points
- `asset`: asset category/lot/assets, assignment, request, return, documents
- `helpdesk`: tickets, claim requests, comments, attachments, FAQ
- `project`: projects, stages, tasks, timesheets

Add-on and platform modules:

- `horilla_api`: DRF API with JWT auth
- `horilla_audit`: audit/history tracking
- `horilla_automations`: mail/notification automation on create/update/delete
- `dynamic_fields`: runtime-added model fields
- `horilla_views` and `horilla_widgets`: generic list/detail/form/table helpers
- `horilla_documents`: document requests and documents
- `biometric`, `facedetection`, `geofencing`: attendance integrations
- `horilla_backup`: local/Google Drive backup
- `horilla_ldap`, `outlook_auth`: external identity/integration support
- `accessibility`, `notifications`, `report`

### Shared Framework Behavior To Preserve

These behaviors are cross-cutting and must be implemented early in Laravel:

- Request-aware model metadata: `created_by`, `modified_by`, `created_at`, `is_active`
- Company-scoped queries through `HorillaCompanyManager`
- Current selected company stored in session
- Soft active/inactive behavior, especially employee filtering
- Audit trail through `django-auditlog` and custom history models
- Dynamic email configuration per company/user context
- Notification storage and read/delete workflows
- File upload paths and private/public media behavior
- XSS validation on text fields
- Date/time format preferences per company
- Permission checks at both route and object level
- HTMX partial responses and modal workflows
- Import/export workflows for Excel/CSV/PDF
- Scheduler-driven reminders, payroll generation, attendance/leave maintenance

## Laravel Architecture

### Suggested Directory Layout

Use Laravel modules by domain while keeping Laravel-native conventions:

```text
app/
  Domain/
    Core/
    Employee/
    Attendance/
    Leave/
    Payroll/
    Recruitment/
    Onboarding/
    Offboarding/
    Performance/
    Asset/
    Helpdesk/
    Project/
    Automation/
    Audit/
    Integration/
  Http/
    Controllers/
    Requests/
    Resources/
    Middleware/
  Models/
  Policies/
  Services/
  Actions/
  Jobs/
  Notifications/
  Exports/
  Imports/
resources/
  views/
    layouts/
    components/
    domains/
routes/
  web.php
  api.php
  domains/
database/
  migrations/
  seeders/
```

For the first phase, keeping models in `app/Models` is acceptable. Move domain services/actions under `app/Domain/*` to prevent very large controllers from reappearing.

### Django To Laravel Mapping

| Django concept | Laravel equivalent |
| --- | --- |
| `models.Model` / `HorillaModel` | Eloquent model + base trait |
| `HorillaCompanyManager` | global/local Eloquent scopes |
| `forms.py` validation | Form Request classes |
| `filters.py` / `django-filter` | query filter classes |
| function views | controllers + action/service classes |
| templates | Blade views/components |
| HTMX partial endpoints | Blade partial routes returning fragments |
| DRF serializers | API Resources + Form Requests |
| DRF APIViews | API controllers |
| Django permissions | Laravel policies/gates + Spatie Permission |
| signals | model observers/events/listeners |
| APScheduler | Laravel Scheduler + queued jobs |
| notifications app | Laravel Notifications + database channel |
| auditlog/simple_history | Spatie Activitylog or custom audit tables |
| `FileField`/storage | Laravel filesystem disks |
| management commands | Artisan commands |
| load_data JSON fixtures | seeders/import commands |

## Migration Strategy

### Rule 1: Build The Laravel Foundation First

Do not start with payroll, recruitment, or attendance. They depend on employees, company scoping, permissions, shifts, settings, audit, email, and files.

Foundation order:

1. Auth, users, roles, permissions
2. Company scoping and selected company session middleware
3. Base model trait with audit metadata and XSS validation
4. Core company/org models
5. Employee profile and work information
6. Shared UI shell, sidebar, nav, notifications, modals, HTMX conventions

### Rule 2: Preserve Table And Field Semantics Initially

Because this repository has no real migration history beyond `__init__.py`, schema must be generated from Django models and verified against a running Django database if one is available.

Initial Laravel migrations should keep recognizable names and relationships. Rename/refactor later only after parity tests pass.

For MySQL, use `utf8mb4` everywhere, prefer `bigint unsigned` IDs, map Django `JSONField` to native MySQL `json`, and be careful with indexed string lengths, timezone handling, boolean casting, and full-text/search behavior. Any PostgreSQL-specific fixture, dump, or query behavior should be normalized during import instead of carried into Laravel.

### Rule 3: Convert Workflows, Not Files

Many Django views are very large. Convert each user workflow end-to-end:

- list/filter/group/export
- create/update/delete/archive
- approve/reject/cancel
- single modal/detail view
- bulk actions
- notification/email/audit side effects

### Rule 4: API Parity Comes After Domain Parity

The current API mirrors core HR modules. Build the web/domain layer first, then expose API Resources over the same services.

### Rule 5: Tests Must Be Added During Conversion

The source app has almost no meaningful tests. Laravel needs feature tests for each converted workflow, especially approval, payroll, attendance, leave balances, and permissions.

## Phase Plan

### Phase 0: Discovery And Baseline

Deliverables:

- Run the Django app locally with sample data
- Export the real source schema/data if available, then convert it into MySQL-compatible Laravel migrations
- Capture screenshots of core workflows
- Capture API responses from `horilla_api`
- Build a route/model/workflow matrix
- Decide whether to keep Blade+HTMX or introduce Livewire/Inertia after parity

Exit criteria:

- Known source-of-truth schema
- Known critical workflows
- Laravel project skeleton decision approved

### Phase 1: Laravel Skeleton And Shared Platform

Deliverables:

- New Laravel app scaffold
- Authentication
- Role/permission package
- Base model trait:
  - created/modified user tracking
  - `is_active`
  - company scope helpers
  - XSS/text validation strategy
- Company selection middleware
- File upload abstraction
- Audit activity tables
- Notification database channel
- Shared Blade layout matching Horilla shell
- HTMX request/partial conventions

Exit criteria:

- User can log in
- Admin can create companies/departments/job positions/job roles
- Company switch affects scoped data
- Audit log records create/update/delete

### Phase 2: Core HR And Employee

Deliverables:

- Companies, departments, job positions, job roles
- Work types, shifts, rotating shifts, schedules
- Employee, work information, bank details
- Tags, notes, policies, disciplinary actions
- Employee import/export
- Employee permissions and manager visibility

Exit criteria:

- Employee profile works
- Work info drives department/job/shift/work type relations
- Scoped list/filter/export matches Django behavior

### Phase 3: Attendance

Deliverables:

- Attendance activity and attendance records
- Clock in/out
- Worked hours and overtime calculations
- Attendance requests and comments/files
- Validation conditions
- Late come/early out tracking
- Allowed IP, geofencing/facedetection hooks as integration points
- Attendance dashboard

Exit criteria:

- A normal employee can clock in/out
- Manager/admin can validate/approve overtime
- Work records and reports match expected totals

### Phase 4: Leave

Deliverables:

- Leave types and accrual/reset rules
- Available leave balances
- Leave requests, attachments, comments
- Allocation requests
- Company holidays and company leaves
- Restriction settings and compensatory leave
- Approval conditions

Exit criteria:

- Leave balance math is covered by tests
- Request/approve/reject/cancel workflows are parity-complete

### Phase 5: Payroll

Deliverables:

- Contracts
- Allowances and deductions
- Work records and payslip generation
- Loan accounts
- Reimbursements and comments/files
- Tax/filing status rules
- PDF payslip output and email sending
- Auto-generation scheduler

Risk note:

- `FilingStatus` currently supports stored Python code. In Laravel, replace this with a constrained expression/rule engine. Do not execute arbitrary PHP from the database.

Exit criteria:

- Payslip totals and PDFs match known fixture examples
- Payroll actions are audited and permission-controlled

### Phase 6: Recruitment And Onboarding

Deliverables:

- Recruitment pipeline, stages, candidates
- Surveys/templates/questions/answers
- Candidate notes, files, ratings, interviews
- Skill zones and resume matching
- Public candidate self-tracking/login flows
- Onboarding stages/tasks/portal
- Candidate-to-employee conversion

Exit criteria:

- Candidate can move through recruitment to onboarding to employee
- File/document flows are complete

### Phase 7: PMS, Assets, Helpdesk, Projects

Deliverables:

- PMS objectives, key results, feedback, meetings
- Asset lots, assets, assignments, requests, returns
- Helpdesk tickets, comments, attachments, claim requests, FAQ
- Projects, stages, tasks, timesheets

Exit criteria:

- Each module has working list/detail/create/update/delete/archive/bulk workflows
- Reports and dashboards render useful totals

### Phase 8: Automations, Dynamic Fields, Integrations

Deliverables:

- Mail templates and automation triggers
- Dynamic custom fields
- LDAP, Outlook, Google Drive backup, S3 storage
- Biometric device integrations
- Face detection/geofencing integrations
- Reporting module
- Accessibility and white-label settings

Dynamic fields recommendation:

- Do not dynamically alter Laravel migrations at runtime.
- Prefer a `custom_fields` and `custom_field_values` design, with typed values and indexes for searchable fields.
- Only generate physical columns for highly used, stable fields after product review.

Exit criteria:

- Admin-configurable fields and automations work without runtime schema mutation
- External integrations are behind service interfaces and can be disabled safely

## Data Migration Plan

1. Generate schema map from Django models.
2. Confirm actual production database schema if available.
3. Create Laravel migrations in dependency order:
   - auth/core
   - companies/org
   - employee/work info
   - attendance/leave/payroll
   - remaining modules
4. Convert `load_data/*.json` fixtures into Laravel seeders.
5. Build import commands:
   - `php artisan horilla:import-users`
   - `php artisan horilla:import-core`
   - `php artisan horilla:import-employees`
   - module-specific imports
6. Run reconciliation reports after import:
   - row counts
   - orphan foreign keys
   - balance totals
   - payslip totals
   - active/inactive counts

## First Build Slice

Start with a Laravel skeleton and implement only this vertical slice:

1. Login/logout
2. Company, department, job position, job role
3. Employee profile and work information
4. Company-scoped employee listing
5. Create/update employee
6. Basic audit log
7. One HTMX modal form
8. One API endpoint for employee list
9. Seed data from `load_data/user_data.json`, `base_data.json`, `employee_info_data.json`, and `work_info_data.json`

This slice proves the most important platform concerns before touching complex HR math.

## Suggested Laravel Packages

- `spatie/laravel-permission` for roles and permissions
- `spatie/laravel-activitylog` for audit trail
- `maatwebsite/excel` for imports/exports
- `barryvdh/laravel-dompdf` or Snappy/wkhtmltopdf for PDFs
- `laravel/sanctum` for API auth
- `laravel/horizon` if Redis queues are used
- `league/flysystem-aws-s3-v3` for S3
- `livewire/livewire` only if the team wants richer reactive components later

## Major Risks

- No real source migration history in the repo; schema must be reverse engineered or read from an existing database.
- Large view files contain business logic mixed with rendering concerns.
- Company-scoped query behavior is implicit and request-dependent.
- Dynamic fields mutate model structure at runtime.
- Payroll contains database-stored executable Python logic.
- Test coverage is effectively absent.
- Many UI interactions depend on HTMX partials and modal targets.
- Multiple schedulers and signal handlers create side effects outside direct request flow.

## Immediate Next Tasks

1. Create a new Laravel project in a sibling directory or dedicated branch.
2. Add packages for auth, permissions, audit, Excel, PDF, and HTMX-friendly Blade layout.
3. Implement the base model trait and company scope middleware.
4. Port the core organization migrations.
5. Port employee migrations and seeders.
6. Build the first employee list/create/edit workflow.
7. Add feature tests for company scoping and employee CRUD.
