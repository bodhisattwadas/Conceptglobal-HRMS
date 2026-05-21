# AGENTS.md — Payroll Management Module

## Project Context

This module clones the Payroll Management part of Open HRMS / Odoo-style HRMS into Laravel.

The Payroll module must manage employee contracts, salary structures, salary rules, payslips, payslip batches, deductions, allowances, leaves, timesheets, and accounting-ready payroll data.

This file is written for a multi-agent Laravel build. Each agent must follow the same naming rules, database rules, workflow rules, and UI structure described here.

---

## 1. Module Name

**Payroll Management Module**

Suggested Laravel module path:

```text
app/Modules/Payroll
```

Suggested route prefix:

```text
/payroll
```

Suggested permission prefix:

```text
payroll.*
```

---

## 2. Main Goals

Build a payroll system that supports:

1. Employee contract based payroll.
2. Salary structures attached to contracts.
3. Salary rules for earnings, deductions, benefits, and allowances.
4. Single employee payslip generation.
5. Batch payslip generation.
6. Payroll computation from salary rules.
7. Payroll adjustment from leaves and timesheets.
8. Draft, verify, approve, close, and cancel flows.
9. Payroll reporting.
10. Accounting-ready totals for future finance integration.

---

## 3. Dashboard and Menu Structure

The screenshot shows the Payroll app with a purple top bar and the following menu items:

```text
Payroll
├── Employee Payslips
├── Payslips Batches
└── Configuration
    ├── Salary Structures
    ├── Salary Rules
    ├── Contribution Registers
    └── Payroll Settings
```

Main top layout:

```text
Left: App launcher icon + Payroll title
Center menu: Employee Payslips | Payslips Batches | Configuration
Right: Messages | Notifications | Company | User Profile
```

Use the same common HRMS layout already used in Employee, HR Administration, Attendance, and Leaves modules.

---

## 4. Main Screens to Clone

### 4.1 Employee Contract Payroll View

Screenshot path reference:

```text
Employees / Abigail Peterson / Abigail Peterson's Contracts History / abi contract
```

Screen purpose:

Show employee contract details used by payroll.

Header:

```text
Employees / Employee Name / Contract History / Contract Name
```

Buttons:

```text
Edit
Create
Action
```

Status pipeline:

```text
New → Running → Expired → Cancelled
```

Main card fields:

```text
Contract Name
Employee
Department
Contract Start Date
Contract End Date
Notice Period
Job Position
Employee Category
Salary Structure
Salary Structure Type
Working Schedule
HR Responsible
```

Tabs:

```text
Contract Details
Salary Information
```

Contract details tab:

```text
Notes
```

Salary information tab should support:

```text
Wage / Basic Salary
Monthly Advantages
Allowances
Deductions
Salary Structure
Payroll Schedule
```

Development note:

This screen may belong to Employee module, but Payroll must read contract data for payslip computation.

---

### 4.2 Salary Structures List

Screenshot path reference:

```text
Payroll / Salary Structures
```

Screen purpose:

List all salary structures.

Buttons:

```text
Create
Import / Download icon if needed
```

Search tools:

```text
Search
Filters
Group By
Favorites
Pagination
List view
Kanban view
```

Table columns:

```text
Checkbox
Name
Reference
Salary Rules
```

Example rows:

```text
Base for new structures | BASE | 3 records
Marketing Executive | ME | 4 records
Marketing Executive for Gilles Grave | MEGG | 2 records
```

Required actions:

```text
Create salary structure
Edit salary structure
Duplicate salary structure
Archive salary structure
Delete only if unused
Open salary rules under structure
```

---

### 4.3 Salary Rule Form

Screenshot path reference:

```text
Salary Rules / Medical Allowance
```

Screen purpose:

Create configurable salary rule used in payslip calculation.

Header:

```text
Salary Rules / Rule Name
```

Buttons:

```text
Edit
Create
Action
```

Main fields:

```text
Name
Code
Active
Sequence
Appears on Payslip
```

Tabs:

```text
General
Child Rules
Inputs
Description
```

General tab sections:

```text
Conditions
Computation
Company Contribution
```

Condition fields:

```text
Condition Based On
Condition Range
Python Condition / Rule Expression
```

Computation fields:

```text
Amount Type
Fixed Amount
Percentage
Percentage Based On
Quantity
Python Code / Formula Expression
```

Company contribution fields:

```text
Contribution Register
```

Important Laravel adaptation:

