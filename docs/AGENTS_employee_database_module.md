# AGENTS.md — Employee Database Module Clone in Laravel

## 0. Purpose

Build a Laravel-based Employee Database module inspired by Horilla / OpenHRMS Employee DB screens.
The goal is not to copy the original product directly. The goal is to build a similar HRMS employee master module with:

- Employee directory in card, list, and activity-style views.
- Employee profile master page.
- Work information, private information, and HR settings tabs.
- Employee documents with expiry alerts.
- Department filtering.
- Organization chart.
- Timesheet list from employee profile.
- Smart buttons for contracts, documents, payslips, timesheets, loans, time off, and related records.
- Clean Laravel code, reusable components, and strong database design.

This file gives work instructions for coding agents.

---

## 1. Product Scope

### Module Name

Employee Database Management

### Primary Users

1. Super Admin
2. HR Manager
3. Department Manager
4. Employee
5. Auditor / Read-only user

### Main Menu

Top navigation should follow this structure:

- Employees
- Document Templates
- Departments
- Loans & Advances
- Reporting
- Configuration

For this phase, only the Employee DB part must be fully built. Other menu items may show placeholder pages or connect later.

---

## 2. Visual Layout Target

### 2.1 Global Layout

Use a purple header like the screenshot.

Header structure:

- Left app icon / menu icon.
- Module title: `Employees`.
- Top menu links.
- Right icons:
  - Chat badge.
  - Notification bell.
  - Activity badge.
  - Logged-in user image and name.

Recommended style values:

```css
--hrms-purple: #7e57a3;
--hrms-purple-dark: #6d4894;
--hrms-border: #d9d9d9;
--hrms-muted-bg: #f7f7f7;
--hrms-link: #6d4894;
```

Use Bootstrap 5, Blade components, Alpine.js, or Livewire where useful.

---

## 3. Dashboard / Screen Structure

## 3.1 Employee Directory Screen

Route:

```text
GET /employees
```

Controller:

```text
EmployeeController@index
```

View:

```text
resources/views/hr/employees/index.blade.php
```

### Page layout

Top page area:

- Page title: `Employees`
- Left button: `Create`
- Right search bar.
- Toolbar links:
  - Filters
  - Group By
  - Favorites
- Pagination: `1-24 / 24`
- View switch icons:
  - Card grid
  - List table
  - Activity / clock view

Left sidebar:

```text
DEPARTMENT
- All
- Administration
- Management
- Professional Services
- Research & Development
- Sales
```

Each item should show employee count.

Main area:

Employee cards in grid.

### Employee card fields

Each card should show:

- Employee photo.
- Name.
- Job position.
- Department and tags.
- Work email.
- Work phone.
- Online status dot.
- Small clock/activity icon.
- Optional chat icon if message thread exists.

### Directory filters

Support:

- Search by name, email, phone, job title, department.
- Department filter.
- Status filter: active, archived, online, offline.
- Employee type: employee, trainee, consultant, contractor.
- Manager filter.
- Company filter.

### Directory views

Implement three views:

```text
/employees?view=kanban
/employees?view=list
/employees?view=activity
```

Default: `kanban`.

### Bulk actions

List view must support checkbox selection.

Bulk actions:

- Archive.
- Unarchive.
- Export CSV.
- Delete, only for Super Admin.
- Assign department.
- Assign manager.

---

## 3.2 Employee Create Screen

Route:

```text
GET /employees/create
POST /employees
```

Controller:

```text
EmployeeController@create
EmployeeController@store
```

View:

```text
resources/views/hr/employees/create.blade.php
```

### Form tabs

Use three main tabs:

1. Work Information
2. Private Information
3. HR Settings

### Header fields

Top section must include:

- Employee photo upload.
- First name.
- Last name.
- Display name.
- Job position.
- Department.
- Manager.
- Coach.
- Work mobile.
- Work phone.
- Work email.

### Save actions

Buttons:

- Save
- Discard

After save, redirect to employee profile.

---

## 3.3 Employee Profile Screen

Route:

```text
GET /employees/{employee}
```

Controller:

```text
EmployeeController@show
```

View:

```text
resources/views/hr/employees/show.blade.php
```

### Breadcrumb

```text
Employees / Abigail Peterson
```

### Top actions

- Edit
- Create
- Print
- Action dropdown
- Previous / Next employee navigation

### Smart button row

Show a horizontal smart button bar above employee details.

Buttons:

1. Connection status
   - `Connected` or `Not Connected`
   - Status dot: green, yellow, grey.

2. Contracts
   - Count active contracts.
   - Link to employee contract list.

3. Time Off
   - Show balance: `0/0 Days`.
   - Link to time off screen.

4. Documents
   - Count documents.
   - Link to employee document list.

