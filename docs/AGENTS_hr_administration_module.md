# AGENTS.md — HR Administration Module Clone in Laravel

## 1. Module Goal

Build the **HR Administration Module** for a Laravel-based HRMS clone inspired by the Open HRMS / Horilla style screens shared by the user.

This module controls the organization structure, access levels, communication, employee movement, legal cases, resignation flow, custody of company assets, and work shift management.

The module must feel like an Odoo/OpenHRMS style application:

- Purple top navigation bar.
- Left-side filters where needed.
- Kanban cards for departments and employee views.
- Form views with Save, Discard, Send For Approval, Confirm, Process, Cancel, and Action buttons.
- Status pipeline on the top right of each workflow form.
- Search, Filters, Group By, Favorites, pagination, and view toggle buttons.
- Organization hierarchy using department tree and employee tree.
- Strong role, field, form, and section-level security.

---

## 2. Main Features Covered

### 2.1 Roles and Access Levels

The system must support:

- Department creation.
- Manager assignment.
- Employee hierarchy.
- Role-based access.
- Job-title-based access.
- Department-based data visibility.
- Branch/company-based data separation.
- Self-service employee access.
- HR Admin access.
- Manager access.
- Super Admin access.

Examples:

- Normal employee can view only their own leave, attendance, documents, custody requests, resignation, and profile.
- Manager can view direct and indirect team members.
- HR Manager can manage employee records but can be restricted from accounts or payroll sections.
- Super Admin can view and manage all modules.

---

### 2.2 Data, Field, and Form Security

The system must allow permission control at these levels:

- Module level.
- Menu level.
- Page level.
- Form section level.
- Field level.
- Record level.
- Action button level.

Examples:

- Hide salary fields from normal HR users.
- Hide legal case details from employees.
- Allow managers to approve custody but not delete records.
- Allow employees to create resignation requests but not approve them.
- Allow branch HR to view only employees in their assigned branch.

---

### 2.3 Communication

The module must support internal communication through:

- HR announcements.
- Alerts.
- Reminders.
- Email notifications.
- Approval messages.
- Activity logs.
- Optional mass mailing to employees by department, branch, role, or company.

---

### 2.4 Branch Transfer

The module must allow employees to be transferred between branches or companies.

Transfer flow:

1. Draft / New.
2. Transferred.
3. Done.

The transfer form must support:

- Employee.
- Date.
- Transfer To.
- Current company.
- New company or branch.
- Responsible person.
- Internal notes.
- Transfer button.

The system must update employee work information after approval.

---

### 2.5 Lawsuit / Legal Management

The system must manage legal cases related to employees.

Legal flow:

1. Draft.
2. Running / Process.
3. Won.
4. Lost / Cancelled.

Legal case form must support:

- Legal case code.
- Reference number.
- Date.
- Hearing date.
- Party 1.
- Party 2.
- Employee.
- Court name.
- Judge.
- Lawyer.
- Company.
- Case details.
- Attachments.
- Action button.
- Process button.
- Cancel button.

---

### 2.6 Resignation

The system must allow employees to submit resignations and HR/admin to process them.

Resignation flow:

1. Draft.
2. Confirm.
3. Approved.
4. Cancelled.

Resignation form must support:

- Resignation code.
- Employee.
- Department.
- Employee contract.
- Join date.
- Last day of employee.
- Approved last day.
- Notice period.
- Resignation type.
- Reason.
- Confirm button.
- Cancel button.
- Action menu.

---

### 2.7 Company Asset / Custody Handling

The system must track company assets issued to employees.

Custody flow:

1. Draft.
2. Waiting For Approval.
3. Approved.
4. Returned.

Custody form must support:

- Employee.
- Property / asset.
- Reason.
- Requested date.
- Return date.
- Company.
- Notes.
- Attachment if needed.
- Send For Approval button.
- Return button.

The system must send reminders if the employee holds the asset beyond the return date.

---

### 2.8 Workshift Management

The system must manage shifts and working time.

Required features:

- Create working time templates.
- Assign shifts by department.
- Assign shifts by employee.
- Schedule shifts with start and end date.
- Send shift change notification.
- Collect employee feedback if needed.
- Generate shift assignments from modal form.

Shift assignment modal must support:

- Department.
- Start date.
- End date.
- Generate button.
- Cancel button.

---

## 3. Main Dashboard / Navigation Structure

### 3.1 Top Navigation

Create a purple top bar with these menus:

- Employees
- Departments
- Announcements
- Transfers
- Legal Management
- Resignation
- Custody
- Shifts
- Configuration
- Reporting

Right side:

- Message icon with count.
- Notification icon with count.
- Activity icon with count.
- Logged-in user image and name.

---

### 3.2 Department Dashboard

Route:

```text
/admin/hr/departments
```

UI style:

- Page title: Departments.
- Create button on top left.
- Search bar on top right.
- Filters, Group By, Favorites.
- Pagination.
- Kanban cards.

Each department card must show:

- Department name.
- Employee button.
- Employee count.
- Time Off Requests count.
- Allocation Requests count.
- New Applicants count.
- Absence progress bar.
- Absence count, such as 0 / 1 or 1 / 3.

