# AGENTS.md — Loan Management Module

## 1. Module Goal

Build a Laravel-based Loan Management module inspired by the HRMS loan workflow shown in the screenshots.

The module allows employees to request company loans, compute installment schedules, submit requests for approval, and let managers or HR approve or refuse the loan. On approval, finance-related fields such as Treasury Account, Loan Account, and Journal must be captured.

This module must connect with the Employee Database module and later with Payroll, Accounting, Notifications, and Reporting.

---

## 2. Main Screens from Dashboard Analysis

### 2.1 Top Navigation

Main app area: `Employees`

Visible menu items:

- Employees
- Document Templates
- Departments
- Services
- Insurance
- Legal Actions
- Plus menu
- Company switcher
- User profile menu

The Loan module can be placed under:

`Employees → Services → Loans`

or

`Employees → Loans & Advances`

Recommended Laravel route prefix:

```txt
/hr/loans
```

---

## 3. Main Workflow

### 3.1 Loan Request States

The screenshots show a clear 3-step status bar:

```txt
Draft → Submitted → Approved
```

Add `Refused` and `Cancelled` internally for better control.

Recommended enum:

```php
loan_status = [
    'draft',
    'submitted',
    'approved',
    'refused',
    'cancelled',
    'closed'
]
```

### 3.2 Workflow Actions

| Current Status | Action | New Status | Allowed Role |
|---|---|---|---|
| Draft | Save | Draft | Employee / HR |
| Draft | Compute Installment | Draft | Employee / HR |
| Draft | Submit | Submitted | Employee / HR |
| Draft | Cancel | Cancelled | Employee / HR |
| Submitted | Approve | Approved | HR Manager / Finance / Admin |
| Submitted | Refuse | Refused | HR Manager / Finance / Admin |
| Approved | Mark installment paid | Approved / Closed | Finance / Admin |
| Approved | Close loan | Closed | Finance / Admin |

### 3.3 Approval Checklist

Before approval, system must check:

- Employee must be active.
- Employee must be working for at least 6 months.
- Loan amount must be greater than 0.
- Number of installments must be greater than 0.
- Payment start date must be set.
- Installment schedule must be computed.
- Treasury Account must be selected.
- Loan Account must be selected.
- Journal must be selected.
- Employee must not have blocked or overdue loan rules, if enabled.

---

## 4. Screen Structure

### 4.1 Create Loan Request Screen

Page title:

```txt
Request for Loan / New
```

Header buttons:

- Save
- Discard
- Compute Installment
- Submit
- Cancel

Status bar:

```txt
Draft | Submitted | Approved
```

Form layout:

Left column:

- Employee
- Department
- Loan Amount
- No Of Installments
- Company

Right column:

- Date
- Job Position
- Payment Start Date
- Currency

Installments tab:

- Payment Date
- Amount
- Add a line

Footer calculation area:

- Total Amount
- Total Paid Amount
- Balance Amount

### 4.2 View Loan Request Screen

Page title example:

```txt
Request for Loan / LO/0001
```

Header buttons:

- Edit
- Create
- Action
- Compute Installment
- Submit / Approve / Refuse depending on state

Record body:

- Loan sequence number: `LO/0001`
- Employee
- Department
- Loan Amount
- Treasury Account
- No Of Installments
- Company
- Date
- Job Position
- Loan Account
- Journal
- Payment Start Date
- Currency

Installments table:

- Payment Date
- Amount
- Paid Amount
- Remaining Amount
- Payment Status
- Paid Date
- Notes

Footer:

- Total Amount
- Total Paid Amount
- Balance Amount

---

## 5. Agents and Responsibilities

### 5.1 Module Owner Agent

Responsible for:

- Keeping the module aligned with the HRMS clone structure.
- Checking that naming, routes, permissions, and UI stay consistent.
- Ensuring loan data is connected to employees, departments, companies, and payroll.

Output files:

```txt
app/Modules/Loans/
routes/hr_loans.php
resources/views/hr/loans/
database/migrations/*loan*.php
```