5. Payslips
   - Count payslips.
   - Link to payroll module.

6. Timesheets
   - Link to employee timesheet list.

7. Loans
   - Count loans.
   - Link to loan module.

8. More dropdown
   - Attendance
   - Appraisals
   - Assets
   - Expenses
   - Lifecycle History

### Profile body

Left content:

- Employee name.
- Job title.
- Work Mobile.
- Work Phone.
- Work Email.

Right content:

- Department.
- Manager.
- Coach.
- Employee photo.

### Profile tabs

Tab 1: Work Information

Sections:

- Location
  - Work Address
  - Work Location
- Approvers
  - Time Off approver
  - Expense approver
- Schedule
  - Working Hours
  - Timezone

Tab 2: Private Information

Sections:

- Contact
  - Private address
  - Private email
  - Private phone
  - Emergency contact
  - Emergency phone
- Personal
  - Gender
  - Date of birth
  - Marital status
  - Nationality
  - Identification number
  - Passport number
- Education
  - Certificate level
  - Field of study
  - School
- Family
  - Spouse name
  - Children count

Tab 3: HR Settings

Sections:

- Status
  - Employee type
  - Employment status
  - Joining date
  - Confirmation date
  - Exit date
- Access
  - User account linked or not.
  - Employee role.
  - Portal access.
- Payroll Setup
  - Bank account.
  - Payroll structure.
  - Salary payment method.
- Tracking
  - Badge ID.
  - PIN code.
  - Attendance device ID.

### Right panel: Organization Chart

Show direct hierarchy.

Example:

```text
Mitchell Admin
Chief Executive Officer
  Abigail Peterson
  Consultant
    Anita Oliver
    Experienced Developer
    Audrey Peterson
    Consultant
```

Rules:

- Current employee must be highlighted.
- Show manager above.
- Show direct reports below.
- Show count badge for child employees.
- Add route: `/employees/{employee}/org-chart` for larger tree view.

---

## 3.4 Employee Edit Screen

Route:

```text
GET /employees/{employee}/edit
PUT /employees/{employee}
```

Controller:

```text
EmployeeController@edit
EmployeeController@update
```

View:

```text
resources/views/hr/employees/edit.blade.php
```

Use the same fields as Create.

Rules:

- Only HR Manager and Super Admin can edit all fields.
- Department Manager can edit limited work fields of team members.
- Employee can edit limited private info only.
- HR Settings tab must be hidden from normal Employee role.

---

## 3.5 Employee Documents List

Route:

```text
GET /employees/{employee}/documents
```

Controller:

```text
EmployeeDocumentController@index
```

View:

```text
resources/views/hr/employee-documents/index.blade.php
```

### List columns

- Checkbox
- Document Number
- Employee
- Document Type
- Issue Date
- Expiry Date
- Notification Type
- Days
- Status
- Attachment
- Action

### Toolbar

- Create
- Import / download icon
- Search
- Filters
- Group By
- Favorites

### Document status

Use status values:

- active
- expiring_soon
- expired
- archived

---

## 3.6 Employee Document Create Screen

Route:

```text
GET /employees/{employee}/documents/create
POST /employees/{employee}/documents
```

Controller:

```text
EmployeeDocumentController@create
EmployeeDocumentController@store
```

View:

```text
resources/views/hr/employee-documents/create.blade.php
```

### Breadcrumb

```text
Employees / Abigail Peterson / Documents / New
```

### Buttons

- Save
- Discard

### Form fields

Left column:

- Document Number
- Employee
- Document Type
- Attachment

Right column:

- Issue Date
- Expiry Date
- Notification Type
- Days

Tab:

- Description

### Notification type options

- none
- before_expiry
- after_issue
- custom_date

### Validation

- Employee is required.
- Document type is required.
- Attachment is optional at first, but required for final approval.
- Expiry date must be after issue date.
- Days must be zero or positive.
- Document number must be unique per company.

---

## 3.7 Employee Timesheets Screen

Route:

```text
GET /employees/{employee}/timesheets
```

Controller:

```text
EmployeeTimesheetController@index
```

View:

```text
resources/views/hr/employee-timesheets/index.blade.php
```

### Breadcrumb

```text
Employees / Abigail Peterson / Timesheets
```

### Toolbar

- Create
- Download / import icon
- Search box
- Active filter chips:
  - Employee: Abigail Peterson
  - Date: February 2022
- Filters
- Group By
- Favorites
- Pagination

### Table columns

- Checkbox
- Date
- Project
- Task
- Description
- Hours Spent
- Action

### Footer

Show total hours.

Example:

```text
05:00
```

### Timesheet create form

Fields:

- Employee
- Date
- Project
- Task
- Description
- Hours spent
- Status: draft, submitted, approved, rejected

---