Example cards:

- Administration.
- Management.
- Professional Services.
- Research & Development.
- Sales.

Left filter may not be required here, but search and grouping must be present.

---

### 3.3 Employee Directory Panel

Route:

```text
/admin/hr/employees
```

UI style:

- Page title: Employees.
- Create button.
- Left filter by Department.
- Card/grid view by default.
- List view toggle.
- Search bar.
- Filters, Group By, Favorites.
- Pagination.

Left department filter:

- All.
- Administration.
- Management.
- Professional Services.
- Research & Development.
- Sales.

Employee card must show:

- Employee image.
- Name.
- Job title.
- Email.
- Phone.
- Department tags.
- Employee type tags.
- Status dot.
- Small activity icon.

---

### 3.4 Announcements Form

Route:

```text
/admin/hr/announcements/create
/admin/hr/announcements/{announcement}
```

UI style:

- Breadcrumb: Announcements / New.
- Save button.
- Discard button.
- Send For Approval button.
- Status bar: Draft → Waiting For Approval → Approved.
- Center form card.

Fields:

- Code No.
- Announcement Type.
- Title.
- Start Date.
- End Date.
- Attachment.
- Requested Date.
- Company.
- Letter tab.
- Letter rich text area.

---

### 3.5 Transfer Form

Route:

```text
/admin/hr/transfers/create
/admin/hr/transfers/{transfer}
```

UI style:

- Breadcrumb: Transfer / New.
- Save button.
- Discard button.
- Transfer button.
- Status bar: New → Transferred → Done.
- Center form card.

Fields:

- Employee.
- Date.
- Transfer To.
- Company.
- Responsible.
- Internal Notes tab.
- Notes textarea.

---

### 3.6 Legal Management Form

Route:

```text
/admin/hr/legal-cases/{legalCase}
```

UI style:

- Breadcrumb: Legal Management / LC0001.
- Edit button.
- Create button.
- Action button.
- Process button.
- Cancel button.
- Status bar: Draft → Running → Won.
- Center form card.

Fields:

- Case code.
- Reference number.
- Date.
- Hearing date.
- Party 1.
- Party 2.
- Employee.
- Court name.
- Judge.
- Lawyer.
- Company.
- Case Details tab.
- Details rich text.
- Attachments.

---

### 3.7 Resignation Form

Route:

```text
/admin/hr/resignations/{resignation}
```

UI style:

- Breadcrumb: Employee Resignation / Employee Name.
- Edit button.
- Create button.
- Action button.
- Confirm button.
- Cancel button.
- Status bar: Draft → Confirm.
- Center form card.

Sections:

Employee Details:

- Employee.
- Department.
- Employee contract.

Dates:

- Join date.
- Last day of employee.
- Approved last day of employee.
- Notice period.

Resignation Details:

- Type.
- Reason.

---

### 3.8 Custody Form

Route:

```text
/admin/hr/custodies/create
/admin/hr/custodies/{custody}
```

UI style:

- Breadcrumb: Custody / New.
- Save button.
- Discard button.
- Send For Approval button.
- Status bar: Draft → Waiting For Approval → Approved → Returned.
- Center form card.

Fields:

- Employee.
- Property.
- Reason.
- Requested Date.
- Return Date.
- Company.
- Notes tab.
- Notes textarea.

---

### 3.9 Shift Working Time Screen

Route:

```text
/admin/hr/shifts/working-times
```

UI style:

- Page title: Shift Working Time.
- Create button.
- Grid/list view toggle.
- Cards showing shift working time templates.

Card fields:

- Name, such as Standard 40 hours/week.
- Department count or linked department name.
- Active status.

Modal route/action:

```text
POST /admin/hr/shifts/generate
```

Modal title:

- Employee Shift.

Modal fields:

- Department.
- Start Date.
- End Date.
- Generate button.
- Cancel button.

---

## 4. Suggested Laravel Folder Structure

