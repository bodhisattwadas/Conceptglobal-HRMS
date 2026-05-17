# AGENTS.md — Attendance and Leaves Module

## 1. Module Goal

Build a Laravel-based clone of the Open HRMS / Horilla-style **Attendance and Leaves** module.

The module must manage:

- Employee check in and check out
- Kiosk mode attendance
- Attendance machine configuration
- Biometric device data download and clearing
- Attendance records
- Attendance regularization requests
- Time off / leave types
- Leave requests
- Leave approval workflow
- Leave settings
- Vacation management
- Leave reminders
- Integration with employee database, payroll, timesheets, and HR administration

This module should follow the dashboard screenshots:

- Purple top navigation bar
- Odoo/Open HRMS style form pages
- List/table pages with search, filters, group by, favorites
- Kanban/list toggle buttons
- Workflow status ribbon on the right
- Chatter area for notes, messages, followers, and activity logs
- Compact form layout with white card panels over light grey background

---

## 2. Source Notes Used

The module is based on the supplied Attendance and Leaves notes. The notes define real-time attendance processing, multiple capture methods, biometric device integration, attendance regularization, leave policies, automatic leave request by email, leave approvals, and vacation management.

---

## 3. Core Menus

### Attendance App Top Menu

Create a main navigation group named **Attendances**.

Top menu items:

1. **Check In / Check Out**
2. **Kiosk Mode**
3. **Attendances**
4. **Attendance Regularization**
5. **Reporting**
6. **Configuration**
7. Quick add icon / plus button

### Leaves / Time Off App Top Menu

Create a main navigation group named **Leaves** or **Time Off**.

Top menu items:

1. **My Time Off**
2. **Overview**
3. **Approvals**
4. **Reporting**
5. **Configuration**
6. **Flight Tickets** optional
7. Company selector
8. User profile dropdown

---

## 4. User Roles

### Employee

Can:

- Check in and check out for self
- View own attendance
- View own leave balance
- Create own leave request
- Create own regularization request
- View own request status
- Cancel own draft or pending request if allowed

Cannot:

- Approve requests
- View all employee attendance
- Modify attendance machine setup
- Edit leave policy

### Manager

Can:

- View direct report attendance
- Approve or reject regularization requests for direct reports
- Approve or refuse leave requests for direct reports
- View team leave overview
- View overlap between team leaves
- Reassign pending work during leave approval

### HR Officer

Can:

- View all attendance
- Create and edit attendance records
- Approve regularization requests
- Configure leave types
- Configure leave approval rules
- Manage leave settings
- Import biometric attendance data
- Export attendance reports

### HR Manager / Admin

Can:

- Full access
- Configure biometric machines
- Clear downloaded device data
- Manage shifts and advanced features
- Override leave balance
- Approve final-level leave requests
- Access reporting

---

## 5. Attendance Dashboard Structure

## 5.1 Check In / Check Out Screen

### Route

`GET /attendance/check`

### Controller

`AttendanceCheckController@index`

### Screen Layout

Centered card page with grey background.

Card sections:

- Top banner area with soft gradient
- Employee avatar overlapping banner and body
- Employee name
- Current attendance state message
- Today work hours
- Large action button

### State 1: Not Checked In

Show:

- Employee name
- Text: `Want to check in?`
- Today's work hours: `00:00`
- Large orange/yellow button with entry icon
- Text below button: `Click to check in`

### State 2: Checked In

Show:

- Employee name
- Text: `Want to check out?`
- Today's work hours: calculated live
- Large orange/yellow button with exit icon
- Text below button: `Click to check out`

### Actions

`POST /attendance/check-in`

Creates an open attendance record.

`POST /attendance/check-out`

Closes the active attendance record.

### Rules

- One open attendance per employee at a time.
- Check out must be after check in.
- Work hours must be calculated from check in to check out.
- If employee forgets checkout, mark the record as incomplete.
- If shift is enabled, compare attendance with assigned shift.

---

## 5.2 Kiosk Mode

### Route

`GET /attendance/kiosk`

### Goal

Allow employees to mark attendance from a shared kiosk screen.

### Supported Methods

- Employee PIN
- ID card / barcode
- QR code
- Optional face recognition placeholder
- Optional biometric mapping

### Screens

1. Kiosk landing screen
2. Employee PIN input
3. Employee confirmation
4. Check in / check out action
5. Success screen

### DB Support

Use `employee_attendance_credentials` table to store:

- employee_id
- pin_hash
- card_number
- qr_token
- biometric_user_id

### Security Rules

- Never store plain PIN.
- Rate limit wrong PIN attempts.
- Log kiosk device IP and browser.
- Allow kiosk only from approved machine/location if configured.