## 3.8 Departments Screen

Route:

```text
GET /departments
```

Controller:

```text
DepartmentController@index
```

View:

```text
resources/views/hr/departments/index.blade.php
```

### Fields

- Name
- Code
- Parent department
- Manager
- Company
- Employee count
- Active status

### Department card stats

- Total employees
- Time off requests
- Absent today

---

## 4. Laravel Folder Structure

Use this structure:

```text
app/
  Models/
    Company.php
    Department.php
    JobPosition.php
    Employee.php
    EmployeePrivateInfo.php
    EmployeeHrSetting.php
    EmployeeDocument.php
    EmployeeDocumentType.php
    EmployeeLifecycleLog.php
    EmployeeTimesheet.php
    WorkLocation.php
    WorkSchedule.php
  Http/
    Controllers/
      Hr/
        EmployeeController.php
        EmployeeDocumentController.php
        EmployeeTimesheetController.php
        DepartmentController.php
        OrganizationChartController.php
    Requests/
      EmployeeStoreRequest.php
      EmployeeUpdateRequest.php
      EmployeeDocumentStoreRequest.php
      EmployeeTimesheetStoreRequest.php
  Policies/
    EmployeePolicy.php
    EmployeeDocumentPolicy.php
    DepartmentPolicy.php
  Services/
    Hr/
      EmployeeCodeService.php
      EmployeeSearchService.php
      OrganizationChartService.php
      EmployeeDocumentAlertService.php
      EmployeeLifecycleService.php
  Enums/
    EmployeeStatus.php
    EmployeeType.php
    EmploymentStatus.php
    DocumentNotificationType.php
    TimesheetStatus.php
  Observers/
    EmployeeObserver.php
    EmployeeDocumentObserver.php
resources/
  views/
    layouts/hrms.blade.php
    components/hr/
      top-nav.blade.php
      employee-card.blade.php
      smart-button.blade.php
      employee-tabs.blade.php
      org-chart-node.blade.php
    hr/
      employees/
        index.blade.php
        create.blade.php
        edit.blade.php
        show.blade.php
        _form.blade.php
        _profile_header.blade.php
        _smart_buttons.blade.php
        _work_information.blade.php
        _private_information.blade.php
        _hr_settings.blade.php
      employee-documents/
        index.blade.php
        create.blade.php
        edit.blade.php
        show.blade.php
      employee-timesheets/
        index.blade.php
        create.blade.php
      departments/
        index.blade.php
        create.blade.php
        edit.blade.php
routes/
  web.php
  hr.php
```

---

## 5. Database Structure

Use MySQL or PostgreSQL. Use Laravel migrations.

All tables must include:

```text
id
created_at
updated_at
```

Use soft deletes for employee records and employee documents.

---

## 5.1 companies

```text
id bigInt PK
name varchar(150)
code varchar(30) unique nullable
email varchar(150) nullable
phone varchar(50) nullable
website varchar(150) nullable
address_line_1 varchar(255) nullable
address_line_2 varchar(255) nullable
city varchar(100) nullable
state varchar(100) nullable
country varchar(100) nullable
postal_code varchar(30) nullable
timezone varchar(100) default 'UTC'
is_active boolean default true
created_at timestamp
updated_at timestamp
```

---

## 5.2 departments

```text
id bigInt PK
company_id FK companies.id
parent_id FK departments.id nullable
manager_employee_id FK employees.id nullable
name varchar(150)
code varchar(50) nullable
description text nullable
sort_order int default 0
is_active boolean default true
created_at timestamp
updated_at timestamp
```

Indexes:

```text
company_id
parent_id
manager_employee_id
name
is_active
```

---

## 5.3 job_positions

```text
id bigInt PK
company_id FK companies.id
name varchar(150)
code varchar(50) nullable
department_id FK departments.id nullable
description text nullable
is_active boolean default true
created_at timestamp
updated_at timestamp
```

Examples:

- Consultant
- Experienced Developer
- Chief Executive Officer
- HR Manager
- Odoo Developer

---

## 5.4 work_locations

```text
id bigInt PK
company_id FK companies.id
name varchar(150)
address_line_1 varchar(255) nullable
address_line_2 varchar(255) nullable
city varchar(100) nullable
state varchar(100) nullable
country varchar(100) nullable
postal_code varchar(30) nullable
floor varchar(80) nullable
room varchar(80) nullable
is_active boolean default true
created_at timestamp
updated_at timestamp
```

Example:

```text
Building 1, Second Floor
```

---

## 5.5 work_schedules

```text
id bigInt PK
company_id FK companies.id
name varchar(150)
weekly_hours decimal(5,2) default 40.00
timezone varchar(100) default 'UTC'
starts_at time nullable
ends_at time nullable
is_default boolean default false
is_active boolean default true
created_at timestamp
updated_at timestamp
```