```text
app/
  Modules/
    HRAdministration/
      Actions/
        ApproveAnnouncementAction.php
        ApproveCustodyAction.php
        ConfirmResignationAction.php
        GenerateDepartmentShiftAction.php
        ProcessLegalCaseAction.php
        TransferEmployeeAction.php
      DTO/
        AnnouncementData.php
        CustodyData.php
        LegalCaseData.php
        ResignationData.php
        TransferData.php
        ShiftAssignmentData.php
      Enums/
        AnnouncementStatus.php
        CustodyStatus.php
        LegalCaseStatus.php
        ResignationStatus.php
        TransferStatus.php
        NotificationType.php
      Http/
        Controllers/
          DepartmentController.php
          EmployeeDirectoryController.php
          AnnouncementController.php
          TransferController.php
          LegalCaseController.php
          ResignationController.php
          CustodyController.php
          ShiftWorkingTimeController.php
          RoleAccessController.php
          FieldSecurityController.php
        Requests/
          StoreDepartmentRequest.php
          StoreAnnouncementRequest.php
          StoreTransferRequest.php
          StoreLegalCaseRequest.php
          StoreResignationRequest.php
          StoreCustodyRequest.php
          StoreShiftWorkingTimeRequest.php
          StoreFieldSecurityRuleRequest.php
      Models/
        Department.php
        Branch.php
        Company.php
        Employee.php
        JobTitle.php
        Role.php
        AccessLevel.php
        FieldSecurityRule.php
        FormSecurityRule.php
        Announcement.php
        AnnouncementRecipient.php
        Transfer.php
        LegalCase.php
        Resignation.php
        Custody.php
        CompanyAsset.php
        ShiftWorkingTime.php
        ShiftAssignment.php
        Reminder.php
        ActivityLog.php
      Policies/
        DepartmentPolicy.php
        AnnouncementPolicy.php
        TransferPolicy.php
        LegalCasePolicy.php
        ResignationPolicy.php
        CustodyPolicy.php
        ShiftPolicy.php
        FieldSecurityPolicy.php
      Services/
        AccessControlService.php
        FieldSecurityService.php
        OrganizationTreeService.php
        ReminderService.php
        AnnouncementService.php
        TransferService.php
        LegalCaseService.php
        ResignationService.php
        CustodyService.php
        ShiftService.php
      ViewModels/
        DepartmentDashboardViewModel.php
        EmployeeDirectoryViewModel.php
        AnnouncementFormViewModel.php
        TransferFormViewModel.php
        LegalCaseFormViewModel.php
        ResignationFormViewModel.php
        CustodyFormViewModel.php
        ShiftWorkingTimeViewModel.php
resources/
  views/
    hr-administration/
      layouts/
        app.blade.php
        topbar.blade.php
        filters.blade.php
        status-pipeline.blade.php
        form-card.blade.php
      departments/
        index.blade.php
        _kanban-card.blade.php
        create.blade.php
        edit.blade.php
      employees/
        index.blade.php
        _card.blade.php
        _list.blade.php
      announcements/
        index.blade.php
        create.blade.php
        show.blade.php
        edit.blade.php
        _form.blade.php
      transfers/
        index.blade.php
        create.blade.php
        show.blade.php
        edit.blade.php
        _form.blade.php
      legal-cases/
        index.blade.php
        create.blade.php
        show.blade.php
        edit.blade.php
        _form.blade.php
      resignations/
        index.blade.php
        create.blade.php
        show.blade.php
        edit.blade.php
        _form.blade.php
      custodies/
        index.blade.php
        create.blade.php
        show.blade.php
        edit.blade.php
        _form.blade.php
      shifts/
        working-times.blade.php
        _working-time-card.blade.php
        _generate-shift-modal.blade.php
routes/
  hr-administration.php
```

---

## 5. Database Structure

Use Laravel migrations. Use soft deletes on master and workflow tables.

### 5.1 companies

```text
id
name
code
email
phone
website
address_line_1
address_line_2
city
state
country
postal_code
is_active
created_by
updated_by
timestamps
softDeletes
```

---

### 5.2 branches

```text
id
company_id
name
code
address_line_1
address_line_2
city
state
country
postal_code
manager_employee_id
is_active
created_by
updated_by
timestamps
softDeletes
```

Relations:

- Branch belongs to Company.
- Branch has many Employees.
- Branch manager belongs to Employee.

---

### 5.3 departments

```text
id
company_id
branch_id
parent_id
name
code
manager_employee_id
color
sequence
is_active
created_by
updated_by
timestamps
softDeletes
```

Relations:

- Department belongs to Company.
- Department belongs to Branch.
- Department belongs to parent Department.
- Department has many child Departments.
- Department manager belongs to Employee.
- Department has many Employees.

Computed counts for dashboard:

- employees_count.
- time_off_requests_count.
- allocation_requests_count.
- new_applicants_count.
- absents_count.
- total_employees_count.

---

### 5.4 job_titles

```text
id
company_id
department_id
name
code
level
is_managerial
is_active
created_by
updated_by
timestamps
softDeletes
```

---

### 5.5 employees

This table may already exist from the Employee DB module. Add missing HR Admin fields if needed.

```text
id
user_id
company_id
branch_id
department_id
job_title_id
manager_employee_id
coach_employee_id
employee_code
first_name
middle_name
last_name
display_name
work_email
work_phone
work_mobile
personal_email
personal_phone
photo_path
join_date
employment_status
employee_type
is_active
created_by
updated_by
timestamps
softDeletes
```

Suggested employment_status values:

```text
active
on_leave
resigned
terminated
retired
transferred
archived
```

Suggested employee_type values:

```text
employee
trainee
consultant
contractor
manager
admin
```

---

### 5.6 roles

```text
id
name
slug
description
is_system
is_active
timestamps
softDeletes
```

Example roles:

- Super Admin.
- HR Admin.
- HR Manager.
- Department Manager.
- Branch Manager.
- Employee.
- Legal Officer.
- Asset Manager.

---

### 5.7 employee_role_assignments

```text
id
employee_id
role_id
company_id
branch_id nullable
department_id nullable
starts_at nullable
ends_at nullable
is_active
created_by
updated_by
timestamps
softDeletes
```