Do not execute raw Python code in Laravel.
Use a safe internal expression engine.

Supported rule expression methods:

```text
always_true
fixed_amount
percentage_of_basic
percentage_of_gross
based_on_attendance
based_on_leave_without_pay
based_on_overtime
custom_formula_safe
```

For `custom_formula_safe`, allow only approved variables and math operations.
Never allow direct PHP eval.

Allowed variables:

```text
basic
wage
gross
net
worked_days
overtime_hours
leave_without_pay_days
paid_leave_days
unpaid_leave_days
allowance_total
deduction_total
contract_amount
employee_grade_factor
```

---

### 4.4 Payslip Batch Form

Screenshot path reference:

```text
Salary Rules / Medical Allowance / Payslips Batches / batch feb payroll
```

Screen purpose:

Generate payslips for many employees in one payroll period.

Header:

```text
Payslips Batches / Batch Name
```

Buttons:

```text
Edit
Create
Generate Payslips
Close
Action
```

Status pipeline:

```text
Draft → Close
```

Main fields:

```text
Batch Name
Period
Credit Note
```

Payslips table columns:

```text
Reference
Employee
Payslip Name
Date From
Date To
Status
Delete Icon
```

Example rows:

```text
SLIP0008 | Abigail Peterson | Salary Slip of Abigail Peterson for February-2022 | 02/01/2022 | 02/28/2022 | Draft
SLIP0007 | Anita Oliver | Salary Slip of Anita Oliver for February-2022 | 02/01/2022 | 02/28/2022 | Draft
```

Required actions:

```text
Generate payslips from selected salary structure
Generate payslips from selected employees
Compute all payslips
Confirm all payslips
Close batch
Cancel batch if not closed
Export batch summary
```

---

## 5. Payroll Workflow

### 5.1 Contract Workflow

```text
New
  ↓
Running
  ↓
Expired
```

Optional:

```text
Cancelled
```

Rules:

1. Only running contracts are eligible for payslip generation.
2. A contract must have one salary structure.
3. A contract must have start date.
4. End date may be blank for active employee.
5. Employee cannot have two running contracts for the same company and same period.

---

### 5.2 Salary Structure Workflow

Salary structure is mostly configuration.

```text
Draft / Active
Archived
```

Rules:

1. Structure has many salary rules.
2. Rules must be ordered by sequence.
3. Structure must have at least one earning rule before being used.
4. Used structures cannot be hard deleted.

---

### 5.3 Salary Rule Workflow

Salary rule states:

```text
Active
Inactive
Archived
```

Rule categories:

```text
basic
allowance
deduction
gross
net
company_contribution
loan_deduction
tax
other
```

Rule amount types:

```text
fixed
percentage
formula
input
```

Rule condition types:

```text
always
range
expression
```

---

### 5.4 Payslip Workflow

```text
Draft
  ↓ compute
Computed
  ↓ verify
Waiting Approval
  ↓ approve
Approved
  ↓ mark paid
Paid
```

Exception states:

```text
Refused
Cancelled
```

Rules:

1. Employee can view own payslips.
2. HR officer can create and compute payslips.
3. Payroll manager can approve payslips.
4. Finance user can mark payslip as paid.
5. Paid payslips cannot be edited.
6. Cancelled payslips cannot be included in batch totals.
7. One employee cannot have duplicate payslip for same payroll period unless one is cancelled.

---

### 5.5 Payslip Batch Workflow

```text
Draft
  ↓ generate payslips
Draft with payslips
  ↓ compute all
Computed
  ↓ close
Closed
```

Rules:

1. Batch must have date_from and date_to.
2. Batch can generate payslips for selected employees or one salary structure.
3. Closed batch cannot be edited.
4. Batch cannot close while any payslip is draft.
5. Batch should show totals for gross, deduction, net, and paid amount.

---

## 6. Database Structure

Use Laravel migrations. All tables must include:

```text
id
created_by
updated_by
created_at
updated_at
deleted_at
```

Use soft deletes for configuration and payroll records.

---

### 6.1 payroll_contracts

Purpose:

Store employee contract details used by payroll.

Columns:

```text
id
company_id
employee_id
department_id
job_position_id
hr_responsible_id
salary_structure_id
contract_name
reference
state enum('new','running','expired','cancelled')
start_date
end_date
notice_period_days
working_schedule_id
employee_category enum('employee','trainee','contractor','consultant')
salary_structure_type
wage decimal(15,2)
currency_id
notes text
active boolean
created_by
updated_by
created_at
updated_at
deleted_at
```