---

### 5.2 UI Agent

Builds the Blade or Inertia screens.

Screens:

```txt
resources/views/hr/loans/index.blade.php
resources/views/hr/loans/create.blade.php
resources/views/hr/loans/edit.blade.php
resources/views/hr/loans/show.blade.php
resources/views/hr/loans/partials/form.blade.php
resources/views/hr/loans/partials/installments.blade.php
resources/views/hr/loans/partials/status-bar.blade.php
```

UI rules:

- Use purple primary buttons to match Open HRMS look.
- Use white card body with light grey page background.
- Use status bar on the top right.
- Use two-column form layout.
- Keep Installments as a tab below the main loan form.
- Show totals at the lower right.
- Show read-only fields in view mode.
- In edit mode, show input/select/date fields.
- Disable approval buttons for unauthorized users.

Important UI components:

- Employee dropdown with search.
- Department auto-filled from employee.
- Job position auto-filled from employee.
- Company auto-filled from employee.
- Currency dropdown.
- Date picker.
- Installment grid.
- Status ribbon.
- Action dropdown.

---

### 5.3 Database Agent

Create database schema.

#### 5.3.1 `employee_loans`

```php
Schema::create('employee_loans', function (Blueprint $table) {
    $table->id();
    $table->string('loan_number')->unique();

    $table->foreignId('employee_id')->constrained('employees');
    $table->foreignId('department_id')->nullable()->constrained('departments');
    $table->foreignId('job_position_id')->nullable()->constrained('job_positions');
    $table->foreignId('company_id')->nullable()->constrained('companies');

    $table->date('request_date');
    $table->decimal('loan_amount', 15, 2);
    $table->unsignedInteger('number_of_installments');
    $table->date('payment_start_date');
    $table->string('currency_code', 10)->default('USD');

    $table->foreignId('treasury_account_id')->nullable()->constrained('accounts');
    $table->foreignId('loan_account_id')->nullable()->constrained('accounts');
    $table->foreignId('journal_id')->nullable()->constrained('journals');

    $table->enum('status', [
        'draft',
        'submitted',
        'approved',
        'refused',
        'cancelled',
        'closed'
    ])->default('draft');

    $table->decimal('total_amount', 15, 2)->default(0);
    $table->decimal('total_paid_amount', 15, 2)->default(0);
    $table->decimal('balance_amount', 15, 2)->default(0);

    $table->text('reason')->nullable();
    $table->text('notes')->nullable();
    $table->string('attachment_path')->nullable();

    $table->foreignId('submitted_by')->nullable()->constrained('users');
    $table->timestamp('submitted_at')->nullable();
    $table->foreignId('approved_by')->nullable()->constrained('users');
    $table->timestamp('approved_at')->nullable();
    $table->foreignId('refused_by')->nullable()->constrained('users');
    $table->timestamp('refused_at')->nullable();
    $table->text('refusal_reason')->nullable();

    $table->foreignId('created_by')->nullable()->constrained('users');
    $table->foreignId('updated_by')->nullable()->constrained('users');
    $table->timestamps();
    $table->softDeletes();
});
```

#### 5.3.2 `employee_loan_installments`

```php
Schema::create('employee_loan_installments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_loan_id')->constrained('employee_loans')->cascadeOnDelete();
    $table->unsignedInteger('installment_no');
    $table->date('payment_date');
    $table->decimal('amount', 15, 2);
    $table->decimal('paid_amount', 15, 2)->default(0);
    $table->decimal('remaining_amount', 15, 2)->default(0);
    $table->enum('status', ['pending', 'partial', 'paid', 'skipped'])->default('pending');
    $table->date('paid_date')->nullable();
    $table->foreignId('paid_by')->nullable()->constrained('users');
    $table->text('notes')->nullable();
    $table->timestamps();
});
```

#### 5.3.3 `employee_loan_activities`