Purpose:

- A user may have different roles in different branches or departments.

---

### 5.8 access_levels

```text
id
role_id
module_key
permission_key
can_view
can_create
can_update
can_delete
can_approve
can_export
can_import
scope
created_by
updated_by
timestamps
softDeletes
```

Suggested scope values:

```text
own
team
department
branch
company
all
none
```

Example module_key:

```text
employees
departments
announcements
transfers
legal_cases
resignations
custodies
shifts
configuration
reports
```

---

### 5.9 field_security_rules

```text
id
role_id
module_key
form_key
section_key nullable
field_key
visibility
is_readonly
mask_type nullable
condition_json nullable
created_by
updated_by
timestamps
softDeletes
```

visibility values:

```text
visible
hidden
readonly
masked
```

mask_type examples:

```text
full
partial
last_four
email
phone
currency
```

Use this for field-level security.

---

### 5.10 form_security_rules

```text
id
role_id
module_key
form_key
section_key
action_key nullable
is_visible
is_enabled
condition_json nullable
created_by
updated_by
timestamps
softDeletes
```

Use this to hide/show sections and buttons.

Examples:

- Hide `legal_case_details` section from non-legal roles.
- Hide `approve_custody` action from employees.
- Disable `delete_resignation` action after confirmation.

---

### 5.11 announcements

```text
id
company_id
branch_id nullable
code_no
announcement_type
title
letter
start_date
end_date
attachment_path nullable
requested_date
status
created_by_employee_id
approved_by_employee_id nullable
approved_at nullable
cancelled_at nullable
created_by
updated_by
timestamps
softDeletes
```

Status values:

```text
draft
waiting_for_approval
approved
cancelled
expired
```

announcement_type examples:

```text
general_announcement
policy_update
holiday_notice
hr_notice
urgent_alert
```

---

### 5.12 announcement_recipients

```text
id
announcement_id
recipient_type
company_id nullable
branch_id nullable
department_id nullable
role_id nullable
employee_id nullable
has_read
read_at nullable
created_at
updated_at
```

recipient_type values:

```text
all_company
branch
department
role
employee
custom_group
```

---

### 5.13 transfers

```text
id
company_id
from_branch_id nullable
to_branch_id nullable
from_department_id nullable
to_department_id nullable
employee_id
transfer_date
transfer_to
responsible_employee_id
status
internal_notes
created_by_employee_id
transferred_by_employee_id nullable
transferred_at nullable
done_at nullable
cancelled_at nullable
created_by
updated_by
timestamps
softDeletes
```

Status values:

```text
new
transferred
done
cancelled
```

---

### 5.14 legal_cases

```text
id
company_id
case_code
reference_number
case_date
hearing_date nullable
party_1
party_2
employee_id nullable
court_name
judge_name nullable
lawyer_name nullable
case_details
status
result_notes nullable
created_by_employee_id
processed_by_employee_id nullable
processed_at nullable
closed_at nullable
created_by
updated_by
timestamps
softDeletes
```

Status values:

```text
draft
running
won
lost
cancelled
```

---

### 5.15 legal_case_attachments

```text
id
legal_case_id
file_name
file_path
mime_type
file_size
uploaded_by_employee_id
created_at
updated_at
```

---

### 5.16 resignations

```text
id
company_id
employee_id
department_id
contract_id nullable
resignation_code
join_date
last_day_of_employee
approved_last_day nullable
notice_period_days
resignation_type
reason
status
submitted_by_employee_id
confirmed_by_employee_id nullable
confirmed_at nullable
approved_by_employee_id nullable
approved_at nullable
cancelled_at nullable
created_by
updated_by
timestamps
softDeletes
```

Status values:

```text
draft
confirmed
approved
cancelled
```

resignation_type examples:

```text
normal_resignation
retirement
termination
contract_end
personal_reason
health_reason
career_change
```

---

### 5.17 company_assets

```text
id
company_id
branch_id nullable
asset_code
name
category
serial_number nullable
purchase_date nullable
purchase_value nullable
status
current_employee_id nullable
notes nullable
created_by
updated_by
timestamps
softDeletes
```

Status values:

```text
available
assigned
under_repair
lost
retired
```

---

### 5.18 custodies

```text
id
company_id
employee_id
asset_id
reason
requested_date
return_date nullable
actual_return_date nullable
status
notes
approved_by_employee_id nullable
approved_at nullable
returned_by_employee_id nullable
returned_at nullable
created_by
updated_by
timestamps
softDeletes
```

Status values:

```text
draft
waiting_for_approval
approved
returned
rejected
cancelled
overdue
```

---

### 5.19 shift_working_times

```text
id
company_id
name
code
weekly_hours
start_time
end_time
break_minutes
working_days_json
is_default
is_active
created_by
updated_by
timestamps
softDeletes
```

Example:

- Standard 40 hours/week.

---

### 5.20 shift_assignments

