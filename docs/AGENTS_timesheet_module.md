# AGENTS.md - Timesheet Standalone Python Desktop Application

## 1. Application Name

**Timesheet Desktop**

This application is a standalone Python-based desktop executable for managing employee timesheets against projects, tasks, and subtasks. It is intended to run on Windows as an installable or portable `.exe`, without requiring Laravel, PHP, a web server, or a browser.

The app should keep the familiar OpenHRMS / Odoo-style timesheet workflow: project task timesheets, employee-wise grouping, planned vs spent hours, task progress, manager review, and exportable reports.

---

## 2. Main Goal

Build a simple single-user desktop application that supports:

- Project task wise timesheet entry
- Employee selection for timesheet rows
- Project wise and task wise hour summaries
- One All Timesheets screen
- One Create New Timesheet screen
- Manual start time, end time, hours spent, billable, and description fields
- Work timer with Run and Stop buttons
- Automatic timer stop after 10 seconds of mouse/keyboard inactivity
- Local SQLite storage
- Windows `.exe` packaging

The first version should work fully offline on one machine using a local SQLite database.

Do not build an admin interface, login screen, manager review flow, role management, payroll screens, or reporting dashboard in this simplified build.

---

## 3. Target Platform

Primary target:

- Windows 10 and Windows 11
- 64-bit executable
- Offline local database

Recommended runtime stack:

```text
Python 3.11+
PySide6 or PyQt6 for desktop UI
SQLite for local data storage
SQLAlchemy for ORM/database access
Alembic or lightweight migration scripts
Pandas/OpenPyXL for Excel export
ReportLab or WeasyPrint for PDF export
PyInstaller for .exe packaging
pytest for tests
```

Preferred UI toolkit:

```text
PySide6
```

Reason:

- Modern Qt widgets
- Good Windows packaging support
- Tables, tabs, dialogs, charts, menus, and toolbars are mature
- Can be styled to match the OpenHRMS dashboard style

---

## 4. Application Modes

### 4.1 Single-User Offline Mode

Default first release.

- Local SQLite database
- One desktop user at a time
- No login screen
- No admin interface
- No network required

### 4.2 Optional Shared Database Mode

Future release only.

- PostgreSQL or MySQL connection
- Multiple users on LAN
- Same desktop application
- Database connection configured in settings

Do not build shared database mode in Phase 1 unless explicitly requested.

---

## 5. Main Screens

### 5.1 All Timesheets Screen

Main visible elements:

- Page title: All Timesheets
- Refresh button
- Table columns:
  - Date
  - Employee
  - Project
  - Task
  - Start Time
  - End Time
  - Hours Spent
  - Billable
  - Description

Expected behavior:

- User can view all saved timesheet rows.
- New saved rows appear after refresh or after saving from Create New.

---

### 5.2 Create New Timesheet Screen

Main visible elements:

- Employee selector
- Date field
- Project selector
- Task selector
- Start Time field
- End Time field
- Hours Spent field
- Billable selector
- Description text area
- Work Timer section:
  - Timer display
  - Notes field
  - Run button
  - Stop button
  - Action log table

Expected behavior:

- User can manually enter a timesheet.
- User can press Run to start the timer.
- User can press Stop to stop the timer.
- Timer automatically stops after 10 seconds with no keyboard or mouse activity.
- Timer start/stop updates start time, end time, elapsed time, and hours spent.
- User can save the timesheet locally.

---

## 6. Core Features

### 6.1 Timesheet Entry

Each timesheet entry stores:

- Employee
- Date
- Project
- Task
- Subtask, optional
- Description
- Hours spent
- Start time, optional
- End time, optional
- Billable flag, optional
- Status
- Created by
- Approved by
- Company, optional for future multi-company support

Rules:

- Hours spent must be greater than 0.
- Date cannot be far future unless admin allows it.
- Employee cannot submit time for archived projects.
- Employee cannot submit time for completed or cancelled tasks unless admin allows it.
- Same employee can add multiple lines for the same day and task.
- Optional setting can restrict one entry per employee, task, and day.
- If start time and end time are supplied, calculated duration must match or warn against the entered hours.

---

### 6.2 Planned Hours and Progress

Task has initially planned hours.

Progress calculation:

```text
progress = min((total_spent_hours / planned_hours) * 100, 100)
```

If planned hours are 0:

```text
progress = 0
```

Extra hours:

```text
extra_hours = max(total_spent_hours - planned_hours, 0)
```

Remaining hours:

```text
remaining_hours = max(planned_hours - total_spent_hours, 0)
```

Example:

```text
Planned Hours: 40
Spent Hours: 26
Progress: 65%
Remaining: 14
Extra: 0
```

---

### 6.3 Work Timer

The Create New screen includes a simple work timer.

Timer behavior:

- User presses Run to start tracking.
- Start Time is filled from the current local time.
- Timer display updates every second.
- The Total line below the timer updates every second while the timer runs.
- Total time accumulates across multiple Run/Stop cycles until the form is cleared.
- User can type a short note while the timer is running.
- User presses Stop to end tracking manually.
- Timer can stop either from the Stop button or from inactivity timeout.
- End Time is filled from the current local time.
- Hours Spent is calculated from elapsed seconds.
- If there is no mouse or keyboard activity anywhere on the system for 10 seconds, the timer stops automatically.
- Start, button stop, timeout stop, save, and clear actions appear in the action log table.

The timer is local only. Do not build approval states, manager review, or payroll locking for this simplified tracker.

---

## 7. Recommended Project Structure

Use a standalone Python application structure:

```text
timesheet_desktop/
  pyproject.toml
  README.md
  requirements.txt
  .env.example
  src/
    timesheet_desktop/
      __init__.py
      main.py
      app.py
      config.py
      constants.py
      database/
        __init__.py
        connection.py
        migrations.py
        seed.py
      models/
        __init__.py
        user.py
        role.py
        employee.py
        department.py
        project.py
        project_task.py
        project_subtask.py
        timesheet.py
        timesheet_status_log.py
        timesheet_setting.py
      repositories/
        employee_repository.py
        project_repository.py
        task_repository.py
        timesheet_repository.py
        report_repository.py
      services/
        timesheet_service.py
        timesheet_progress_service.py
        work_timer_service.py
        timesheet_report_service.py
        timesheet_export_service.py
        backup_service.py
      ui/
        main_window.py
        timesheet_list_view.py
        timesheet_create_view.py
        widgets/
          work_timer.py
      assets/
        icons/
        styles/
          app.qss
      exports/
        templates/
      utils/
        date_utils.py
        file_utils.py
        validators.py
  tests/
    test_timesheet_service.py
    test_progress_service.py
    test_permissions.py
    test_exports.py
  packaging/
    pyinstaller.spec
    app_icon.ico
    installer/
```

---

## 8. Local Database Structure

Use SQLite for Phase 1.

Default database location:

```text
%APPDATA%/ConceptGlobal/TimesheetDesktop/timesheet.db
```

Portable mode database location:

```text
./data/timesheet.db
```

### 8.1 users

```text
id
employee_id nullable
name
email unique
password_hash
role_id
is_active boolean default true
last_login_at nullable
created_at
updated_at
```

### 8.2 roles

```text
id
name unique
description nullable
created_at
updated_at
```

### 8.3 permissions

```text
id
name unique
description nullable
```

### 8.4 role_permissions

```text
role_id
permission_id
```

### 8.5 employees

```text
id
department_id nullable
employee_code unique
name
email nullable
phone nullable
job_title nullable
manager_employee_id nullable
is_active boolean default true
created_at
updated_at
```

### 8.6 departments

```text
id
name unique
manager_employee_id nullable
created_at
updated_at
```

### 8.7 projects

```text
id
name
code unique
customer_name nullable
manager_employee_id nullable
description nullable
status enum: draft, active, on_hold, completed, cancelled
start_date nullable
end_date nullable
created_by
updated_by
created_at
updated_at
deleted_at nullable
```

### 8.8 project_tasks

```text
id
project_id
parent_task_id nullable
title
description nullable
customer_name nullable
deadline nullable
planned_hours decimal default 0
spent_hours decimal default 0
remaining_hours decimal default 0
extra_hours decimal default 0
progress_percent decimal default 0
status enum: new, in_progress, blocked, done, cancelled
is_recurrent boolean default false
priority enum: low, normal, high, urgent
created_by
updated_by
created_at
updated_at
deleted_at nullable
```

### 8.9 project_task_assignees

```text
id
project_task_id
employee_id
assigned_by
assigned_at
```

Unique:

```text
project_task_id + employee_id
```

### 8.10 timesheets

```text
id
employee_id
department_id nullable
project_id
project_task_id
project_sub_task_id nullable
date
start_time nullable
end_time nullable
hours_spent decimal
description text nullable
is_billable boolean default false
            status enum: saved
source enum: manual, project_task, import, admin_adjustment
created_by
updated_by
created_at
updated_at
deleted_at nullable
```