---

## 5.3 Attendance Machine Configuration Screen

Screenshot page title example:

`Attendances / 192.168.2.64`

Top buttons:

- Edit
- Create
- Action
- Previous / Next
- Clear Data
- Download Data

Main form card fields:

- Machine IP
- Port No
- Working Address
- Company

### Routes

```text
GET    /attendance/machines
GET    /attendance/machines/create
POST   /attendance/machines
GET    /attendance/machines/{machine}
GET    /attendance/machines/{machine}/edit
PUT    /attendance/machines/{machine}
DELETE /attendance/machines/{machine}

POST /attendance/machines/{machine}/download-data
POST /attendance/machines/{machine}/clear-data
```

### Controller

`AttendanceMachineController`

### Form Fields

| Field | Type | Required |
|---|---|---|
| machine_ip | string | yes |
| port_no | integer | yes |
| working_address_id | FK | no |
| company_id | FK | yes |
| device_type | enum | no |
| status | enum | yes |
| last_sync_at | datetime | no |
| notes | text | no |

### Device Types

- ZKTeco
- Face detection
- Fingerprint
- RFID
- Manual import
- Other

### Actions

#### Download Data

- Connect to device
- Pull raw attendance logs
- Store raw logs first
- Convert logs into attendance records
- Show sync result

#### Clear Data

- Require admin permission
- Ask confirmation
- Clear device-side logs only after successful import
- Store audit log

### Service Classes

- `AttendanceMachineSyncService`
- `BiometricDeviceClient`
- `AttendanceLogParser`
- `AttendanceImportService`

---

## 5.4 Attendances List Screen

### Route

`GET /attendance/records`

### Screen Layout

Odoo-style list view:

Top:

- Page title: `Attendances`
- Create button
- Search bar
- Filters
- Group By
- Favorites
- Pagination
- List/Kanban toggle

Table columns:

- Checkbox
- Employee
- Check In
- Check Out
- Worked Hours
- Status
- Source
- Company
- Manager
- Action menu

### Filters

- Today
- This week
- This month
- Checked in
- Checked out
- Incomplete
- Late check in
- Early checkout
- Overtime
- Employee
- Department
- Company
- Source
- Date range

### Group By

- Employee
- Department
- Company
- Date
- Source
- Status

### Status Values

- open
- completed
- incomplete
- regularized
- cancelled

---

## 5.5 Attendance Regularization Request Screen

Screenshot page title example:

`Regularization Request / Ronnie`

Top buttons:

- Edit
- Create
- Action
- Approve
- Reject

Workflow ribbon:

- Draft
- Requested
- Approved

Main fields:

Left:

- Regularization Category
- Reason
- Employee

Right:

- From Date
- To Date

Bottom chatter:

- Send message
- Log note
- Schedule activity
- State change logs

### Routes

```text
GET    /attendance/regularizations
GET    /attendance/regularizations/create
POST   /attendance/regularizations
GET    /attendance/regularizations/{regularization}
GET    /attendance/regularizations/{regularization}/edit
PUT    /attendance/regularizations/{regularization}

POST /attendance/regularizations/{regularization}/submit
POST /attendance/regularizations/{regularization}/approve
POST /attendance/regularizations/{regularization}/reject
POST /attendance/regularizations/{regularization}/mark-draft
```

### Controller

`AttendanceRegularizationController`

### Workflow

```text
draft -> requested -> approved
draft -> requested -> rejected
rejected -> draft
```

### Regularization Categories

- Onsite work
- Client visit
- Work from home
- Missed check in
- Missed check out
- Official duty
- Travel
- Other

### Rules

- Employee can create regularization for self.
- Manager or HR can approve.
- Regularization date range cannot overlap an approved leave.
- On approval, attendance records must be created or adjusted.
- Log every workflow change.
- Prevent duplicate approved regularization for same employee and date range.

### Chatter Log

Every request must support:

- Comments
- Internal notes
- Followers
- Activities
- Attachments
- State transition log

---

## 6. Leaves / Time Off Dashboard Structure

## 6.1 Time Off Types List

Screenshot page title:

`Time Off Types`

Top:

- Create
- Import/download icon
- Search
- Filters
- Group By
- Favorites
- Pagination
- View switcher

Table columns:

- Display Name
- Approval

Rows shown:

- Paid Time Off
- Compensatory Days
- Sick Time Off
- Unpaid
- Parental Leaves
- Extra Hours

Approval shown:

- Approved by Time Off Officer

### Routes