```text
id
company_id
branch_id nullable
department_id nullable
employee_id nullable
shift_working_time_id
start_date
end_date
assignment_type
status
generated_by_employee_id nullable
notified_at nullable
feedback_status nullable
feedback_comment nullable
created_by
updated_by
timestamps
softDeletes
```

assignment_type values:

```text
department
employee
branch
company
```

status values:

```text
draft
active
expired
cancelled
```

---

### 5.21 reminders

```text
id
company_id
module_key
record_type
record_id
employee_id nullable
recipient_email nullable
title
message
remind_at
sent_at nullable
status
created_by
updated_by
timestamps
softDeletes
```

status values:

```text
pending
sent
failed
cancelled
```

Use reminders for:

- Custody return date.
- Legal hearing date.
- Announcement expiry.
- Shift notifications.
- Resignation last working day.

---

### 5.22 activity_logs

```text
id
company_id nullable
employee_id nullable
user_id nullable
module_key
record_type
record_id
action
old_values json nullable
new_values json nullable
ip_address nullable
user_agent nullable
created_at
```

Use this for lifecycle tracking and audit trail.

---

## 6. Routes

Create routes in:

```text
routes/hr-administration.php
```

Suggested route group:

```php
Route::middleware(['web', 'auth'])
    ->prefix('admin/hr')
    ->name('hr.')
    ->group(function () {
        Route::resource('departments', DepartmentController::class);
        Route::get('employees', [EmployeeDirectoryController::class, 'index'])->name('employees.index');

        Route::resource('announcements', AnnouncementController::class);
        Route::post('announcements/{announcement}/send-for-approval', [AnnouncementController::class, 'sendForApproval'])->name('announcements.send-for-approval');
        Route::post('announcements/{announcement}/approve', [AnnouncementController::class, 'approve'])->name('announcements.approve');
        Route::post('announcements/{announcement}/cancel', [AnnouncementController::class, 'cancel'])->name('announcements.cancel');

        Route::resource('transfers', TransferController::class);
        Route::post('transfers/{transfer}/transfer', [TransferController::class, 'transfer'])->name('transfers.transfer');
        Route::post('transfers/{transfer}/done', [TransferController::class, 'done'])->name('transfers.done');

        Route::resource('legal-cases', LegalCaseController::class);
        Route::post('legal-cases/{legalCase}/process', [LegalCaseController::class, 'process'])->name('legal-cases.process');
        Route::post('legal-cases/{legalCase}/won', [LegalCaseController::class, 'markWon'])->name('legal-cases.won');
        Route::post('legal-cases/{legalCase}/lost', [LegalCaseController::class, 'markLost'])->name('legal-cases.lost');
        Route::post('legal-cases/{legalCase}/cancel', [LegalCaseController::class, 'cancel'])->name('legal-cases.cancel');

        Route::resource('resignations', ResignationController::class);
        Route::post('resignations/{resignation}/confirm', [ResignationController::class, 'confirm'])->name('resignations.confirm');
        Route::post('resignations/{resignation}/approve', [ResignationController::class, 'approve'])->name('resignations.approve');
        Route::post('resignations/{resignation}/cancel', [ResignationController::class, 'cancel'])->name('resignations.cancel');

        Route::resource('custodies', CustodyController::class);
        Route::post('custodies/{custody}/send-for-approval', [CustodyController::class, 'sendForApproval'])->name('custodies.send-for-approval');
        Route::post('custodies/{custody}/approve', [CustodyController::class, 'approve'])->name('custodies.approve');
        Route::post('custodies/{custody}/return', [CustodyController::class, 'return'])->name('custodies.return');

        Route::get('shifts/working-times', [ShiftWorkingTimeController::class, 'index'])->name('shifts.working-times.index');
        Route::post('shifts/working-times', [ShiftWorkingTimeController::class, 'store'])->name('shifts.working-times.store');
        Route::post('shifts/generate', [ShiftWorkingTimeController::class, 'generate'])->name('shifts.generate');

        Route::get('configuration/roles-access', [RoleAccessController::class, 'index'])->name('configuration.roles-access.index');
        Route::post('configuration/roles-access', [RoleAccessController::class, 'store'])->name('configuration.roles-access.store');

        Route::get('configuration/field-security', [FieldSecurityController::class, 'index'])->name('configuration.field-security.index');
        Route::post('configuration/field-security', [FieldSecurityController::class, 'store'])->name('configuration.field-security.store');
    });
```

---

## 7. Controllers and Responsibilities

### 7.1 DepartmentController

Responsibilities:

- List departments in kanban format.
- Show employee count.
- Show absence progress.
- Create department.
- Edit department.
- Assign manager.
- Support hierarchy through parent department.
- Provide department tree data.

Must support:

- Search by name/code.
- Group by company, branch, manager, parent department.
- Filters by company, branch, active/inactive.

---

### 7.2 EmployeeDirectoryController

Responsibilities:

- List employees in card view.
- Provide department sidebar filter.
- Provide list view.
- Support search by name, email, phone, department, job title.
- Apply role-based record scope.

Important:

Do not expose restricted employee fields here. Use FieldSecurityService before sending data to the view.

---