Indexes:

```text
employee_id + date
project_id + date
project_task_id + date
status
```

### 8.11 timesheet_status_logs

```text
id
timesheet_id
old_status nullable
new_status
changed_by
reason nullable
changed_at
created_at
updated_at
```

### 8.12 timesheet_settings

```text
id
allow_future_entries boolean default false
future_entry_limit_days integer default 0
allow_employee_edit_after_submit boolean default false
allow_employee_delete_after_submit boolean default false
minimum_hours_per_entry decimal default 0.25
maximum_hours_per_day decimal default 24
restrict_to_assigned_tasks boolean default false
created_by
updated_by
created_at
updated_at
```

### 8.13 audit_logs

```text
id
user_id
entity_type
entity_id
action
old_values_json nullable
new_values_json nullable
created_at
```

---

## 9. Python Models

Use SQLAlchemy models or dataclasses backed by repositories.

Example model names:

```text
User
Role
Permission
Employee
Department
Project
ProjectTask
ProjectSubTask
ProjectTaskAssignee
Timesheet
TimesheetStatusLog
TimesheetSetting
AuditLog
```

Relationships:

- Employee has many timesheets.
- Employee may have a manager employee.
- Project has many tasks.
- Project has many timesheets.
- ProjectTask belongs to project.
- ProjectTask has many assignees.
- ProjectTask has many timesheets.
- Timesheet belongs to employee, project, and task.
- Timesheet stores local start time, end time, hours, and billable state.

---

## 10. Services

### 10.1 TimesheetService

Responsibilities:

- Create timesheet
- Update timesheet
- Delete timesheet
- Validate daily hour limit
- Validate project/task status
- Validate role permissions
- Trigger task progress recalculation
- Write audit log entries

Suggested methods:

```python
create_timesheet(data: dict, current_user: User) -> Timesheet
update_timesheet(timesheet_id: int, data: dict, current_user: User) -> Timesheet
delete_timesheet(timesheet_id: int, current_user: User) -> None
validate_daily_hour_limit(employee_id: int, date: date, hours: Decimal) -> None
```

### 10.2 TimesheetProgressService

Suggested methods:

```python
recalculate_task(task_id: int) -> None
get_spent_hours(task_id: int) -> Decimal
get_progress(task_id: int) -> Decimal
get_remaining_hours(task_id: int) -> Decimal
get_extra_hours(task_id: int) -> Decimal
```

Logic:

- Sum saved entries.
- Update task spent hours.
- Update task progress.
- Update task remaining and extra hours.

### 10.3 WorkTimerService

Suggested methods:

```python
start(note: str = "") -> None
stop(reason: str = "Stopped") -> TimerSession
elapsed_seconds() -> int
should_auto_stop(last_activity_at: float) -> bool
```

### 10.4 TimesheetReportService

Suggested methods:

```python
employee_summary(filters: dict) -> list[dict]
project_summary(filters: dict) -> list[dict]
task_summary(filters: dict) -> list[dict]
daily_summary(filters: dict) -> list[dict]
```

### 10.5 TimesheetExportService

Suggested methods:

```python
export_csv(filters: dict, destination: Path) -> Path
export_xlsx(filters: dict, destination: Path) -> Path
export_pdf_summary(filters: dict, destination: Path) -> Path
```

### 10.6 BackupService

Suggested methods:

```python
create_backup(destination: Path) -> Path
restore_backup(source: Path) -> None
```

Rules:

- Backups should include the SQLite database.
- Restore must warn the user before replacing current data.
- Automatic backups can run on app close or before migrations.

---

## 11. UI Design Instructions

### 11.1 Common Layout

Use:

- Desktop main window with top menu and toolbar
- Left navigation or tab-based module navigation
- Clean white content panels
- Light neutral background
- Purple accent color inspired by OpenHRMS/Odoo screenshots
- Search bar with filters
- Group By and Favorites controls
- List/grid/pivot/chart view buttons where practical
- Clear table and form controls

Recommended desktop navigation:

```text
All Timesheets
Create New
```

### 11.2 Timesheet List Page

Sections:

```text
Header:
- All Timesheets title
- Refresh button

Table:
- Date
- Employee
- Project
- Task
- Start Time
- End Time
- Hours Spent
- Billable
- Description
```

### 11.3 Create New Page

Layout:

```text
Employee | Date
Project  | Task
Start Time | End Time
Hours Spent | Billable
Description
Work Timer
Action Log

Buttons:
Run | Stop | Save Timesheet | Clear
```