Indexes:

```text
employee_id
company_id
salary_structure_id
state
start_date
end_date
```

Important constraint:

Prevent overlapping running contracts for same employee and company.

---

### 6.2 salary_structures

Columns:

```text
id
company_id
name
reference
parent_id nullable
description text
active boolean
created_by
updated_by
created_at
updated_at
deleted_at
```

Relationships:

```text
hasMany salary_structure_rules
belongsTo company
belongsTo parent salary structure
```

---

### 6.3 salary_rules

Columns:

```text
id
company_id
name
code
category enum('basic','allowance','deduction','gross','net','company_contribution','loan_deduction','tax','other')
sequence integer
active boolean
appears_on_payslip boolean
condition_type enum('always','range','expression')
condition_expression text nullable
condition_range_based_on varchar nullable
condition_min decimal(15,2) nullable
condition_max decimal(15,2) nullable
amount_type enum('fixed','percentage','formula','input')
fixed_amount decimal(15,2) nullable
percentage decimal(8,4) nullable
percentage_based_on varchar nullable
formula text nullable
quantity_formula text nullable
contribution_register_id nullable
note text nullable
created_by
updated_by
created_at
updated_at
deleted_at
```

Indexes:

```text
company_id
code
category
sequence
active
```

Unique:

```text
company_id + code
```

---

### 6.4 salary_structure_rules

Pivot table.

Columns:

```text
id
salary_structure_id
salary_rule_id
sequence integer
created_at
updated_at
```

Unique:

```text
salary_structure_id + salary_rule_id
```

---

### 6.5 contribution_registers

Columns:

```text
id
company_id
name
code
description text
active boolean
created_by
updated_by
created_at
updated_at
deleted_at
```

Used for employer contributions, taxes, insurance, provident fund, and other grouped payroll items.

---

### 6.6 payslips

Columns:

```text
id
company_id
employee_id
contract_id
salary_structure_id
payslip_batch_id nullable
reference
name
state enum('draft','computed','waiting_approval','approved','paid','refused','cancelled')
date_from
date_to
worked_days decimal(8,2)
worked_hours decimal(10,2)
overtime_hours decimal(10,2)
paid_leave_days decimal(8,2)
unpaid_leave_days decimal(8,2)
basic_amount decimal(15,2)
gross_amount decimal(15,2)
deduction_amount decimal(15,2)
company_contribution_amount decimal(15,2)
net_amount decimal(15,2)
paid_amount decimal(15,2)
currency_id
is_credit_note boolean
computed_at timestamp nullable
approved_at timestamp nullable
paid_at timestamp nullable
approved_by nullable
paid_by nullable
notes text
created_by
updated_by
created_at
updated_at
deleted_at
```

Indexes:

```text
employee_id
contract_id
salary_structure_id
payslip_batch_id
state
date_from
date_to
```

Unique active rule:

```text
employee_id + date_from + date_to + company_id where state != cancelled
```

---

### 6.7 payslip_lines

Columns:

```text
id
payslip_id
salary_rule_id nullable
code
name
category
sequence
quantity decimal(12,4)
rate decimal(12,4)
amount decimal(15,2)
total decimal(15,2)
appears_on_payslip boolean
note text nullable
created_at
updated_at
```

Indexes:

```text
payslip_id
salary_rule_id
code
category
sequence
```

---

### 6.8 payslip_batches

Columns:

```text
id
company_id
name
reference
state enum('draft','computed','closed','cancelled')
date_from
date_to
credit_note boolean
salary_structure_id nullable
total_employees integer default 0
total_basic decimal(15,2) default 0
total_gross decimal(15,2) default 0
total_deduction decimal(15,2) default 0
total_net decimal(15,2) default 0
closed_at timestamp nullable
closed_by nullable
created_by
updated_by
created_at
updated_at
deleted_at
```

---

### 6.9 payslip_inputs

Purpose:

Manual input items used in payroll computation.

Columns:

```text
id
payslip_id
code
name
amount decimal(15,2)
description text nullable
created_at
updated_at
```

Example inputs:

```text
Bonus
Commission
Penalty
Reimbursement
Manual Deduction
```

---

### 6.10 payroll_adjustments

Purpose:

Store approved adjustments before payslip computation.

Columns:

```text
id
company_id
employee_id
payslip_id nullable
type enum('bonus','deduction','reimbursement','loan_deduction','penalty','overtime','other')
name
amount decimal(15,2)
effective_date
status enum('draft','approved','used','cancelled')
source_module nullable
source_id nullable
notes text nullable
created_by
approved_by nullable
approved_at nullable
created_at
updated_at
deleted_at
```

---

### 6.11 payroll_settings

Columns:

```text
id
company_id
default_currency_id
default_journal_id nullable
default_salary_structure_id nullable
payroll_approval_required boolean
allow_employee_payslip_download boolean
include_attendance_in_payroll boolean
include_leave_in_payroll boolean
include_timesheet_in_payroll boolean
default_working_days_per_month decimal(8,2)
default_working_hours_per_day decimal(8,2)
created_at
updated_at
```

---

### 6.12 payroll_audit_logs

Columns:

```text
id
company_id
actor_id
entity_type
entity_id
action
old_values json nullable
new_values json nullable
ip_address nullable
user_agent nullable
created_at
```

Track:

```text
salary rule update
salary structure update
contract salary update
payslip compute
payslip approve
payslip paid
batch close
```

---

## 7. Model Relationships

### Contract Model

```php
Contract belongsTo Employee
Contract belongsTo Department
Contract belongsTo JobPosition
Contract belongsTo SalaryStructure
Contract hasMany Payslips
```

### SalaryStructure Model

```php
SalaryStructure hasMany SalaryStructureRule
SalaryStructure belongsToMany SalaryRule
SalaryStructure hasMany Contracts
SalaryStructure hasMany Payslips
```

### SalaryRule Model

```php
SalaryRule belongsToMany SalaryStructure
SalaryRule hasMany PayslipLine
SalaryRule belongsTo ContributionRegister
```

### Payslip Model

```php
Payslip belongsTo Employee
Payslip belongsTo Contract
Payslip belongsTo SalaryStructure
Payslip belongsTo PayslipBatch
Payslip hasMany PayslipLine
Payslip hasMany PayslipInput
```

### PayslipBatch Model

```php
PayslipBatch hasMany Payslip
PayslipBatch belongsTo SalaryStructure
PayslipBatch belongsTo Company
```

---

## 8. Laravel Folder Structure

```text
app/Modules/Payroll/
├── Controllers/
│   ├── ContractPayrollController.php
│   ├── SalaryStructureController.php
│   ├── SalaryRuleController.php
│   ├── ContributionRegisterController.php
│   ├── PayslipController.php
│   ├── PayslipBatchController.php
│   ├── PayrollReportController.php
│   └── PayrollSettingController.php
├── Models/
│   ├── PayrollContract.php
│   ├── SalaryStructure.php
│   ├── SalaryRule.php
│   ├── SalaryStructureRule.php
│   ├── ContributionRegister.php
│   ├── Payslip.php
│   ├── PayslipLine.php
│   ├── PayslipBatch.php
│   ├── PayslipInput.php
│   ├── PayrollAdjustment.php
│   ├── PayrollSetting.php
│   └── PayrollAuditLog.php
├── Requests/
│   ├── StorePayrollContractRequest.php
│   ├── StoreSalaryStructureRequest.php
│   ├── StoreSalaryRuleRequest.php
│   ├── StorePayslipRequest.php
│   ├── StorePayslipBatchRequest.php
│   └── StorePayrollSettingRequest.php
├── Services/
│   ├── PayrollComputationService.php
│   ├── SalaryRuleEngine.php
│   ├── PayslipBatchService.php
│   ├── PayrollEligibilityService.php
│   ├── PayrollNumberService.php
│   ├── PayrollAccountingExportService.php
│   ├── PayrollReportService.php
│   └── PayrollAuditService.php
├── Policies/
│   ├── PayrollContractPolicy.php
│   ├── SalaryStructurePolicy.php
│   ├── SalaryRulePolicy.php
│   ├── PayslipPolicy.php
│   ├── PayslipBatchPolicy.php
│   └── PayrollSettingPolicy.php
├── Jobs/
│   ├── GenerateBatchPayslipsJob.php
│   ├── ComputePayslipJob.php
│   └── SendPayslipNotificationJob.php
├── Events/
│   ├── PayslipComputed.php
│   ├── PayslipApproved.php
│   ├── PayslipPaid.php
│   └── PayrollBatchClosed.php
└── Notifications/
    ├── PayslipApprovedNotification.php
    ├── PayslipPaidNotification.php
    └── PayrollBatchClosedNotification.php
```