### 7.3 AnnouncementController

Responsibilities:

- Create HR announcements.
- Save as draft.
- Send for approval.
- Approve announcement.
- Send notifications to recipients.
- Track read status.
- Attach documents.
- Expire announcements after end date.

---

### 7.4 TransferController

Responsibilities:

- Create employee transfer.
- Update branch/company/department after transfer.
- Record employee lifecycle event.
- Add activity log.
- Notify employee, old manager, new manager, and HR.

---

### 7.5 LegalCaseController

Responsibilities:

- Create legal case.
- Process case.
- Mark as won/lost.
- Cancel case.
- Attach case files.
- Add hearing date reminder.
- Restrict visibility to allowed roles.

---

### 7.6 ResignationController

Responsibilities:

- Employee can create resignation.
- HR/admin can create resignation on behalf of employee.
- Confirm resignation.
- Approve resignation.
- Update employee status after final day.
- Trigger offboarding placeholder event.
- Notify manager and HR.

---

### 7.7 CustodyController

Responsibilities:

- Create custody request.
- Send for approval.
- Approve custody.
- Assign asset to employee.
- Return asset.
- Mark overdue if return date passes.
- Send reminder.

---

### 7.8 ShiftWorkingTimeController

Responsibilities:

- Manage shift working time templates.
- Generate shift assignments by department.
- Send notifications.
- Avoid overlapping shift assignment.
- Allow modal-based generation.

---

### 7.9 RoleAccessController

Responsibilities:

- Manage roles.
- Assign access levels.
- Configure module permissions.
- Configure data scope.

---

### 7.10 FieldSecurityController

Responsibilities:

- Configure field visibility.
- Configure readonly fields.
- Configure masked fields.
- Configure form sections and button rules.

---

## 8. Services

### 8.1 AccessControlService

Must provide:

```php
canViewModule(User $user, string $moduleKey): bool
canPerform(User $user, string $moduleKey, string $permissionKey): bool
getScope(User $user, string $moduleKey): string
applyScope(Builder $query, User $user, string $moduleKey): Builder
```

Scope behavior:

- `own`: only records linked to logged-in employee.
- `team`: records for direct reporting employees.
- `department`: records for same department.
- `branch`: records for same branch.
- `company`: records for same company.
- `all`: all records.
- `none`: no records.

---

### 8.2 FieldSecurityService

Must provide:

```php
filterFields(User $user, string $moduleKey, string $formKey, array $data): array
isFieldVisible(User $user, string $moduleKey, string $formKey, string $fieldKey): bool
isFieldReadonly(User $user, string $moduleKey, string $formKey, string $fieldKey): bool
isSectionVisible(User $user, string $moduleKey, string $formKey, string $sectionKey): bool
isActionVisible(User $user, string $moduleKey, string $formKey, string $actionKey): bool
```

Rules:

- Hidden fields must not be rendered.
- Masked fields must be transformed before rendering.
- Readonly fields must not be accepted from request payload.
- Server-side validation must enforce field security. Do not trust frontend only.

---

### 8.3 OrganizationTreeService

Must provide:

```php
getDepartmentTree(int $companyId): array
getEmployeeTree(int $managerEmployeeId = null): array
getManagerChain(int $employeeId): array
getSubordinates(int $managerEmployeeId, bool $recursive = true): Collection
```

---

### 8.4 ReminderService

Must provide:

```php
createReminder(string $moduleKey, Model $record, Carbon $date, array $recipients): Reminder
sendDueReminders(): void
markAsSent(Reminder $reminder): void
```

Used by:

- Custody return reminders.
- Legal hearing reminders.
- Announcement start/end reminders.
- Shift schedule reminders.
- Resignation last day reminders.

---

### 8.5 TransferService

Must handle:

- Validation of employee current company/branch.
- Updating employee branch/company/department.
- Updating related contracts if needed.
- Creating employee lifecycle log.
- Sending notifications.

---

### 8.6 CustodyService

Must handle:

- Asset availability check.
- Asset assignment.
- Asset return.
- Overdue calculation.
- Reminder scheduling.

---

## 9. Policies

Create Laravel policies for:

- DepartmentPolicy.
- AnnouncementPolicy.
- TransferPolicy.
- LegalCasePolicy.
- ResignationPolicy.
- CustodyPolicy.
- ShiftPolicy.
- FieldSecurityPolicy.

Example policy rules:

### ResignationPolicy

- Employee can create own resignation.
- Manager can view team resignations.
- HR can view company resignations.
- Only HR/Admin can approve.
- Employee cannot edit after confirmation.

### CustodyPolicy

- Employee can request custody.
- Asset Manager can approve custody.
- HR Admin can view all custody records.
- Employee can view only own custody records.

### LegalCasePolicy

- Legal Officer, HR Admin, and Super Admin can view legal cases.
- Employee cannot view legal cases unless explicitly allowed.
- Only Legal Officer/Admin can process or close legal cases.

---

## 10. Workflow Status Rules

### 10.1 Announcement

```text
Draft -> Waiting For Approval -> Approved
Draft -> Cancelled
Waiting For Approval -> Cancelled
Approved -> Expired
```