Example:

```text
Standard 40 Hours / Week
```

---

## 5.6 employees

This is the master table.

```text
id bigInt PK
company_id FK companies.id
user_id FK users.id nullable
employee_code varchar(50) unique
first_name varchar(100)
middle_name varchar(100) nullable
last_name varchar(100) nullable
display_name varchar(180)
photo_path varchar(255) nullable
work_email varchar(180) nullable unique
work_phone varchar(50) nullable
work_mobile varchar(50) nullable
department_id FK departments.id nullable
job_position_id FK job_positions.id nullable
manager_id FK employees.id nullable
coach_id FK employees.id nullable
work_location_id FK work_locations.id nullable
work_schedule_id FK work_schedules.id nullable
timezone varchar(100) nullable
employee_type enum('employee','trainee','consultant','contractor') default 'employee'
employment_status enum('draft','active','on_leave','notice_period','exited','archived') default 'draft'
connection_status enum('connected','not_connected','invited') default 'not_connected'
hire_date date nullable
confirmation_date date nullable
exit_date date nullable
notes text nullable
is_active boolean default true
created_by FK users.id nullable
updated_by FK users.id nullable
deleted_at timestamp nullable
created_at timestamp
updated_at timestamp
```

Indexes:

```text
company_id
employee_code
work_email
department_id
job_position_id
manager_id
coach_id
employment_status
employee_type
connection_status
is_active
```

Rules:

- `display_name` is generated from first, middle, and last name unless manually changed.
- `employee_code` must be generated by `EmployeeCodeService`.
- `manager_id` cannot equal employee id.
- `coach_id` cannot equal employee id.
- If `user_id` exists, the employee is linked to a login account.

---

## 5.7 employee_private_infos

One-to-one with employees.

```text
id bigInt PK
employee_id FK employees.id unique
private_email varchar(180) nullable
private_phone varchar(50) nullable
private_mobile varchar(50) nullable
private_address_line_1 varchar(255) nullable
private_address_line_2 varchar(255) nullable
private_city varchar(100) nullable
private_state varchar(100) nullable
private_country varchar(100) nullable
private_postal_code varchar(30) nullable
emergency_contact_name varchar(150) nullable
emergency_contact_relation varchar(80) nullable
emergency_contact_phone varchar(50) nullable
gender enum('male','female','other','prefer_not_to_say') nullable
date_of_birth date nullable
marital_status enum('single','married','widowed','divorced','separated') nullable
nationality varchar(100) nullable
identification_number varchar(100) nullable
passport_number varchar(100) nullable
passport_expiry_date date nullable
certificate_level varchar(150) nullable
field_of_study varchar(150) nullable
school varchar(180) nullable
spouse_name varchar(150) nullable
children_count int default 0
created_at timestamp
updated_at timestamp
```

Access rule:

- HR can view and edit.
- Employee can view and edit own basic private contact fields.
- Manager cannot view sensitive private fields unless permission is granted.

---

## 5.8 employee_hr_settings

One-to-one with employees.

```text
id bigInt PK
employee_id FK employees.id unique
time_off_approver_id FK employees.id nullable
expense_approver_id FK employees.id nullable
attendance_manager_id FK employees.id nullable
badge_id varchar(100) nullable
pin_code varchar(100) nullable
attendance_device_id varchar(100) nullable
bank_name varchar(150) nullable
bank_account_number varchar(100) nullable
bank_ifsc_or_swift varchar(100) nullable
bank_account_holder varchar(150) nullable
salary_payment_method enum('bank_transfer','cash','cheque','other') default 'bank_transfer'
payroll_structure_id bigint nullable
portal_access boolean default false
can_login boolean default false
created_at timestamp
updated_at timestamp
```

Security:

- Bank fields must never be shown in card/list views.
- Bank account number should be masked in UI.

---

## 5.9 employee_document_types

```text
id bigInt PK
company_id FK companies.id nullable
name varchar(150)
code varchar(50) nullable
requires_expiry boolean default false
requires_issue_date boolean default false
allowed_extensions varchar(255) nullable
max_size_mb int default 10
is_active boolean default true
created_at timestamp
updated_at timestamp
```

Examples:

- Passport
- ID Proof
- Driving License
- Education Certificate
- Experience Letter
- Appraisal Report
- Contract Copy

---

## 5.10 employee_documents