```php
Schema::create('employee_loan_activities', function (Blueprint $table) {
    $table->id();
    $table->foreignId('employee_loan_id')->constrained('employee_loans')->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained('users');
    $table->string('activity_type');
    $table->string('from_status')->nullable();
    $table->string('to_status')->nullable();
    $table->text('message')->nullable();
    $table->json('meta')->nullable();
    $table->timestamps();
});
```

#### 5.3.4 `loan_settings`

```php
Schema::create('loan_settings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->nullable()->constrained('companies');
    $table->unsignedInteger('minimum_working_months')->default(6);
    $table->decimal('max_loan_amount', 15, 2)->nullable();
    $table->unsignedInteger('max_installments')->nullable();
    $table->boolean('allow_multiple_active_loans')->default(false);
    $table->boolean('require_attachment')->default(false);
    $table->boolean('payroll_deduction_enabled')->default(true);
    $table->timestamps();
});
```

---

## 6. Models and Relationships

### 6.1 EmployeeLoan Model

```php
class EmployeeLoan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'loan_number',
        'employee_id',
        'department_id',
        'job_position_id',
        'company_id',
        'request_date',
        'loan_amount',
        'number_of_installments',
        'payment_start_date',
        'currency_code',
        'treasury_account_id',
        'loan_account_id',
        'journal_id',
        'status',
        'total_amount',
        'total_paid_amount',
        'balance_amount',
        'reason',
        'notes',
        'attachment_path',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'refused_by',
        'refused_at',
        'refusal_reason',
        'created_by',
        'updated_by',
    ];

    public function employee() { return $this->belongsTo(Employee::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function jobPosition() { return $this->belongsTo(JobPosition::class); }
    public function company() { return $this->belongsTo(Company::class); }
    public function installments() { return $this->hasMany(EmployeeLoanInstallment::class); }
    public function activities() { return $this->hasMany(EmployeeLoanActivity::class); }
}
```

---

## 7. Controller Agent

Create controllers:

```txt
app/Http/Controllers/Hr/LoanController.php
app/Http/Controllers/Hr/LoanInstallmentController.php
app/Http/Controllers/Hr/LoanApprovalController.php
app/Http/Controllers/Hr/LoanReportController.php
app/Http/Controllers/Hr/LoanSettingController.php
```

### 7.1 LoanController Methods

```php
index()
create()
store(StoreEmployeeLoanRequest $request)
show(EmployeeLoan $loan)
edit(EmployeeLoan $loan)
update(UpdateEmployeeLoanRequest $request, EmployeeLoan $loan)
destroy(EmployeeLoan $loan)
```

### 7.2 LoanApprovalController Methods

```php
computeInstallment(EmployeeLoan $loan)
submit(EmployeeLoan $loan)
approve(ApproveLoanRequest $request, EmployeeLoan $loan)
refuse(RefuseLoanRequest $request, EmployeeLoan $loan)
cancel(EmployeeLoan $loan)
close(EmployeeLoan $loan)
```

### 7.3 LoanInstallmentController Methods

```php
markPaid(EmployeeLoanInstallment $installment)
markPartial(EmployeeLoanInstallment $installment)
resetPayment(EmployeeLoanInstallment $installment)
```

---

## 8. Service Agent

Create services:

```txt
app/Services/Loans/LoanNumberService.php
app/Services/Loans/LoanInstallmentService.php
app/Services/Loans/LoanApprovalService.php
app/Services/Loans/LoanEligibilityService.php
app/Services/Loans/LoanBalanceService.php
app/Services/Loans/LoanActivityService.php
```

### 8.1 LoanNumberService

Generates sequence:

```txt
LO/0001
LO/0002
LO/0003
```

Rules:

- Sequence must be unique.
- Prefix should be configurable later.
- Number should be assigned on create.

### 8.2 LoanInstallmentService

Responsibilities:

- Generate installment rows after clicking `Compute Installment`.
- Divide loan amount by number of installments.
- Round last installment to fix decimal differences.
- Use monthly dates from payment start date.

Example:

Loan amount: 6000  
Installments: 3  
Start date: 31/03/2022

Generated rows:

| Payment Date | Amount |
|---|---:|
| 31/03/2022 | 2000.00 |
| 30/04/2022 | 2000.00 |
| 30/05/2022 | 2000.00 |

### 8.3 LoanEligibilityService

Checks:

- Employee joining date.
- Minimum 6 months employment.
- Active employee status.
- Existing loan restrictions.
- Max amount rules.
- Max installment rules.

### 8.4 LoanApprovalService

Responsibilities:

- Validate status transition.
- Validate approval checklist.
- Save treasury account, loan account, and journal.
- Mark loan approved.
- Send notification to employee.
- Create activity log.
- Prepare future payroll deduction entries if payroll module exists.

---

## 9. Request Validation Agent

Create request classes:

```txt
app/Http/Requests/Hr/Loans/StoreEmployeeLoanRequest.php
app/Http/Requests/Hr/Loans/UpdateEmployeeLoanRequest.php
app/Http/Requests/Hr/Loans/ApproveLoanRequest.php
app/Http/Requests/Hr/Loans/RefuseLoanRequest.php
```

### 9.1 Store Rules

```php
return [
    'employee_id' => ['required', 'exists:employees,id'],
    'loan_amount' => ['required', 'numeric', 'min:1'],
    'number_of_installments' => ['required', 'integer', 'min:1'],
    'payment_start_date' => ['required', 'date'],
    'currency_code' => ['required', 'string', 'max:10'],
    'reason' => ['nullable', 'string'],
    'attachment' => ['nullable', 'file', 'max:5120'],
];
```

### 9.2 Approval Rules

```php
return [
    'treasury_account_id' => ['required', 'exists:accounts,id'],
    'loan_account_id' => ['required', 'exists:accounts,id'],
    'journal_id' => ['required', 'exists:journals,id'],
];
```

### 9.3 Refuse Rules

```php
return [
    'refusal_reason' => ['required', 'string', 'max:1000'],
];
```

---

## 10. Routes Agent

Add route file:

```txt
routes/hr_loans.php
```

Routes:

```php
Route::middleware(['auth'])->prefix('hr/loans')->name('hr.loans.')->group(function () {
    Route::get('/', [LoanController::class, 'index'])->name('index');
    Route::get('/create', [LoanController::class, 'create'])->name('create');
    Route::post('/', [LoanController::class, 'store'])->name('store');
    Route::get('/{loan}', [LoanController::class, 'show'])->name('show');
    Route::get('/{loan}/edit', [LoanController::class, 'edit'])->name('edit');
    Route::put('/{loan}', [LoanController::class, 'update'])->name('update');
    Route::delete('/{loan}', [LoanController::class, 'destroy'])->name('destroy');

    Route::post('/{loan}/compute-installment', [LoanApprovalController::class, 'computeInstallment'])->name('compute-installment');
    Route::post('/{loan}/submit', [LoanApprovalController::class, 'submit'])->name('submit');
    Route::post('/{loan}/approve', [LoanApprovalController::class, 'approve'])->name('approve');
    Route::post('/{loan}/refuse', [LoanApprovalController::class, 'refuse'])->name('refuse');
    Route::post('/{loan}/cancel', [LoanApprovalController::class, 'cancel'])->name('cancel');
    Route::post('/{loan}/close', [LoanApprovalController::class, 'close'])->name('close');

    Route::post('/installments/{installment}/paid', [LoanInstallmentController::class, 'markPaid'])->name('installments.paid');
    Route::post('/installments/{installment}/partial', [LoanInstallmentController::class, 'markPartial'])->name('installments.partial');

    Route::get('/reports/summary', [LoanReportController::class, 'summary'])->name('reports.summary');
    Route::get('/settings/general', [LoanSettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings/general', [LoanSettingController::class, 'update'])->name('settings.update');
});
```

---

## 11. Permissions Agent

Use Spatie Laravel Permission or custom policy system.

Required permissions:

```txt
loan.view
loan.create
loan.edit_own
loan.edit_all
loan.delete
loan.submit
loan.approve
loan.refuse
loan.cancel
loan.close
loan.mark_paid
loan.report
loan.settings
```

Roles:

### Employee

- Can create own loan request.
- Can view own loans.
- Can edit only draft own loans.
- Can submit own draft loans.
- Cannot approve or refuse.

### Manager

- Can view subordinate loan requests.
- Can approve if company policy allows manager approval.
- Can refuse subordinate requests.

### HR Manager

- Can view all loans.
- Can approve or refuse.
- Can edit submitted loans before approval.
- Can access reports.

### Finance Officer

- Can view approved loans.
- Can set accounts and journal.
- Can mark installments as paid.
- Can access finance report.

### Admin

- Full access.

---

## 12. Policy Agent

Create:

```txt
app/Policies/EmployeeLoanPolicy.php
```

Policy methods:

```php
viewAny(User $user)
view(User $user, EmployeeLoan $loan)
create(User $user)
update(User $user, EmployeeLoan $loan)
delete(User $user, EmployeeLoan $loan)
submit(User $user, EmployeeLoan $loan)
approve(User $user, EmployeeLoan $loan)
refuse(User $user, EmployeeLoan $loan)
markPaid(User $user, EmployeeLoan $loan)
settings(User $user)
```

Important rules:

- Employee can only see own loan unless manager role applies.
- Draft loan can be edited by creator or HR.
- Submitted loan cannot be edited by employee.
- Approved loan cannot be edited except payment details by Finance/Admin.
- Refused loan cannot be approved unless moved back to draft by Admin.

---

## 13. Notification Agent

Create notifications:

```txt
app/Notifications/Loans/LoanSubmittedNotification.php
app/Notifications/Loans/LoanApprovedNotification.php
app/Notifications/Loans/LoanRefusedNotification.php
app/Notifications/Loans/LoanInstallmentDueNotification.php
app/Notifications/Loans/LoanInstallmentPaidNotification.php
```

Notification channels:

- Database notification
- Email
- Optional SMS later

Events:

| Event | Notify |
|---|---|
| Loan submitted | HR Manager / Manager |
| Loan approved | Employee |
| Loan refused | Employee |
| Installment due soon | Employee / Payroll / Finance |
| Installment paid | Employee / Finance |

---

## 14. Reporting Agent

Reports needed:

### 14.1 Loan Summary Report

Columns:

- Loan Number
- Employee
- Department
- Loan Amount
- Total Paid
- Balance
- Status
- Approved Date

Filters:

- Employee
- Department
- Company
- Status
- Date range
- Balance greater than zero

### 14.2 Installment Due Report

Columns:

- Employee
- Loan Number
- Installment No
- Payment Date
- Amount
- Paid Amount
- Remaining Amount
- Status

Filters:

- Due this month
- Overdue
- Paid
- Pending

### 14.3 Export

Support:

- Excel
- CSV
- PDF later

---

## 15. Payroll Integration Agent

The Loan module must be ready for Payroll integration.

On approval:

- Create expected deduction schedule.
- Link installment to payroll period when payroll module exists.
- Deduct installment amount from payslip.
- Mark installment paid when payslip is confirmed.

Recommended future table:

```txt
payroll_deductions
```

Fields:

- employee_id
- employee_loan_id
- employee_loan_installment_id
- payslip_id
- deduction_amount
- status

Do not hard-code payroll logic in LoanController. Keep payroll integration in a separate service.

---

## 16. Accounting Integration Agent

The screenshots show these approval-time accounting fields:

- Treasury Account
- Loan Account
- Journal

Rules:

- These fields are required before approval.
- Accounting entries are optional in first phase.
- Save account references even if full accounting module is not ready.
- When accounting module is active, create journal entry on approval.

Suggested accounting entry:

```txt
Debit: Employee Loan Account
Credit: Treasury Account
Amount: Loan Amount
Journal: Selected Journal
```