```text
GET    /leaves/types
GET    /leaves/types/create
POST   /leaves/types
GET    /leaves/types/{type}
GET    /leaves/types/{type}/edit
PUT    /leaves/types/{type}
DELETE /leaves/types/{type}
```

### Controller

`LeaveTypeController`

### Fields

| Field | Type | Required |
|---|---|---|
| name | string | yes |
| code | string | yes |
| color | string | no |
| approval_policy | enum | yes |
| allocation_required | boolean | yes |
| paid | boolean | yes |
| allow_half_day | boolean | yes |
| allow_hourly | boolean | yes |
| carry_forward | boolean | no |
| max_carry_forward_days | decimal | no |
| negative_balance_allowed | boolean | no |
| active | boolean | yes |
| company_id | FK | yes |

### Approval Policy Values

- no_approval
- manager
- time_officer
- manager_and_time_officer
- hr_manager

---

## 6.2 Leave Settings Screen

Screenshot page title:

`Settings`

Left settings menu:

- General Settings
- Website
- Leaves
- Inventory
- Invoicing
- Payroll
- Project
- Timesheets
- Events
- Employees
- Recruitment
- Attendances
- Expenses

Main Leaves settings:

#### Leave Email Alias

Text:

- Allows creating leave request from email.
- Email subject can start with `LEAVE REQUEST`.
- Content can mention `Date From` and `Date To`.

Fields:

- Prefix
- Domain

#### Leaves Reminder

Fields:

- Enabled checkbox
- Days Before

#### Flight Ticket

Fields:

- Expense Account

#### Advanced Features

- Employee Shift
- Vacation Management

### Routes

```text
GET /leaves/settings
PUT /leaves/settings
```

### Controller

`LeaveSettingController`

### Settings Fields

- leave_email_alias_enabled
- leave_email_prefix
- leave_email_domain
- leave_reminder_enabled
- leave_reminder_days_before
- flight_ticket_enabled
- flight_ticket_expense_account_id
- employee_shift_enabled
- vacation_management_enabled
- company_id

---

## 6.3 My Time Off / Leave Request Form

Screenshot page title example:

`My Time Off / Mitchell Admin on Compensatory Days: 22.50 hours on 2022-02-07`

Top buttons:

- Edit
- Create
- Action
- Approve
- Refuse
- Mark as Draft

Workflow ribbon:

- To Approve
- Approved

Main fields:

Left:

- Time Off Type
- Dates
- Duration
- Description

Right:

- Remaining Legal Leaves
- Company

Tab:

- Pending Works

Pending Works table:

- Task
- Project
- Description
- Delete icon
- Add a line

Bottom:

- Send message
- Log note
- Schedule activity
- Followers
- Activities

### Routes

```text
GET    /leaves/requests
GET    /leaves/requests/create
POST   /leaves/requests
GET    /leaves/requests/{request}
GET    /leaves/requests/{request}/edit
PUT    /leaves/requests/{request}

POST /leaves/requests/{request}/submit
POST /leaves/requests/{request}/approve
POST /leaves/requests/{request}/refuse
POST /leaves/requests/{request}/mark-draft
POST /leaves/requests/{request}/cancel
```

### Controller

`LeaveRequestController`

### Workflow

```text
draft -> to_approve -> approved
draft -> to_approve -> refused
refused -> draft
approved -> cancelled
```

### Rules

- Employee can request leave for self.
- HR can create leave for any employee.
- Duration should be calculated from date range.
- Half-day and hourly leave depend on leave type.
- Leave cannot exceed available balance unless leave type allows negative balance.
- Leave cannot overlap with another approved leave.
- Leave cannot overlap with full attendance unless HR override is used.
- Pending work lines can be added before approval.
- On approval, leave balance must be deducted.
- On refusal, no balance is deducted.
- On cancellation of approved leave, balance must be restored.

---

## 6.4 Leave Overview

### Purpose

Show team or company leave calendar.

### Views

- Calendar view
- List view
- Department grouped view
- Employee grouped view

### Filters

- My team
- My department
- All employees
- Approved leaves
- Pending leaves
- Date range
- Leave type
- Company

### Features

- Show overlapping leaves.
- Show department staffing warning.
- Show public holiday overlaps.
- Show employees on vacation today.

---

## 6.5 Leave Approvals

### Purpose

Manager and HR approval queue.

### Route

`GET /leaves/approvals`

### Columns

- Employee
- Leave Type
- Date From
- Date To
- Duration
- Status
- Department
- Manager
- Pending Works
- Company
- Actions

### Bulk Actions

- Approve selected
- Refuse selected
- Export selected

---

## 6.6 Automatic Leave Request from Email

### Goal

Convert incoming email into leave request.

### Expected Email Format

Subject begins with:

`LEAVE REQUEST`

Email body should contain:

- Employee email
- Leave type
- Date From
- Date To
- Reason

### Service

`LeaveEmailRequestParserService`

### Rules

- Find employee by email.
- Find leave type by name or code.
- Create draft or submitted leave request.
- Attach original email body.
- Notify manager.
- Log parser errors.

---

## 6.7 Vacation Management

### Purpose

Manage employee vacation requests with better handover control.

### Features

- Leave notification
- Paid or unpaid vacation
- Overlap detection
- Pending work assignment
- Approval notes
- Task handover
- Optional replacement employee

### Tables

- `leave_pending_works`
- `leave_handover_assignments`

---

## 7. Database Structure

## 7.1 attendance_records

```php
Schema::create('attendance_records', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
    $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('shift_id')->nullable()->constrained('work_shifts')->nullOnDelete();

    $table->dateTime('check_in_at');
    $table->dateTime('check_out_at')->nullable();
    $table->decimal('worked_hours', 8, 2)->default(0);
    $table->decimal('overtime_hours', 8, 2)->default(0);

    $table->enum('source', ['web', 'kiosk', 'biometric', 'manual', 'regularization'])->default('web');
    $table->enum('status', ['open', 'completed', 'incomplete', 'regularized', 'cancelled'])->default('open');

    $table->string('machine_ip')->nullable();
    $table->string('device_user_id')->nullable();
    $table->string('location')->nullable();
    $table->string('ip_address')->nullable();
    $table->text('notes')->nullable();

    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

    $table->timestamps();
    $table->softDeletes();

    $table->index(['employee_id', 'check_in_at']);
    $table->index(['company_id', 'check_in_at']);
    $table->index(['status', 'source']);
});
```

---

## 7.2 attendance_machines

```php
Schema::create('attendance_machines', function (Blueprint $table) {
    $table->id();
    $table->string('machine_ip')->unique();
    $table->integer('port_no')->default(4370);
    $table->foreignId('working_address_id')->nullable()->constrained('work_locations')->nullOnDelete();
    $table->foreignId('company_id')->constrained()->cascadeOnDelete();

    $table->enum('device_type', ['zkteco', 'face', 'fingerprint', 'rfid', 'manual_import', 'other'])->default('zkteco');
    $table->enum('status', ['active', 'inactive', 'error'])->default('active');

    $table->dateTime('last_sync_at')->nullable();
    $table->string('last_sync_status')->nullable();
    $table->text('notes')->nullable();

    $table->timestamps();
    $table->softDeletes();
});
```

---

## 7.3 biometric_attendance_logs

```php
Schema::create('biometric_attendance_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('attendance_machine_id')->constrained()->cascadeOnDelete();
    $table->string('device_user_id');
    $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();

    $table->dateTime('punch_time');
    $table->enum('punch_type', ['in', 'out', 'unknown'])->default('unknown');
    $table->json('raw_payload')->nullable();

    $table->boolean('processed')->default(false);
    $table->foreignId('attendance_record_id')->nullable()->constrained()->nullOnDelete();

    $table->timestamps();

    $table->unique(['attendance_machine_id', 'device_user_id', 'punch_time']);
});
```

---

## 7.4 attendance_regularization_requests

```php
Schema::create('attendance_regularization_requests', function (Blueprint $table) {
    $table->id();
    $table->string('request_no')->unique();
    $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
    $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();

    $table->foreignId('regularization_category_id')->nullable()->constrained()->nullOnDelete();
    $table->text('reason')->nullable();

    $table->dateTime('from_date');
    $table->dateTime('to_date');

    $table->enum('status', ['draft', 'requested', 'approved', 'rejected'])->default('draft');

    $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
    $table->dateTime('approved_at')->nullable();
    $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
    $table->dateTime('rejected_at')->nullable();
    $table->text('rejection_reason')->nullable();

    $table->timestamps();
    $table->softDeletes();
});
```

---

## 7.5 regularization_categories

```php
Schema::create('regularization_categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('code')->unique();
    $table->boolean('active')->default(true);
    $table->timestamps();
});
```

---

## 7.6 leave_types

```php
Schema::create('leave_types', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();

    $table->string('name');
    $table->string('code')->unique();
    $table->string('color')->nullable();

    $table->enum('approval_policy', [
        'no_approval',
        'manager',
        'time_officer',
        'manager_and_time_officer',
        'hr_manager'
    ])->default('time_officer');

    $table->boolean('allocation_required')->default(true);
    $table->boolean('paid')->default(true);
    $table->boolean('allow_half_day')->default(true);
    $table->boolean('allow_hourly')->default(false);
    $table->boolean('carry_forward')->default(false);
    $table->decimal('max_carry_forward_days', 8, 2)->nullable();
    $table->boolean('negative_balance_allowed')->default(false);
    $table->boolean('active')->default(true);

    $table->timestamps();
    $table->softDeletes();
});
```