Blade / Inertia / Livewire views:

```text
resources/views/modules/payroll/
├── layouts/payroll-app.blade.php
├── contracts/show.blade.php
├── salary-structures/index.blade.php
├── salary-structures/create.blade.php
├── salary-structures/show.blade.php
├── salary-rules/index.blade.php
├── salary-rules/create.blade.php
├── salary-rules/show.blade.php
├── payslips/index.blade.php
├── payslips/create.blade.php
├── payslips/show.blade.php
├── payslip-batches/index.blade.php
├── payslip-batches/create.blade.php
├── payslip-batches/show.blade.php
├── reports/index.blade.php
└── settings/index.blade.php
```

---

## 9. Routes

Use route group:

```php
Route::middleware(['auth', 'verified'])
    ->prefix('payroll')
    ->name('payroll.')
    ->group(function () {
        Route::resource('contracts', ContractPayrollController::class);
        Route::resource('salary-structures', SalaryStructureController::class);
        Route::resource('salary-rules', SalaryRuleController::class);
        Route::resource('contribution-registers', ContributionRegisterController::class);
        Route::resource('payslips', PayslipController::class);
        Route::resource('payslip-batches', PayslipBatchController::class);

        Route::post('payslips/{payslip}/compute', [PayslipController::class, 'compute'])->name('payslips.compute');
        Route::post('payslips/{payslip}/submit', [PayslipController::class, 'submit'])->name('payslips.submit');
        Route::post('payslips/{payslip}/approve', [PayslipController::class, 'approve'])->name('payslips.approve');
        Route::post('payslips/{payslip}/refuse', [PayslipController::class, 'refuse'])->name('payslips.refuse');
        Route::post('payslips/{payslip}/mark-paid', [PayslipController::class, 'markPaid'])->name('payslips.mark-paid');
        Route::post('payslips/{payslip}/cancel', [PayslipController::class, 'cancel'])->name('payslips.cancel');
        Route::get('payslips/{payslip}/pdf', [PayslipController::class, 'pdf'])->name('payslips.pdf');

        Route::post('payslip-batches/{batch}/generate', [PayslipBatchController::class, 'generate'])->name('payslip-batches.generate');
        Route::post('payslip-batches/{batch}/compute-all', [PayslipBatchController::class, 'computeAll'])->name('payslip-batches.compute-all');
        Route::post('payslip-batches/{batch}/close', [PayslipBatchController::class, 'close'])->name('payslip-batches.close');
        Route::post('payslip-batches/{batch}/cancel', [PayslipBatchController::class, 'cancel'])->name('payslip-batches.cancel');

        Route::get('reports', [PayrollReportController::class, 'index'])->name('reports.index');
        Route::get('reports/monthly-summary', [PayrollReportController::class, 'monthlySummary'])->name('reports.monthly-summary');
        Route::get('reports/employee-ledger', [PayrollReportController::class, 'employeeLedger'])->name('reports.employee-ledger');

        Route::get('settings', [PayrollSettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [PayrollSettingController::class, 'update'])->name('settings.update');
    });
```

---

## 10. Permissions

Use Spatie Laravel Permission or a similar package.

Permission list:

```text
payroll.dashboard.view
payroll.contract.view
payroll.contract.create
payroll.contract.update
payroll.contract.delete
payroll.salary_structure.view
payroll.salary_structure.create
payroll.salary_structure.update
payroll.salary_structure.delete
payroll.salary_rule.view
payroll.salary_rule.create
payroll.salary_rule.update
payroll.salary_rule.delete
payroll.payslip.view
payroll.payslip.view_own
payroll.payslip.create
payroll.payslip.update
payroll.payslip.compute
payroll.payslip.approve
payroll.payslip.refuse
payroll.payslip.mark_paid
payroll.payslip.cancel
payroll.payslip.download
payroll.batch.view
payroll.batch.create
payroll.batch.update
payroll.batch.generate
payroll.batch.close
payroll.batch.cancel
payroll.report.view
payroll.settings.manage
```

Suggested roles:

```text
Employee
HR Officer
Payroll Officer
Payroll Manager
Finance Officer
Company Admin
Super Admin
```

Access matrix:

| Feature | Employee | HR Officer | Payroll Officer | Payroll Manager | Finance | Admin |
|---|---:|---:|---:|---:|---:|---:|
| View own payslip | Yes | Yes | Yes | Yes | Yes | Yes |
| View all payslips | No | Yes | Yes | Yes | Yes | Yes |
| Create payslip | No | No | Yes | Yes | No | Yes |
| Compute payslip | No | No | Yes | Yes | No | Yes |
| Approve payslip | No | No | No | Yes | No | Yes |
| Mark paid | No | No | No | No | Yes | Yes |
| Manage salary rules | No | No | No | Yes | No | Yes |
| Manage payroll settings | No | No | No | Yes | No | Yes |

---

## 11. Payroll Computation Rules

### 11.1 Basic Flow

```text
1. Select employee.
2. Find active running contract for period.
3. Load salary structure from contract.
4. Load salary rules by sequence.
5. Pull attendance, leaves, overtime, loans, and manual inputs.
6. Build payroll context.
7. Apply rules one by one.
8. Save payslip lines.
9. Calculate totals.
10. Move payslip to Computed state.
```

### 11.2 Payroll Context Object

The computation service should build this object:

```php
[
    'employee_id' => 1,
    'contract_id' => 1,
    'date_from' => '2022-02-01',
    'date_to' => '2022-02-28',
    'basic' => 0,
    'wage' => 50000,
    'worked_days' => 22,
    'worked_hours' => 176,
    'overtime_hours' => 0,
    'paid_leave_days' => 2,
    'unpaid_leave_days' => 0,
    'gross' => 0,
    'deductions' => 0,
    'net' => 0,
    'allowance_total' => 0,
    'deduction_total' => 0,
]
```

### 11.3 Rule Calculation Example

Basic salary:

```text
category: basic
amount_type: fixed
fixed_amount: contract.wage
```

Medical allowance:

```text
category: allowance
condition_type: always
amount_type: fixed
fixed_amount: 1500
appears_on_payslip: true
```

Provident fund:

```text
category: deduction
condition_type: always
amount_type: percentage
percentage: 12
percentage_based_on: basic
```

Unpaid leave deduction:

```text
category: deduction
amount_type: formula
formula: (basic / working_days) * unpaid_leave_days
```

Net salary:

```text
net = gross - deduction_total
```

---

## 12. Integration With Other Modules

### 12.1 Employee Module

Payroll needs:

```text
Employee profile
Department
Job position
Employee category
Manager
Company
Work email
```

### 12.2 Attendance Module

Payroll can use:

```text
Worked days
Worked hours
Late marks
Overtime hours
Attendance regularization
```

### 12.3 Leaves Module

Payroll can use:

```text
Paid leave days
Unpaid leave days
Leave without pay
Compensatory leave
Extra hours
```

### 12.4 Timesheets Module

Payroll can use:

```text
Billable hours
Project hours
Task hours
Approved timesheets
```

### 12.5 Loan Module

Payroll can use:

```text
Approved loans
Monthly installment deduction
Remaining balance
Paid installment history
```

### 12.6 Accounting Module

Future integration fields:

```text
Journal
Salary expense account
Payable account
Bank account
Treasury account
Tax account
Loan account
```

---

## 13. UI Components

Build reusable components:

```text
TopPurpleNavbar
PayrollMenuTabs
SearchFilterGroupBar
ListTable
KanbanCard
FormCard
StatusPipeline
ActionButtonDropdown
SmartButton
PayslipLineTable
BatchPayslipTable
SalaryRuleTabs
AuditChatter
```

Status pipeline style must match screenshots:

```text
Draft / Running / Closed states shown as right-side purple arrow steps.
```

Buttons:

```text
Primary purple button
White secondary button
Small action dropdown button
```

---

## 14. Reports

### 14.1 Payroll Monthly Summary

Filters:

```text
Company
Month
Department
Salary Structure
Employee
State
```

Columns:

```text
Employee
Department
Basic
Allowance
Gross
Deduction
Net
Paid Amount
Status
```

### 14.2 Salary Rule Report

Columns:

```text
Rule Code
Rule Name
Category
Amount
Employees Applied
Total Amount
```

### 14.3 Employee Payroll Ledger

Columns:

```text
Month
Payslip Reference
Gross
Deductions
Net
Paid Date
Status
```

### 14.4 Batch Payroll Report

Columns:

```text
Batch
Period
Total Employees
Total Gross
Total Deduction
Total Net
State
Closed By
Closed At
```

---

## 15. Validation Rules

### Contract