```text
id bigInt PK
company_id FK companies.id
employee_id FK employees.id
document_type_id FK employee_document_types.id nullable
document_number varchar(100)
title varchar(180) nullable
issue_date date nullable
expiry_date date nullable
notification_type enum('none','before_expiry','after_issue','custom_date') default 'none'
notification_days int default 0
notification_date date nullable
attachment_path varchar(255) nullable
attachment_original_name varchar(255) nullable
attachment_mime varchar(120) nullable
attachment_size int nullable
description text nullable
status enum('draft','active','expiring_soon','expired','archived') default 'draft'
created_by FK users.id nullable
approved_by FK users.id nullable
approved_at timestamp nullable
deleted_at timestamp nullable
created_at timestamp
updated_at timestamp
```

Indexes:

```text
company_id
employee_id
document_type_id
document_number
expiry_date
status
```

Unique constraint:

```text
company_id + document_number
```

Rules:

- If expiry date is within notification days, mark `expiring_soon`.
- If expiry date is before today, mark `expired`.
- Attachments stored under `storage/app/private/hr/documents`.
- Use signed routes for secure document download.

---

## 5.11 employee_timesheets

```text
id bigInt PK
company_id FK companies.id
employee_id FK employees.id
project_name varchar(180)
task_name varchar(180) nullable
work_date date
minutes_spent int default 0
description text nullable
status enum('draft','submitted','approved','rejected') default 'draft'
approved_by FK users.id nullable
approved_at timestamp nullable
created_by FK users.id nullable
created_at timestamp
updated_at timestamp
```

Indexes:

```text
company_id
employee_id
work_date
status
```

Display rule:

- Convert `minutes_spent` to `HH:MM`.
- Footer total must sum minutes for current filtered result.

---

## 5.12 employee_lifecycle_logs

Used to keep history from hiring to exit.

```text
id bigInt PK
company_id FK companies.id
employee_id FK employees.id
event_type enum('created','hired','department_changed','job_changed','manager_changed','promoted','transferred','contract_changed','document_added','leave_started','leave_ended','resigned','exited','archived','restored')
event_date date
old_value json nullable
new_value json nullable
remarks text nullable
created_by FK users.id nullable
created_at timestamp
updated_at timestamp
```

Rules:

- Create log when department, job, manager, status, hire date, or exit date changes.
- Show this later in Employee Lifecycle screen.

---

## 5.13 employee_relations Optional Table

Use only if family records become many-to-one.

```text
id bigInt PK
employee_id FK employees.id
name varchar(150)
relation varchar(80)
date_of_birth date nullable
phone varchar(50) nullable
is_emergency_contact boolean default false
created_at timestamp
updated_at timestamp
```

---

## 6. Eloquent Relationships

### Employee model

```php
class Employee extends Model
{
    use SoftDeletes;

    public function company() { return $this->belongsTo(Company::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function jobPosition() { return $this->belongsTo(JobPosition::class); }
    public function manager() { return $this->belongsTo(Employee::class, 'manager_id'); }
    public function coach() { return $this->belongsTo(Employee::class, 'coach_id'); }
    public function subordinates() { return $this->hasMany(Employee::class, 'manager_id'); }
    public function privateInfo() { return $this->hasOne(EmployeePrivateInfo::class); }
    public function hrSetting() { return $this->hasOne(EmployeeHrSetting::class); }
    public function documents() { return $this->hasMany(EmployeeDocument::class); }
    public function timesheets() { return $this->hasMany(EmployeeTimesheet::class); }
    public function lifecycleLogs() { return $this->hasMany(EmployeeLifecycleLog::class); }
}
```

---

## 7. Routes

Create `routes/hr.php` and load it from `RouteServiceProvider` or `web.php`.

```php
Route::middleware(['web', 'auth'])->prefix('hr')->name('hr.')->group(function () {
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

    Route::get('/employees/{employee}/documents', [EmployeeDocumentController::class, 'index'])->name('employees.documents.index');
    Route::get('/employees/{employee}/documents/create', [EmployeeDocumentController::class, 'create'])->name('employees.documents.create');
    Route::post('/employees/{employee}/documents', [EmployeeDocumentController::class, 'store'])->name('employees.documents.store');
    Route::get('/employees/{employee}/documents/{document}', [EmployeeDocumentController::class, 'show'])->name('employees.documents.show');
    Route::get('/employees/{employee}/documents/{document}/edit', [EmployeeDocumentController::class, 'edit'])->name('employees.documents.edit');
    Route::put('/employees/{employee}/documents/{document}', [EmployeeDocumentController::class, 'update'])->name('employees.documents.update');
    Route::delete('/employees/{employee}/documents/{document}', [EmployeeDocumentController::class, 'destroy'])->name('employees.documents.destroy');
    Route::get('/employees/{employee}/documents/{document}/download', [EmployeeDocumentController::class, 'download'])->name('employees.documents.download');

    Route::get('/employees/{employee}/timesheets', [EmployeeTimesheetController::class, 'index'])->name('employees.timesheets.index');
    Route::get('/employees/{employee}/timesheets/create', [EmployeeTimesheetController::class, 'create'])->name('employees.timesheets.create');
    Route::post('/employees/{employee}/timesheets', [EmployeeTimesheetController::class, 'store'])->name('employees.timesheets.store');

    Route::resource('departments', DepartmentController::class);
    Route::get('/employees/{employee}/org-chart', [OrganizationChartController::class, 'show'])->name('employees.org-chart');
});
```