### 11.4 Dialogs

Use dialogs only for:

- Validation errors
- Save success messages

Forms must show validation messages near the field.

---

## 12. Filters

Filters are not required for the first simplified build.

Future optional filters:

- Date range
- Employee
- Project
- Task
- Billable

---

## 13. Group By Options

Grouping is not required for the first simplified build.

---

## 14. Access Control

Do not build role-based access control in this build.

Rules:

- The app opens directly as a single-user tracker.
- No admin interface.
- No login screen.
- No manager approval.
- No payroll role.
- No permission configuration screen.

---

## 15. Reporting

### 15.1 Employee Time Report

Columns:

- Employee
- Department
- Total hours
- Approved hours
- Billable hours
- Non-billable hours
- Project count
- Task count

### 15.2 Project Time Report

Columns:

- Project
- Planned hours
- Spent hours
- Remaining hours
- Extra hours
- Progress
- Billable hours

### 15.3 Task Progress Report

Columns:

- Task
- Project
- Assignee
- Planned hours
- Spent hours
- Progress
- Status
- Deadline

### 15.4 Payroll Support Report

Columns:

- Employee
- Date range
- Approved hours
- Overtime hours
- Leave hours, if integrated later
- Payable hours

---

## 16. Export

Export formats:

- CSV
- XLSX
- PDF summary

Export columns:

```text
Date
Employee
Department
Project
Task
Description
Hours Spent
Status
Billable
Approved By
Approved At
```

Export behavior:

- User chooses destination using a Save File dialog.
- File opens automatically only if the user selects "Open after export".
- Exports must respect role permissions.
- Exported numbers should use consistent decimal formatting.

---

## 17. Settings

Settings screen should support:

- Future entry rules
- Maximum hours per day
- Minimum hours per entry
- Timer inactivity limit, default 10 seconds
- Restrict employees to assigned tasks
- Database location display
- Backup location
- Export defaults
- Theme/accent color, optional

---

## 18. Seed Data

Create demo data for development and first-run sample mode.

### Employees

- Abigail Peterson
- Anita Oliver
- Audrey Peterson
- Beth Evans
- Jeffrey Kelly
- Marc Demo
- Walter Horton
- Keith Byrd
- Toni Jimenez
- Tina Williamson

### Projects

- Office Design
- Research & Development

### Tasks

- Meeting Room Furnitures
- Room 2: Decoration
- Office planning
- Unit Testing
- User interface improvements
- Social network integration
- Document management

### Timesheet Entries

Sample:

```text
Abigail Peterson | Research & Development | Unit Testing | Requirements analysis | 03:00
Abigail Peterson | Office Design | Room 2: Decoration | Requirements analysis | 02:00
Marc Demo | Office Design | Meeting Room Furnitures | Requirements analysis | 01:00
Walter Horton | Office Design | Meeting Room Furnitures | Requirements analysis | 01:00
Keith Byrd | Office Design | Meeting Room Furnitures | On Site Visit | 02:00
```

---

## 19. Validation Rules

Timesheet form validation:

```text
employee_id: required, existing employee
project_id: required, existing active project
project_task_id: required, existing active task
date: required date
hours_spent: required number, min setting value, max daily limit
description: optional text
is_billable: boolean
```

Extra validation:

- Task must belong to selected project.
- Employee must be active.
- Employee must be assigned to task if the setting is enabled.
- Date cannot be locked by payroll if payroll integration is added later.
- Date cannot be outside project/task date range if strict mode is enabled.
- End time must be after start time when both are supplied.

---

## 20. Packaging as Windows EXE

Use PyInstaller.

Recommended command:

```bash
pyinstaller packaging/pyinstaller.spec --clean --noconfirm
```

The packaged output should include:

```text
dist/
  TimesheetDesktop/
    TimesheetDesktop.exe
    assets/
    qt runtime files
```

Optional one-file build:

```bash
pyinstaller --name TimesheetDesktop --windowed --onefile src/timesheet_desktop/main.py
```

Prefer folder-based build for Phase 1 because Qt applications are easier to debug and update that way.

Packaging requirements:

- App icon included.
- Console window hidden.
- Database is not bundled as a fixed read-only file.
- First launch creates writable app data directory.
- Logs are written to user app data.
- Assets and QSS styles load correctly from packaged executable.

---

## 21. Installer

Installer is optional for Phase 1.

If needed, use one of:

- Inno Setup
- NSIS
- WiX Toolset

Installer should:

- Install the application under Program Files.
- Create Start Menu shortcut.
- Create Desktop shortcut if selected.
- Preserve user database during upgrades.
- Provide uninstall entry.

---

## 22. Logging and Error Handling

Log file location:

```text
%APPDATA%/ConceptGlobal/TimesheetDesktop/logs/app.log
```

Rules:

- Show friendly error dialogs.
- Write technical details to log files.
- Never expose password hashes in logs.
- Record save failures with enough context to debug.
- Record database migration errors.

---

## 23. Testing Plan

### Unit Tests

- User can create a timesheet.
- Start time and end time are stored.
- Short timer-driven entries can be saved.
- Task progress updates after timesheet creation.
- Task progress updates after timesheet update.
- Task progress updates after timesheet deletion.
- All Timesheets returns saved rows.

### Validation Tests

- Hours spent must be positive.
- Hours spent cannot exceed daily limit.
- Task must belong to selected project.
- Employee must be active.

### UI Smoke Tests

- App launches.
- All Timesheets tab loads.
- Create New tab loads.
- Run button starts the timer.
- Stop button stops the timer.
- Timer auto-stops after 10 seconds of keyboard/mouse inactivity.
- Save Timesheet writes the entry.

---

## 24. Implementation Phases

### Phase 1 - Desktop Foundation

- Create Python project structure
- Add desktop main window
- Add local SQLite database
- Add seed employees, projects, and tasks

### Phase 2 - Basic Timesheet CRUD

- Create employees, projects, tasks, and timesheets tables
- Create models and repositories
- Create All Timesheets page
- Create New Timesheet form
- Add employee/project/task selectors
- Add validation

### Phase 3 - Work Timer

- Add Run button
- Add Stop button
- Track elapsed seconds
- Fill start time and end time
- Fill hours spent from elapsed time
- Add notes
- Add action log
- Auto-stop after 10 seconds of keyboard/mouse inactivity

### Phase 4 - Polish

- Improve layout
- Add friendly validation messages
- Add optional basic filters

### Phase 5 - Packaging

- Add app icon
- Add PyInstaller spec
- Build Windows `.exe`
- Test packaged app on a clean Windows machine
- Add optional installer

### Phase 6 - Backup and Restore

- Manual backup
- Manual restore
- Optional automatic backup before migrations
- Optional scheduled backup on app close

---

## 25. Acceptance Criteria

The application is complete when:

- The app runs as a Windows desktop `.exe`.
- The app does not require Laravel, PHP, Apache/Nginx, or a browser.
- First launch creates or connects to a writable SQLite database.
- The app opens without login.
- There is no admin interface.
- User can view one All Timesheets page.
- User can open one Create New page.
- User can add timesheets against project tasks.
- User can enter employee, date, project, task, start time, end time, hours spent, billable, and description.
- User can press Run to start the timer.
- User can press Stop to stop the timer.
- Timer automatically stops after 10 seconds of no keyboard or mouse activity.
- Saved timesheets appear on the All Timesheets page.
- The packaged executable can run on a clean Windows machine.

---

## 26. Agent Instructions

When working on this application:

1. Build this as a standalone Python desktop app, not a Laravel module.
2. Do not require a web server for core functionality.
3. Keep attendance and timesheet data separate.
4. Do not add payroll, approval, manager review, or admin flows unless requested later.
5. Use task planned hours for progress.
6. Always recalculate task totals after insert, update, or delete.
7. Keep important changes in audit logs.
8. Enforce access through a permission service.
9. Keep UI close to the supplied dashboard style while using native desktop patterns.
10. Avoid hardcoded employee names except seed data.
11. Store user data in a writable app data directory.
12. Test both source execution and packaged `.exe` execution.
13. Keep packaging scripts repeatable.

---

## 27. Suggested Commands

Create and run the app during development:

```bash
python -m venv .venv
.venv\Scripts\activate
pip install -r requirements.txt
python -m timesheet_desktop
```

Run tests:

```bash
pytest
```

Build executable:

```bash
pyinstaller packaging/pyinstaller.spec --clean --noconfirm
```

Optional dependency examples:

```bash
pip install PySide6 SQLAlchemy alembic pandas openpyxl reportlab pytest pyinstaller
```

---

## 28. Final Notes

This Timesheet Desktop application is project and task focused. It should help a user record time spent against employees, projects, and tasks with as little friction as possible.

The first build should stay simple, offline, and reliable. Advanced billing, invoice generation, shared database deployment, cloud sync, and detailed analytics can be added later.