---

## 7.7 leave_allocations

```php
Schema::create('leave_allocations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
    $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();
    $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();

    $table->decimal('allocated_days', 8, 2)->default(0);
    $table->decimal('used_days', 8, 2)->default(0);
    $table->decimal('remaining_days', 8, 2)->default(0);

    $table->date('valid_from')->nullable();
    $table->date('valid_to')->nullable();

    $table->enum('status', ['draft', 'approved', 'expired'])->default('approved');

    $table->timestamps();

    $table->unique(['employee_id', 'leave_type_id', 'valid_from', 'valid_to'], 'leave_allocation_unique');
});
```

---

## 7.8 leave_requests

```php
Schema::create('leave_requests', function (Blueprint $table) {
    $table->id();
    $table->string('request_no')->unique();

    $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
    $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();
    $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();

    $table->dateTime('date_from');
    $table->dateTime('date_to');
    $table->decimal('duration_days', 8, 2)->default(0);
    $table->decimal('duration_hours', 8, 2)->default(0);

    $table->text('description')->nullable();

    $table->decimal('remaining_legal_leaves', 8, 2)->nullable();

    $table->enum('status', ['draft', 'to_approve', 'approved', 'refused', 'cancelled'])->default('draft');

    $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
    $table->dateTime('approved_at')->nullable();

    $table->foreignId('refused_by')->nullable()->constrained('users')->nullOnDelete();
    $table->dateTime('refused_at')->nullable();
    $table->text('refusal_reason')->nullable();

    $table->timestamps();
    $table->softDeletes();

    $table->index(['employee_id', 'date_from', 'date_to']);
    $table->index(['status', 'company_id']);
});
```

---

## 7.9 leave_pending_works

```php
Schema::create('leave_pending_works', function (Blueprint $table) {
    $table->id();
    $table->foreignId('leave_request_id')->constrained()->cascadeOnDelete();

    $table->string('task');
    $table->string('project')->nullable();
    $table->text('description')->nullable();
    $table->foreignId('assigned_to_employee_id')->nullable()->constrained('employees')->nullOnDelete();

    $table->timestamps();
});
```

---

## 7.10 leave_settings

```php
Schema::create('leave_settings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();

    $table->boolean('leave_email_alias_enabled')->default(false);
    $table->string('leave_email_prefix')->nullable();
    $table->string('leave_email_domain')->nullable();

    $table->boolean('leave_reminder_enabled')->default(false);
    $table->integer('leave_reminder_days_before')->default(3);

    $table->boolean('flight_ticket_enabled')->default(false);
    $table->foreignId('flight_ticket_expense_account_id')->nullable()->constrained('accounts')->nullOnDelete();

    $table->boolean('employee_shift_enabled')->default(false);
    $table->boolean('vacation_management_enabled')->default(false);

    $table->timestamps();
});
```

---

## 7.11 work_shifts

```php
Schema::create('work_shifts', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->time('start_time');
    $table->time('end_time');
    $table->decimal('standard_hours', 5, 2)->default(8);
    $table->boolean('active')->default(true);
    $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
    $table->timestamps();
});
```

---

## 7.12 employee_shift_assignments

```php
Schema::create('employee_shift_assignments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
    $table->foreignId('work_shift_id')->constrained()->cascadeOnDelete();
    $table->date('start_date');
    $table->date('end_date')->nullable();
    $table->boolean('active')->default(true);
    $table->timestamps();
});
```

---

## 7.13 chatter_messages

Use a shared HRMS chatter table.

```php
Schema::create('chatter_messages', function (Blueprint $table) {
    $table->id();
    $table->morphs('messageable');
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->enum('type', ['message', 'note', 'activity', 'system'])->default('message');
    $table->text('body');
    $table->json('meta')->nullable();
    $table->timestamps();
});
```

---

## 8. Laravel Folder Structure