---

## 8. Controllers

## 8.1 EmployeeController@index

Responsibilities:

- Read query string.
- Apply search.
- Apply filters.
- Apply department filter.
- Apply view type.
- Return paginated employees.
- Return department counts.

Expected query params:

```text
search
department_id
status
employee_type
manager_id
company_id
view
page
```

Use `EmployeeSearchService`.

---

## 8.2 EmployeeController@show

Load:

```php
$employee->load([
    'company',
    'department',
    'jobPosition',
    'manager.jobPosition',
    'coach',
    'subordinates.jobPosition',
    'privateInfo',
    'hrSetting.timeOffApprover',
    'hrSetting.expenseApprover',
]);
```

Also load counts:

```php
$employee->loadCount([
    'documents',
    'timesheets',
]);
```

For future module counts, use placeholder service methods:

- contracts_count
- payslips_count
- loans_count
- time_off_summary

---

## 8.3 EmployeeDocumentController@store

Steps:

1. Validate request.
2. Store file securely.
3. Create document row.
4. Calculate status.
5. Add lifecycle log.
6. Redirect to document list or profile smart button.

---

## 8.4 EmployeeTimesheetController@index

Steps:

1. Filter by employee.
2. Filter by month if present.
3. Search project, task, description.
4. Return rows.
5. Return total minutes.

---

## 9. Services

## 9.1 EmployeeCodeService

Purpose:

Generate employee codes.

Pattern:

```text
EMP-00001
EMP-00002
```

Config:

```php
config/hrms.php
'employee_code_prefix' => 'EMP',
'employee_code_padding' => 5,
```

---

## 9.2 EmployeeSearchService

Methods:

```php
public function query(array $filters): Builder
public function applySearch(Builder $query, ?string $search): Builder
public function applyDepartment(Builder $query, ?int $departmentId): Builder
public function applyStatus(Builder $query, ?string $status): Builder
public function departmentCounts(int $companyId): Collection
```

Search fields:

- display_name
- employee_code
- work_email
- work_phone
- work_mobile
- department.name
- jobPosition.name

---

## 9.3 OrganizationChartService

Methods:

```php
public function miniChart(Employee $employee): array
public function fullTree(?Employee $root = null): array
public function directReports(Employee $employee): Collection
```

Mini chart must return:

- Manager chain, one level above.
- Current employee.
- Direct reports, one level below.

---

## 9.4 EmployeeDocumentAlertService

Methods:

```php
public function recalculateStatus(EmployeeDocument $document): string
public function expiringSoon(int $days = 30): Collection
public function expired(): Collection
```

Cron command:

```text
php artisan hr:documents-refresh-status
```

Schedule daily.

---

## 9.5 EmployeeLifecycleService

Methods:

```php
public function log(Employee $employee, string $eventType, array $old = [], array $new = [], ?string $remarks = null): void
public function logFieldChanges(Employee $employee, array $changes): void
```

---

## 10. Policies and Permissions

Use Laravel policies and roles.

Recommended roles:

```text
super_admin
hr_manager
department_manager
employee
auditor
```

### Employee permissions

| Action | Super Admin | HR Manager | Dept Manager | Employee | Auditor |
|---|---:|---:|---:|---:|---:|
| View all employees | yes | yes | team only | own only | yes |
| Create employee | yes | yes | no | no | no |
| Edit work info | yes | yes | team limited | no | no |
| Edit private info | yes | yes | no | own limited | no |
| Edit HR settings | yes | yes | no | no | no |
| Archive employee | yes | yes | no | no | no |
| View documents | yes | yes | team limited | own only | yes |
| Upload document | yes | yes | team limited | own only | no |
| Delete document | yes | yes | no | no | no |

Use gates:

```php
viewAny
view
create
update
delete
archive
viewPrivateInfo
updatePrivateInfo
viewHrSettings
updateHrSettings
```

---

## 11. Validation Rules

## 11.1 EmployeeStoreRequest

```php
'first_name' => ['required', 'string', 'max:100'],
'last_name' => ['nullable', 'string', 'max:100'],
'display_name' => ['nullable', 'string', 'max:180'],
'work_email' => ['nullable', 'email', 'max:180', 'unique:employees,work_email'],
'work_phone' => ['nullable', 'string', 'max:50'],
'work_mobile' => ['nullable', 'string', 'max:50'],
'department_id' => ['nullable', 'exists:departments,id'],
'job_position_id' => ['nullable', 'exists:job_positions,id'],
'manager_id' => ['nullable', 'exists:employees,id'],
'coach_id' => ['nullable', 'exists:employees,id'],
'photo' => ['nullable', 'image', 'max:2048'],
'hire_date' => ['nullable', 'date'],
```