On repayment:

```txt
Debit: Treasury Account
Credit: Employee Loan Account
Amount: Installment Amount
```

---

## 17. Search, Filter, and List Agent

Loan list screen must support:

Search:

- Loan number
- Employee name
- Department
- Job position

Filters:

- Draft
- Submitted
- Approved
- Refused
- Closed
- My Loans
- My Department
- Overdue Loans
- Active Balance

Group By:

- Status
- Department
- Company
- Employee
- Payment month

Views:

- List view
- Kanban view later
- Calendar due view later

---

## 18. Seeder Agent

Create demo data:

```txt
LO/0001
Employee: Jeffrey Kelly
Department: Sales
Job Position: Marketing and Community Manager
Loan Amount: 6000.00
No Of Installments: 3
Payment Start Date: 2022-03-31
Currency: USD
Status: submitted
```

Installments:

| Payment Date | Amount |
|---|---:|
| 2022-03-31 | 2000.00 |
| 2022-04-30 | 2000.00 |
| 2022-05-30 | 2000.00 |

Also create:

- one draft loan
- one approved loan
- one refused loan
- one partially paid loan

---

## 19. Testing Agent

Feature tests:

```txt
tests/Feature/Hr/Loans/CreateLoanTest.php
tests/Feature/Hr/Loans/ComputeInstallmentTest.php
tests/Feature/Hr/Loans/SubmitLoanTest.php
tests/Feature/Hr/Loans/ApproveLoanTest.php
tests/Feature/Hr/Loans/RefuseLoanTest.php
tests/Feature/Hr/Loans/LoanPolicyTest.php
tests/Feature/Hr/Loans/LoanReportTest.php
```

Test cases:

- Employee can create draft loan.
- Employee cannot approve loan.
- Employee cannot view another employee loan.
- Installments are generated correctly.
- Last installment fixes rounding difference.
- Loan cannot be submitted without installments.
- Loan cannot be approved if employee has worked less than 6 months.
- Loan cannot be approved without treasury account.
- Loan cannot be approved without loan account.
- Loan cannot be approved without journal.
- Approved loan balance is correct.
- Paid installment updates total paid and balance.

---

## 20. Implementation Phases

### Phase 1 — Basic Loan CRUD

- Database migrations
- Models
- Loan create/edit/show pages
- List page
- Basic permissions

### Phase 2 — Installment Computation

- Compute installment button
- Installment service
- Installment table
- Total, paid, and balance calculation

### Phase 3 — Approval Workflow

- Submit
- Approve
- Refuse
- Status bar
- Approval checklist
- Activity log

### Phase 4 — Finance Fields

- Treasury Account
- Loan Account
- Journal
- Approval validation
- Basic accounting references

### Phase 5 — Reports and Export

- Summary report
- Installment due report
- Excel/CSV export

### Phase 6 — Payroll Integration

- Deduction schedule
- Payslip deduction linkage
- Auto installment paid from payroll

---

## 21. Coding Rules for Agents

- Keep business logic out of controllers.
- Use services for calculation and approval.
- Use policies for access control.
- Use form request classes for validation.
- Keep Blade partials small.
- Use database transactions for approval and installment payment.
- Never delete approved loans permanently.
- Use soft delete for loan header records.
- Log every status change.
- Do not allow employee to edit submitted or approved loan.
- Keep money fields as decimal, never float.
- Use company-level settings for rules.

---

## 22. Acceptance Criteria

The module is complete when:

- Employee can create a loan request.
- Employee can enter loan amount, installments, payment start date, and currency.
- System can compute installment schedule.
- Employee can submit the request.
- HR or Finance can approve or refuse.
- Approval requires Treasury Account, Loan Account, and Journal.
- Employee with less than 6 months of work cannot be approved.
- Installments are visible in a table.
- Total amount, paid amount, and balance are calculated correctly.
- Proper role permissions are applied.
- Activity log stores all workflow changes.
- Reports show active, approved, pending, and due loan data.