```text
app/
  Models/
    AttendanceRecord.php
    AttendanceMachine.php
    BiometricAttendanceLog.php
    AttendanceRegularizationRequest.php
    RegularizationCategory.php
    LeaveType.php
    LeaveAllocation.php
    LeaveRequest.php
    LeavePendingWork.php
    LeaveSetting.php
    WorkShift.php
    EmployeeShiftAssignment.php

  Http/
    Controllers/
      Attendance/
        AttendanceCheckController.php
        KioskAttendanceController.php
        AttendanceRecordController.php
        AttendanceMachineController.php
        AttendanceRegularizationController.php
        AttendanceReportController.php
      Leave/
        LeaveTypeController.php
        LeaveRequestController.php
        LeaveApprovalController.php
        LeaveOverviewController.php
        LeaveSettingController.php
        LeaveReportController.php

    Requests/
      Attendance/
        StoreAttendanceMachineRequest.php
        StoreAttendanceRegularizationRequest.php
        StoreAttendanceRecordRequest.php
      Leave/
        StoreLeaveTypeRequest.php
        StoreLeaveRequestRequest.php
        UpdateLeaveSettingRequest.php

  Services/
    Attendance/
      AttendanceCheckService.php
      AttendanceCalculationService.php
      AttendanceMachineSyncService.php
      BiometricDeviceClient.php
      AttendanceImportService.php
      AttendanceRegularizationService.php
    Leave/
      LeaveDurationService.php
      LeaveBalanceService.php
      LeaveApprovalService.php
      LeaveEmailRequestParserService.php
      LeaveReminderService.php
      VacationManagementService.php

  Policies/
    AttendanceRecordPolicy.php
    AttendanceMachinePolicy.php
    AttendanceRegularizationPolicy.php
    LeaveTypePolicy.php
    LeaveRequestPolicy.php
    LeaveSettingPolicy.php

  Notifications/
    AttendanceRegularizationSubmitted.php
    AttendanceRegularizationApproved.php
    AttendanceRegularizationRejected.php
    LeaveRequestSubmitted.php
    LeaveRequestApproved.php
    LeaveRequestRefused.php
    LeaveReminderNotification.php

resources/
  views/
    layouts/
      hrms.blade.php

    attendance/
      check.blade.php
      kiosk/
        index.blade.php
        pin.blade.php
        success.blade.php
      records/
        index.blade.php
        show.blade.php
        form.blade.php
      machines/
        index.blade.php
        show.blade.php
        form.blade.php
      regularizations/
        index.blade.php
        show.blade.php
        form.blade.php
      reports/
        index.blade.php

    leaves/
      types/
        index.blade.php
        form.blade.php
        show.blade.php
      requests/
        index.blade.php
        show.blade.php
        form.blade.php
      approvals/
        index.blade.php
      overview/
        index.blade.php
      settings/
        index.blade.php
      reports/
        index.blade.php

    components/
      hrms-topbar.blade.php
      list-toolbar.blade.php
      status-ribbon.blade.php
      chatter.blade.php
      form-card.blade.php
      employee-avatar.blade.php
      smart-button.blade.php
```

---

## 9. Important Services

## 9.1 AttendanceCheckService

Responsibilities:

- Check if employee has an open attendance.
- Create check in record.
- Close check out record.
- Calculate worked hours.
- Detect late check in.
- Detect early checkout.
- Attach source as `web` or `kiosk`.

Methods:

```php
checkIn(Employee $employee, array $context = []): AttendanceRecord
checkOut(Employee $employee, array $context = []): AttendanceRecord
getTodayWorkedHours(Employee $employee): float
getOpenAttendance(Employee $employee): ?AttendanceRecord
```

---

## 9.2 AttendanceMachineSyncService

Responsibilities:

- Connect to configured machine.
- Download raw punch logs.
- Save raw logs.
- Map biometric user id to employee.
- Convert punches to attendance records.
- Mark logs as processed.
- Clear data if requested.

Methods:

```php
downloadData(AttendanceMachine $machine): SyncResult
clearData(AttendanceMachine $machine): ClearResult
processLogs(AttendanceMachine $machine): ProcessingResult
```

---

## 9.3 AttendanceRegularizationService

Responsibilities:

- Submit regularization request.
- Approve request.
- Reject request.
- Create or update attendance record after approval.
- Log workflow events.

Methods:

```php
submit(AttendanceRegularizationRequest $request): void
approve(AttendanceRegularizationRequest $request, User $approver): AttendanceRecord
reject(AttendanceRegularizationRequest $request, User $user, string $reason = null): void
markDraft(AttendanceRegularizationRequest $request): void
```

---

## 9.4 LeaveDurationService

Responsibilities:

- Calculate duration in days and hours.
- Respect weekends and holidays.
- Respect employee shift.
- Support half-day and hourly leave.
- Support company timezone.

Methods:

```php
calculate(Employee $employee, Carbon $from, Carbon $to, LeaveType $type): LeaveDuration
```

---

## 9.5 LeaveBalanceService

Responsibilities:

- Get remaining leave balance.
- Deduct balance after approval.
- Restore balance after cancellation.
- Validate negative leave policy.

Methods:

```php
getBalance(Employee $employee, LeaveType $type): float
canApply(Employee $employee, LeaveType $type, float $days): bool
deduct(LeaveRequest $request): void
restore(LeaveRequest $request): void
```

---

## 9.6 LeaveApprovalService

Responsibilities:

- Submit leave request.
- Approve request based on approval policy.
- Refuse request.
- Mark request as draft.
- Send notifications.
- Create chatter log.

Methods:

```php
submit(LeaveRequest $request): void
approve(LeaveRequest $request, User $approver): void
refuse(LeaveRequest $request, User $user, string $reason = null): void
markDraft(LeaveRequest $request): void
cancel(LeaveRequest $request): void
```

---

## 10. Validation Rules

## Attendance Check In

- Employee must be active.
- Employee cannot check in twice without checkout.
- Date must be in company timezone.
- IP/location may be stored.

## Attendance Check Out

- Employee must have open attendance.
- Checkout time must be after check in.
- Worked hours must be recalculated.

## Regularization

- From date required.
- To date required.
- To date must be after from date.
- Reason required.
- Employee required.
- Cannot overlap approved leave.
- Cannot duplicate approved regularization.

## Leave Request

- Employee required.
- Leave type required.
- Date from required.
- Date to required.
- Date to must be after date from.
- Leave type must be active.
- Duration must be greater than zero.
- Balance must be enough unless negative balance is allowed.
- Cannot overlap approved leave.
- Pending work lines optional.

## Leave Type

- Name required.
- Code required and unique.
- Approval policy required.
- Company required if multi-company mode is enabled.

---

## 11. Reporting

## Attendance Reports

Create report page:

`/attendance/reports`

Cards:

- Present today
- Absent today
- Late check in
- Early checkout
- Overtime hours
- Missing checkout
- Attendance regularization pending

Tables:

- Daily attendance report
- Monthly attendance summary
- Employee-wise attendance
- Department-wise attendance
- Biometric sync report

Export:

- Excel
- CSV
- PDF

## Leave Reports

Create report page:

`/leaves/reports`

Cards:

- Employees on leave today
- Pending approvals
- Approved leaves this month
- Refused leaves
- Leave balance summary
- Overlapping leave warnings

Tables:

- Leave balance report
- Leave request report
- Department leave report
- Leave type usage report
- Vacation handover report

Export:

- Excel
- CSV
- PDF

---

## 12. UI Components

## 12.1 Top Bar

Use purple top bar.

Left:

- App icon
- App title

Center:

- Module menu items

Right:

- Messages count
- Notifications
- Calendar icon
- Company name
- User avatar
- User name

## 12.2 List Toolbar

Must include:

- Search input
- Filters button
- Group By button
- Favorites button
- Pagination
- Previous/Next
- List/Kanban toggle

## 12.3 Form Header

Must include:

- Breadcrumb title
- Edit
- Create
- Save
- Discard
- Action dropdown
- Print if needed
- Previous/Next controls
- Workflow status ribbon on right

## 12.4 Chatter

Must appear in request forms:

- Regularization Request
- Leave Request

Chatter options:

- Send message
- Log note
- Schedule activity
- Attachment count
- Follow button
- Follower count
- Activity log

---

## 13. API Endpoints

Optional API endpoints for mobile or frontend use.

```text
GET    /api/attendance/today
POST   /api/attendance/check-in
POST   /api/attendance/check-out
GET    /api/attendance/records
POST   /api/attendance/regularizations
POST   /api/attendance/regularizations/{id}/submit
POST   /api/attendance/regularizations/{id}/approve
POST   /api/attendance/regularizations/{id}/reject

GET    /api/leaves/types
GET    /api/leaves/balance
GET    /api/leaves/requests
POST   /api/leaves/requests
POST   /api/leaves/requests/{id}/submit
POST   /api/leaves/requests/{id}/approve
POST   /api/leaves/requests/{id}/refuse
```

---

## 14. Seeder Data

Create seeders for:

### Leave Types

- Paid Time Off
- Compensatory Days
- Sick Time Off
- Unpaid
- Parental Leaves
- Extra Hours

### Regularization Categories

- Onsite work
- Client visit
- Work from home
- Missed check in
- Missed check out
- Official duty
- Travel
- Other

### Work Shifts

- Standard 40 hours/week
- Morning shift
- Evening shift
- Night shift

### Attendance Machine

- IP: 192.168.2.64
- Port: 124
- Company: My Company (San Francisco)

---

## 15. Background Jobs

Create queued jobs:

```text
SyncAttendanceMachineJob
ProcessBiometricLogsJob
SendLeaveReminderJob
MarkMissingCheckoutJob
GenerateMonthlyAttendanceSummaryJob
GenerateLeaveBalanceSnapshotJob
```