Extra rule:

- manager_id and coach_id cannot point to same employee during update.

---

## 11.2 EmployeeDocumentStoreRequest

```php
'document_number' => ['required', 'string', 'max:100'],
'document_type_id' => ['required', 'exists:employee_document_types,id'],
'issue_date' => ['nullable', 'date'],
'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
'notification_type' => ['required', Rule::in(['none','before_expiry','after_issue','custom_date'])],
'notification_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
'attachment' => ['nullable', 'file', 'max:10240'],
'description' => ['nullable', 'string'],
```

---

## 11.3 EmployeeTimesheetStoreRequest

```php
'work_date' => ['required', 'date'],
'project_name' => ['required', 'string', 'max:180'],
'task_name' => ['nullable', 'string', 'max:180'],
'description' => ['nullable', 'string'],
'hours' => ['required', 'numeric', 'min:0.01', 'max:24'],
```

Convert hours to minutes before storing.

---

## 12. UI Components

## 12.1 Employee Card Component

Path:

```text
resources/views/components/hr/employee-card.blade.php
```

Props:

```text
employee
showDepartment
showStatus
```

Must show:

- Photo.
- Name.
- Job title.
- Department tags.
- Email.
- Phone.
- Connection dot.
- Link to profile.

---

## 12.2 Smart Button Component

Path:

```text
resources/views/components/hr/smart-button.blade.php
```

Props:

```text
icon
label
count
url
status
```

Style:

- Border box.
- Small icon left.
- Count top or left.
- Label below count.
- Purple icon color.

---

## 12.3 Employee Tabs Component

Path:

```text
resources/views/components/hr/employee-tabs.blade.php
```

Tabs:

- Work Information
- Private Information
- HR Settings

Must respect permissions.

---

## 12.4 Organization Chart Node

Path:

```text
resources/views/components/hr/org-chart-node.blade.php
```

Props:

```text
employee
active
showChildrenCount
```

Must show:

- Small photo.
- Name.
- Job title.
- Count badge.

---

## 13. Seed Data

Create seeders:

```text
CompanySeeder
DepartmentSeeder
JobPositionSeeder
EmployeeSeeder
EmployeeDocumentTypeSeeder
EmployeeTimesheetSeeder
```

Seed at least 24 employees to match the screenshot count.

Example employees:

- Abigail Peterson — Consultant
- Anita Oliver — Experienced Developer
- Audrey Peterson — Consultant
- Beth Evans — Experienced Developer
- Doris Cole — Consultant
- Eli Lambert — Marketing and Community Manager
- Ernest Reed — Consultant
- Jeffrey Kelly — Marketing and Community Manager
- Jennie Fletcher — Experienced Developer
- Mitchell Admin — Chief Executive Officer
- Ronnie Hart — Chief Technical Officer
- Tina Williamson — Human Resources Manager

Seed departments:

- Administration
- Management
- Professional Services
- Research & Development
- Sales

Seed document types:

- Passport
- ID Proof
- License
- Certificate
- Contract
- Appraisal Report

Seed timesheets for Abigail Peterson:

```text
02/10/2022 | Research & Development | Unit Testing | Requirements analysis | 03:00
02/05/2022 | Office Design | Room 2: Decoration | Requirements analysis | 02:00
```

---

## 14. Dashboard Counts

For employee profile smart buttons:

```php
$stats = [
    'contracts_count' => 0,
    'time_off_balance_label' => '0/0 Days',
    'documents_count' => $employee->documents()->count(),
    'payslips_count' => 2,
    'timesheets_count' => $employee->timesheets()->count(),
    'loans_count' => 0,
];
```

In this phase, non-built modules may use placeholder counts.

Do not hardcode forever. Create service:

```text
EmployeeSmartButtonService
```

---

## 15. File Upload Rules

### Employee photos

Storage:

```text
storage/app/public/hr/employees/photos
```

Public URL through storage symlink.

Allowed:

- jpg
- jpeg
- png
- webp

Max size: 2 MB.

### Employee documents

Storage:

```text
storage/app/private/hr/employee-documents
```

Download only through authorized controller action.

Allowed:

- pdf
- jpg
- jpeg
- png
- doc
- docx

Max size: document type setting or 10 MB default.

---

## 16. Reports

In this phase, add basic reporting endpoints:

```text
GET /hr/reports/employees/by-department
GET /hr/reports/employees/by-status
GET /hr/reports/documents/expiring
```