Actions:

- Save: allowed in draft.
- Send For Approval: draft only.
- Approve: waiting only.
- Cancel: draft or waiting.

---

### 10.2 Transfer

```text
New -> Transferred -> Done
New -> Cancelled
Transferred -> Cancelled
```

Actions:

- Transfer: new only.
- Done: transferred only.
- Cancel: new or transferred.

---

### 10.3 Legal Case

```text
Draft -> Running -> Won
Draft -> Running -> Lost
Draft -> Cancelled
Running -> Cancelled
```

Actions:

- Process: draft only.
- Mark Won: running only.
- Mark Lost: running only.
- Cancel: draft or running.

---

### 10.4 Resignation

```text
Draft -> Confirmed -> Approved
Draft -> Cancelled
Confirmed -> Cancelled
```

Actions:

- Confirm: draft only.
- Approve: confirmed only.
- Cancel: draft or confirmed.

---

### 10.5 Custody

```text
Draft -> Waiting For Approval -> Approved -> Returned
Draft -> Cancelled
Waiting For Approval -> Rejected
Approved -> Overdue
```

Actions:

- Send For Approval: draft only.
- Approve: waiting only.
- Reject: waiting only.
- Return: approved or overdue.

---

## 11. UI Components

### 11.1 Status Pipeline Component

Reusable Blade component:

```text
<x-hr.status-pipeline :status="$status" :steps="$steps" />
```

Must render like:

```text
Draft | Waiting For Approval | Approved
```

Current status must be purple.

---

### 11.2 Form Header Component

Reusable component with:

- Breadcrumb.
- Save.
- Discard.
- Edit.
- Create.
- Main action button.
- Cancel button.
- Action dropdown.
- Previous/next record control.

---

### 11.3 Kanban Card Component

Used for:

- Department cards.
- Employee cards.
- Shift cards.

Must support:

- Image area.
- Title.
- Subtitle.
- Tags.
- Count badges.
- Status dot.
- Small activity icon.

---

### 11.4 Search Toolbar Component

Must include:

- Search input.
- Filters.
- Group By.
- Favorites.
- Pagination.
- Kanban/List view toggle.

---

### 11.5 Side Filter Component

Used in employee directory.

Must support:

- Department list.
- Count per department.
- Active selected filter.

---

## 12. Validation Rules

### 12.1 Announcement

- title is required.
- start_date is required.
- end_date must be after or same as start_date.
- attachment must be allowed file type.
- letter may be nullable.
- company_id is required.

### 12.2 Transfer

- employee_id is required.
- transfer_date is required.
- transfer_to is required.
- to_branch_id or to_department_id is required.
- responsible_employee_id is required.
- cannot transfer resigned employee.
- cannot transfer to the same branch and same department without a change.

### 12.3 Legal Case

- reference_number is required.
- party_1 is required.
- party_2 is required.
- court_name is required.
- case_date is required.
- hearing_date must be after or same as case_date.

### 12.4 Resignation

- employee_id is required.
- last_day_of_employee is required.
- resignation_type is required.
- reason is required.
- approved_last_day must be after join_date.
- notice_period_days cannot be negative.

### 12.5 Custody

- employee_id is required.
- asset_id is required.
- requested_date is required.
- return_date must be after or same as requested_date.
- asset must be available before approval.

### 12.6 Shift Assignment

- department_id is required for department assignment.
- start_date is required.
- end_date is required.
- end_date must be after or same as start_date.
- shift_working_time_id is required.
- prevent overlapping active assignments for the same employee/date range.

---

## 13. Notifications

Use Laravel Notifications.

Suggested notifications:

```text
AnnouncementSubmittedNotification
AnnouncementApprovedNotification
TransferCreatedNotification
TransferCompletedNotification
LegalHearingReminderNotification
ResignationSubmittedNotification
ResignationApprovedNotification
CustodyApprovalRequestedNotification
CustodyApprovedNotification
CustodyReturnReminderNotification
ShiftAssignedNotification
```

Channels:

- Database.
- Mail.
- Optional SMS later.

---

## 14. Scheduled Jobs

Create console commands:

```text
hr:send-due-reminders
hr:expire-announcements
hr:mark-custody-overdue
hr:expire-shift-assignments
hr:update-resigned-employees
```

Schedule examples:

```php
$schedule->command('hr:send-due-reminders')->everyFifteenMinutes();
$schedule->command('hr:expire-announcements')->daily();
$schedule->command('hr:mark-custody-overdue')->daily();
$schedule->command('hr:expire-shift-assignments')->daily();
$schedule->command('hr:update-resigned-employees')->daily();
```

---

## 15. Reports

Create basic reports:

### 15.1 Department Report

Fields:

- Department.
- Company.
- Branch.
- Manager.
- Employee count.
- Absence count.
- Leave request count.

### 15.2 Transfer Report

Fields:

- Employee.
- From branch.
- To branch.
- From department.
- To department.
- Date.
- Responsible.
- Status.

### 15.3 Custody Report

Fields:

- Employee.
- Asset.
- Requested date.
- Return date.
- Actual return date.
- Status.
- Overdue days.

### 15.4 Resignation Report

Fields:

- Employee.
- Department.
- Type.
- Submitted date.
- Last day.
- Approved last day.
- Status.

### 15.5 Legal Case Report

Fields:

- Case code.
- Reference number.
- Employee.
- Court.
- Hearing date.
- Status.

---

## 16. Seeder Data

Create seeders for demo data.

### 16.1 Companies

- YourCompany.

### 16.2 Departments

- Administration.
- Management.
- Professional Services.
- Research & Development.
- Sales.

### 16.3 Roles

- Super Admin.
- HR Admin.
- HR Manager.
- Department Manager.
- Branch Manager.
- Employee.
- Legal Officer.
- Asset Manager.

### 16.4 Employees

Use demo employee names similar to the screens:

- Abigail Peterson.
- Anita Oliver.
- Audrey Peterson.
- Beth Evans.
- Doris Cole.
- Mitchell Admin.

### 16.5 Working Times

- Standard 40 hours/week.

### 16.6 Assets

- Laptop.
- Mobile Phone.
- Access Card.
- Desk Chair.

---

## 17. Implementation Phases

### Phase 1 — Core Layout and Navigation

- Create HR Administration route file.
- Create purple top navigation.
- Create shared layout.
- Add menu items.
- Add search toolbar component.
- Add status pipeline component.

### Phase 2 — Organization Structure

- Create companies, branches, departments, job titles.
- Create department kanban dashboard.
- Create employee directory with department sidebar.
- Add organization tree service.

### Phase 3 — Roles and Security

- Create roles.
- Create access levels.
- Create field security rules.
- Create form security rules.
- Add policies.
- Apply record scopes.

### Phase 4 — Communication

- Build announcements.
- Add recipients.
- Add approval flow.
- Add database and mail notifications.
- Add read status.

### Phase 5 — Transfers

- Build transfer CRUD.
- Add transfer workflow.
- Update employee branch/company/department.
- Add lifecycle log.

### Phase 6 — Legal Management

- Build legal case CRUD.
- Add process, won, lost, cancel actions.
- Add attachments.
- Add hearing reminders.

### Phase 7 — Resignation

- Build resignation CRUD.
- Add confirm and approve workflow.
- Update employee status.
- Add notification to manager and HR.

### Phase 8 — Custody

- Build asset master.
- Build custody workflow.
- Add asset assignment and return.
- Add overdue reminder.

### Phase 9 — Shifts

- Build working time templates.
- Build generate shift modal.
- Add shift assignment generation.
- Add notifications.

### Phase 10 — Reports and Final Polish

- Add reports.
- Add filters and export.
- Add audit logs.
- Improve responsive views.
- Add tests.

---

## 18. Testing Requirements

### Feature Tests

- HR admin can create department.
- Manager can view own department employees.
- Employee cannot view legal cases.
- Employee can submit resignation.
- HR can approve resignation.
- Asset Manager can approve custody.
- Custody return updates asset status.
- Transfer updates employee branch.
- Announcement approval sends notifications.
- Field security hides restricted fields.

### Unit Tests

- AccessControlService scope logic.
- FieldSecurityService masking logic.
- OrganizationTreeService hierarchy logic.
- ReminderService due reminders.
- CustodyService overdue detection.
- TransferService employee update logic.

---

## 19. Design Notes

- Use clean, compact forms similar to OpenHRMS.
- Keep white form cards centered on a light grey background.
- Use purple as the primary action color.
- Use small text labels and compact spacing.
- Use status ribbons at top right.
- Use simple tabs for Letter, Notes, Internal Notes, and Case Details.
- Use cards for department and employee dashboards.
- Always show Save and Discard in create/edit mode.
- Always show Edit and Create in read mode.

---

## 20. Important Coding Rules for Agents

1. Do not hardcode user permissions in Blade files only.
2. Always enforce permissions in policies and services.
3. Do not expose hidden fields in API or controller response.
4. All workflow actions must use service/action classes.
5. Every important workflow change must create an activity log.
6. Every approval action must record approver and timestamp.
7. Every file upload must validate file type and size.
8. Every employee-related query must apply company/branch/department scope.
9. Keep module code inside `app/Modules/HRAdministration` where possible.
10. Use Laravel validation request classes.
11. Use enums for statuses.
12. Use soft deletes on workflow and master tables.
13. Use database notifications for all internal alerts.
14. Build UI with reusable Blade components.
15. Keep code simple, clean, and testable.

---

## 21. Future Integration Points

This module must be ready to connect with:

- Employee Database module.
- Attendance module.
- Leave module.
- Payroll module.
- Recruitment module.
- Performance module.
- Offboarding module.
- Document module.
- Asset module.

Integration examples:

- Resignation approval can trigger offboarding.
- Transfer can update employee contract.
- Custody can show in employee profile smart buttons.
- Department dashboard can show leave and absence counts.
- Announcement can target departments or roles.
- Shift assignment can affect attendance calculations.