### Scheduler

```php
$schedule->job(new SyncAttendanceMachineJob)->everyThirtyMinutes();
$schedule->job(new SendLeaveReminderJob)->dailyAt('09:00');
$schedule->job(new MarkMissingCheckoutJob)->dailyAt('23:55');
```

---

## 16. Notifications

Use mail, database, and optional in-app notification.

### Attendance

- Regularization submitted
- Regularization approved
- Regularization rejected
- Missing checkout reminder
- Biometric sync failed

### Leaves

- Leave request submitted
- Leave approved
- Leave refused
- Leave reminder before start date
- Leave overlap warning
- Pending approval reminder

---

## 17. Permissions

Create permission names:

```text
attendance.view_own
attendance.view_team
attendance.view_all
attendance.create
attendance.edit
attendance.delete
attendance.check_in_out
attendance.manage_machines
attendance.download_machine_data
attendance.clear_machine_data

regularization.view_own
regularization.view_team
regularization.view_all
regularization.create
regularization.approve
regularization.reject

leave_type.view
leave_type.create
leave_type.edit
leave_type.delete

leave_request.view_own
leave_request.view_team
leave_request.view_all
leave_request.create
leave_request.approve
leave_request.refuse
leave_request.cancel

leave_settings.manage
leave_reports.view
attendance_reports.view
```

---

## 18. Testing Plan

## Attendance Tests

- Employee can check in.
- Employee cannot check in twice.
- Employee can check out after check in.
- Worked hours calculated correctly.
- Kiosk PIN creates attendance.
- Biometric logs are imported once only.
- Regularization creates attendance after approval.
- Rejected regularization does not affect attendance.

## Leave Tests

- Employee can create leave request.
- Leave duration calculates correctly.
- Leave balance reduces after approval.
- Leave balance restores after cancellation.
- Employee cannot apply more leave than balance unless allowed.
- Leave overlap is blocked.
- Manager can approve team leave.
- Employee cannot approve own leave.
- Leave reminder job sends alerts.

---

## 19. Implementation Phases

## Phase 1 — Foundation

- Create migrations
- Create models and relationships
- Create permissions
- Create seeders
- Create base layouts

## Phase 2 — Attendance

- Check in / check out screen
- Attendance records list
- Manual attendance form
- Attendance calculation service

## Phase 3 — Kiosk and Machines

- Kiosk PIN mode
- Attendance machine CRUD
- Raw biometric logs
- Download data and clear data buttons

## Phase 4 — Regularization

- Regularization CRUD
- Submit, approve, reject workflow
- Chatter logs
- Attendance adjustment after approval

## Phase 5 — Leaves

- Leave type CRUD
- Leave settings
- Leave request form
- Pending work lines
- Leave balance calculation

## Phase 6 — Approvals and Reports

- Leave approval queue
- Attendance reports
- Leave reports
- Export to Excel, CSV, PDF

## Phase 7 — Polish

- Search, filters, group by
- Responsive UI
- Notifications
- Audit logs
- Final testing

---

## 20. Model Relationships

### Employee

```php
public function attendances()
{
    return $this->hasMany(AttendanceRecord::class);
}

public function leaveRequests()
{
    return $this->hasMany(LeaveRequest::class);
}

public function leaveAllocations()
{
    return $this->hasMany(LeaveAllocation::class);
}

public function shiftAssignments()
{
    return $this->hasMany(EmployeeShiftAssignment::class);
}
```

### AttendanceRecord

```php
public function employee()
{
    return $this->belongsTo(Employee::class);
}

public function shift()
{
    return $this->belongsTo(WorkShift::class);
}
```

### LeaveRequest

```php
public function employee()
{
    return $this->belongsTo(Employee::class);
}

public function type()
{
    return $this->belongsTo(LeaveType::class, 'leave_type_id');
}

public function pendingWorks()
{
    return $this->hasMany(LeavePendingWork::class);
}
```

---

## 21. Notes for Developer Agents

- Keep Attendance and Leaves in separate namespaces, but allow integration.
- Reuse the Employee DB module for employee, department, manager, company, and work location data.
- Reuse HR Administration module for roles, access levels, departments, and employee hierarchy.
- Use service classes for business logic. Do not place workflow logic inside controllers.
- Use policies for all access checks.
- Every approval or rejection must create a chatter system log.
- Do not delete approved attendance or leave records directly. Use cancellation status.
- Use soft deletes for configuration and request tables.
- Use company timezone for all attendance and leave calculations.
- Keep UI close to the supplied screenshots.