Reports should return Blade pages first. JSON export may be added later.

---

## 17. Export

Add CSV export for employees.

Route:

```text
GET /hr/employees/export
```

Fields:

- Employee Code
- Name
- Work Email
- Work Phone
- Department
- Job Position
- Manager
- Employee Type
- Status
- Hire Date

Do not export private info or bank info by default.

---

## 18. Import Later

Do not build import in this phase unless asked.

But keep code ready by using:

```text
maatwebsite/excel
```

or native CSV parser later.

---

## 19. Notifications

Use Laravel notifications.

Notification classes:

```text
EmployeeDocumentExpiringNotification
EmployeeDocumentExpiredNotification
EmployeeCreatedNotification
EmployeeProfileUpdatedNotification
```

Channels:

- database
- mail, optional

Daily command:

```text
php artisan hr:notify-expiring-documents
```

---

## 20. Activity Log

Use custom lifecycle table first.

Optional package later:

```text
spatie/laravel-activitylog
```

Track:

- Employee created.
- Department changed.
- Manager changed.
- Job position changed.
- Status changed.
- Document added.
- Document expired.
- Employee archived.

---

## 21. Testing Requirements

Use Pest or PHPUnit.

Feature tests:

- HR can view employee directory.
- Employee can view own profile.
- Employee cannot view another employee private info.
- HR can create employee.
- HR can upload employee document.
- Expiry status is calculated correctly.
- Timesheet total shows correct HH:MM.
- Department sidebar counts are correct.
- Organization chart shows manager and direct reports.

Unit tests:

- EmployeeCodeService.
- EmployeeDocumentAlertService.
- OrganizationChartService.
- EmployeeSearchService.

---

## 22. Non-functional Requirements

### Performance

- Employee index must use eager loading.
- Avoid N+1 queries.
- Paginate employee directory.
- Use indexes on search and filter fields.

### Security

- Use policies for every action.
- Secure document downloads.
- Mask bank account number.
- Do not expose private info in API unless authorized.
- Validate all uploads.

### Accessibility

- Buttons must have clear labels.
- Image alt text must use employee name.
- Table headers must be semantic.
- Keyboard navigation must work for tabs.

### Responsive behavior

- Desktop: left department sidebar + card grid.
- Tablet: collapsible sidebar.
- Mobile: stacked cards and top filter drawer.

---

## 23. Coding Standards

- Follow Laravel naming conventions.
- Keep controllers thin.
- Use Form Requests for validation.
- Use Services for business logic.
- Use Policies for permissions.
- Use Blade components for repeated UI blocks.
- Use enums for fixed statuses.
- Use migration foreign keys with cascade rules carefully.
- Use soft deletes for employees and documents.

---

## 24. Implementation Order

### Phase 1 — Foundation

1. Create routes.
2. Create migrations.
3. Create models and relationships.
4. Create seeders.
5. Create base HRMS layout.

### Phase 2 — Employee Directory

1. Employee index card view.
2. Search.
3. Department filter.
4. List view.
5. Pagination.

### Phase 3 — Employee Profile

1. Profile header.
2. Smart buttons.
3. Work Information tab.
4. Private Information tab.
5. HR Settings tab.
6. Organization chart panel.

### Phase 4 — Documents

1. Document type setup.
2. Document list.
3. Document create form.
4. Attachment upload.
5. Expiry status.
6. Notifications command.

### Phase 5 — Timesheets

1. Timesheet list.
2. Month filter.
3. Total hours footer.
4. Create form.

### Phase 6 — Permissions and Tests

1. Policies.
2. Role checks.
3. Feature tests.
4. Unit tests.

---

## 25. Acceptance Checklist

The module is complete when:

- Employee directory looks close to the supplied screen.
- Department sidebar works and shows counts.
- Search works.
- Card/list/activity view switch exists.
- Create employee works.
- Edit employee works.
- Employee profile shows smart buttons.
- Employee profile has Work Information, Private Information, and HR Settings tabs.
- Organization chart panel works.
- Employee documents can be created with attachment, issue date, expiry date, notification type, and days.
- Employee document expiry status works.
- Employee timesheets list shows date, project, task, description, hours spent, and total.
- Policies protect private and HR fields.
- Seed data creates a realistic demo screen.
- Tests pass.

---

## 26. Notes for Coding Agents

- Do not build every HRMS module now.
- Keep links to Contracts, Time Off, Payslips, Loans, Assets, and Appraisals as placeholders unless those modules exist.
- Build Employee DB in a way that future modules can connect through employee id.
- Keep UI close to the reference, but do not copy protected images or brand assets.
- Use placeholder profile photos from local seed assets or generated initials.
- Focus on clean Laravel architecture first, then polish UI.