```text
employee_id required
company_id required
salary_structure_id required for running contract
start_date required
end_date must be after start_date
wage must be >= 0
```

### Salary Structure

```text
name required
reference required
reference unique per company
at least one salary rule before use
```

### Salary Rule

```text
name required
code required unique per company
category required
sequence required integer
amount_type required
fixed amount required if fixed
percentage required if percentage
formula required if formula
```

### Payslip

```text
employee_id required
contract_id required
date_from required
date_to required and after date_from
no duplicate active payslip for same employee and period
```

### Batch

```text
name required
date_from required
date_to required and after date_from
cannot close if no payslips
cannot close if any payslip is draft
```

---

## 16. Seed Data

Create seeders for:

```text
PayrollPermissionSeeder
PayrollRoleSeeder
ContributionRegisterSeeder
DefaultSalaryRuleSeeder
DefaultSalaryStructureSeeder
PayrollSettingSeeder
DemoContractSeeder
DemoPayslipBatchSeeder
```

Default salary rules:

```text
BASIC - Basic Salary
HRA - House Rent Allowance
MED - Medical Allowance
TRAVEL - Travel Allowance
GROSS - Gross Salary
PF - Provident Fund
TAX - Tax Deduction
LOAN - Loan Deduction
NET - Net Salary
```

Default salary structures:

```text
BASE - Base for new structures
ME - Marketing Executive
MEGG - Marketing Executive for Gilles Grave
```

---

## 17. Testing Plan

### Unit Tests

```text
Salary rule fixed amount calculation
Salary rule percentage calculation
Salary rule formula calculation
Unpaid leave deduction
Loan installment deduction
Gross salary calculation
Net salary calculation
```

### Feature Tests

```text
Payroll officer can create payslip
Payroll officer can compute payslip
Payroll manager can approve payslip
Finance officer can mark payslip as paid
Employee can view own payslip only
Batch can generate payslips
Batch cannot close with draft payslips
Used salary rule cannot be hard deleted
```

### Browser Tests

```text
Salary structures list loads
Salary rule form tabs work
Payslip batch form generates payslips
Status pipeline updates after actions
Payslip PDF downloads
```

---

## 18. Implementation Phases

### Phase 1 — Core Payroll Configuration

Build:

```text
salary_structures
salary_rules
salary_structure_rules
contribution_registers
payroll_settings
```

Screens:

```text
Salary Structures list
Salary Structure form
Salary Rules list
Salary Rule form
```

---

### Phase 2 — Employee Contracts

Build:

```text
payroll_contracts
contract form
contract status flow
salary information tab
```

---

### Phase 3 — Payslip Generation

Build:

```text
payslips
payslip_lines
payslip_inputs
PayrollComputationService
SalaryRuleEngine
```

Actions:

```text
Create
Compute
Submit
Approve
Refuse
Mark Paid
Cancel
```

---

### Phase 4 — Payslip Batches

Build:

```text
payslip_batches
batch generation
batch compute all
batch close
batch summary totals
```

---

### Phase 5 — Integrations

Connect:

```text
Attendance
Leaves
Timesheets
Loans
Accounting export
Notifications
```

---

### Phase 6 — Reports and Polish

Build:

```text
Monthly summary
Employee ledger
Salary rule report
Batch report
PDF payslip
Export CSV / XLSX
Audit logs
```

---

## 19. Coding Rules For Agents

1. Do not mix Payroll logic inside controllers.
2. Put calculations inside `PayrollComputationService` and `SalaryRuleEngine`.
3. Never use raw `eval` for formula calculation.
4. All money fields must use decimal, not float.
5. Every state change must create an audit log.
6. Every approval action must verify permission.
7. Use database transactions for payslip compute, batch generate, approve, and mark paid.
8. Use soft deletes for all payroll records.
9. Do not delete paid payslips.
10. Do not edit paid payslips.
11. Do not allow duplicate payslip for same employee and same period.
12. Keep UI close to Odoo/Open HRMS style but implement clean Laravel code.

---

## 20. Final Expected Outcome

At the end of this module, the Laravel HRMS should support:

```text
Payroll configuration
Employee contracts
Salary structure setup
Salary rule setup
Single payslip generation
Batch payslip generation
Payroll approval
Payroll payment marking
Payroll reports
Payroll integration hooks
```

The user should be able to create a contract, attach salary structure, define salary rules, generate payroll for one employee or a batch, approve it, and mark it as paid.
