<?php

use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeePortalController;
use App\Http\Controllers\HrAdministrationController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MasterSettingController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProjectTaskController;
use App\Http\Controllers\TimesheetController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [SessionController::class, 'create'])->name('login');
    Route::post('/login', [SessionController::class, 'store'])->name('login.store');
});

Route::post('/logout', [SessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'access:employee'])->prefix('employee')->name('employee.')->group(function (): void {
    Route::get('/dashboard', [EmployeePortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile/edit', [EmployeePortalController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile', [EmployeePortalController::class, 'updateProfile'])->name('profile.update');
});

Route::get('/', DashboardController::class)->middleware('auth')->name('dashboard');

Route::middleware(['auth', 'access:super_admin'])->group(function (): void {
    Route::get('/organization', [OrganizationController::class, 'index'])->name('organization.index');
    Route::post('/organization/companies', [OrganizationController::class, 'storeCompany'])->name('organization.companies.store');
    Route::post('/organization/departments', [OrganizationController::class, 'storeDepartment'])->name('organization.departments.store');
    Route::post('/organization/job-positions', [OrganizationController::class, 'storeJobPosition'])->name('organization.job-positions.store');
    Route::get('/organization/job-positions/{jobPosition}/edit', [OrganizationController::class, 'editJobPosition'])->name('organization.job-positions.edit');
    Route::put('/organization/job-positions/{jobPosition}', [OrganizationController::class, 'updateJobPosition'])->name('organization.job-positions.update');
    Route::delete('/organization/job-positions/{jobPosition}', [OrganizationController::class, 'destroyJobPosition'])->name('organization.job-positions.destroy');

    Route::prefix('admin/hr')->name('hr-admin.')->group(function (): void {
        Route::get('/departments', [HrAdministrationController::class, 'departments'])->name('departments.index');
        Route::get('/employees', [HrAdministrationController::class, 'employees'])->name('employees.index');
        Route::get('/announcements/create', [HrAdministrationController::class, 'announcementCreate'])->name('announcements.create');
    });

Route::patch('/employees/{employee}/archive', [EmployeeController::class, 'archive'])->name('employees.archive');
Route::patch('/employees/{employee}/restore', [EmployeeController::class, 'restore'])->name('employees.restore');
Route::get('/employees/{employee}/documents/create', [EmployeeController::class, 'createDocument'])->name('employees.documents.create');
Route::post('/employees/{employee}/documents', [EmployeeController::class, 'storeDocument'])->name('employees.documents.store');
Route::get('/employees/{employee}/cv/download', [EmployeeController::class, 'downloadCv'])->name('employees.cv.download');
Route::get('/employees/{employee}/documents/{documentIndex}/download', [EmployeeController::class, 'downloadDocument'])->name('employees.documents.download');
Route::get('/employees/{employee}/timesheets', [EmployeeController::class, 'timesheets'])->name('employees.timesheets.index');
Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
Route::post('/loans/bulk-delete', [LoanController::class, 'bulkDelete'])->name('loans.bulk-delete');
Route::get('/loans/create', [LoanController::class, 'create'])->name('loans.create');
Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
Route::get('/loans/{loan}/edit', [LoanController::class, 'edit'])->name('loans.edit');
Route::put('/loans/{loan}', [LoanController::class, 'update'])->name('loans.update');
Route::get('/loans/{loan}', [LoanController::class, 'show'])->name('loans.show');
Route::post('/loans/{loan}/compute-installments', [LoanController::class, 'computeInstallments'])->name('loans.compute-installments');
Route::post('/loans/{loan}/submit', [LoanController::class, 'submit'])->name('loans.submit');
Route::post('/loans/{loan}/approve', [LoanController::class, 'approve'])->name('loans.approve');
Route::post('/loans/{loan}/refuse', [LoanController::class, 'refuse'])->name('loans.refuse');
Route::post('/loans/{loan}/cancel', [LoanController::class, 'cancel'])->name('loans.cancel');
Route::resource('employees', EmployeeController::class)->except(['destroy']);

Route::get('/leaves/types', [LeaveController::class, 'types'])->name('leaves.types');
Route::get('/leaves/requests', [LeaveController::class, 'requests'])->name('leaves.requests');
Route::get('/leaves/requests/create', [LeaveController::class, 'createRequest'])->name('leaves.requests.create');
Route::post('/leaves/requests', [LeaveController::class, 'storeRequest'])->name('leaves.requests.store');
Route::get('/leaves/requests/{leaveRequest}', [LeaveController::class, 'showRequest'])->name('leaves.requests.show');
Route::patch('/leaves/requests/{leaveRequest}/approve', [LeaveController::class, 'approve'])->name('leaves.requests.approve');
Route::patch('/leaves/requests/{leaveRequest}/refuse', [LeaveController::class, 'refuse'])->name('leaves.requests.refuse');
Route::patch('/leaves/requests/{leaveRequest}/draft', [LeaveController::class, 'markDraft'])->name('leaves.requests.draft');
Route::get('/leaves/settings', [LeaveController::class, 'settings'])->name('leaves.settings');
Route::post('/leaves/settings', [LeaveController::class, 'updateSettings'])->name('leaves.settings.update');
Route::get('/settings/master', [MasterSettingController::class, 'edit'])->name('settings.master.edit');
Route::post('/settings/master', [MasterSettingController::class, 'update'])->name('settings.master.update');

Route::get('/employees/view/{view}', [EmployeeController::class, 'view'])->name('employees.view');
Route::post('/employees/bulk-action', [EmployeeController::class, 'bulkAction'])->name('employees.bulk-action');

Route::prefix('payroll')->name('payroll.')->group(function (): void {
    Route::get('/contracts', [PayrollController::class, 'contracts'])->name('contracts.index');
    Route::get('/contracts/create', [PayrollController::class, 'createContract'])->name('contracts.create');
    Route::post('/contracts', [PayrollController::class, 'storeContract'])->name('contracts.store');
    Route::get('/salary-structures', [PayrollController::class, 'structures'])->name('structures.index');
    Route::get('/salary-structures/create', [PayrollController::class, 'createStructure'])->name('structures.create');
    Route::post('/salary-structures', [PayrollController::class, 'storeStructure'])->name('structures.store');
    Route::get('/salary-rules', [PayrollController::class, 'rules'])->name('rules.index');
    Route::get('/salary-rules/create', [PayrollController::class, 'createRule'])->name('rules.create');
    Route::post('/salary-rules', [PayrollController::class, 'storeRule'])->name('rules.store');
    Route::get('/salary-rules/{rule}/edit', [PayrollController::class, 'editRule'])->name('rules.edit');
    Route::put('/salary-rules/{rule}', [PayrollController::class, 'updateRule'])->name('rules.update');
    Route::get('/payslip-batches', [PayrollController::class, 'batches'])->name('batches.index');
    Route::get('/payslip-batches/create', [PayrollController::class, 'createBatch'])->name('batches.create');
    Route::post('/payslip-batches', [PayrollController::class, 'storeBatch'])->name('batches.store');
    Route::get('/salary-rules/{rule}', [PayrollController::class, 'rule'])->name('rules.show');
    Route::get('/payslip-batches/{batch}', [PayrollController::class, 'batch'])->name('batches.show');
    Route::post('/payslip-batches/{batch}/compute', [PayrollController::class, 'computeBatch'])->name('batches.compute');
    Route::post('/payslip-batches/{batch}/approve', [PayrollController::class, 'approveBatch'])->name('batches.approve');
    Route::post('/payslip-batches/{batch}/close', [PayrollController::class, 'closeBatch'])->name('batches.close');
    Route::get('/payslips/{payslip}/download', [PayrollController::class, 'downloadPayslip'])->name('payslips.download');
    Route::get('/contracts/{contract}', [PayrollController::class, 'contract'])->name('contracts.show');
    Route::get('/contribution-registers', [PayrollController::class, 'registers'])->name('registers.index');
    Route::post('/contribution-registers', [PayrollController::class, 'storeRegister'])->name('registers.store');
    Route::get('/settings', [PayrollController::class, 'settings'])->name('settings.edit');
    Route::post('/settings', [PayrollController::class, 'updateSettings'])->name('settings.update');
});

Route::prefix('timesheets')->name('timesheets.')->group(function (): void {
    Route::get('/', [TimesheetController::class, 'index'])->name('index');
    Route::get('/export/csv', [TimesheetController::class, 'exportCsv'])->name('export.csv');
    Route::get('/reports/employee-summary', [TimesheetController::class, 'employeeSummary'])->name('reports.employee');
    Route::get('/reports/project-summary', [TimesheetController::class, 'projectSummary'])->name('reports.project');
    Route::get('/reports/task-summary', [TimesheetController::class, 'taskSummary'])->name('reports.task');
    Route::get('/settings', [TimesheetController::class, 'settings'])->name('settings.edit');
    Route::post('/settings', [TimesheetController::class, 'updateSettings'])->name('settings.update');
    Route::post('/projects', [TimesheetController::class, 'storeProject'])->name('projects.store');
    Route::post('/projects/{project}/assignments', [TimesheetController::class, 'updateProjectAssignments'])->name('projects.assignments.update');
    Route::get('/{timesheet}', [TimesheetController::class, 'show'])->name('show');
    Route::get('/{timesheet}/edit', [TimesheetController::class, 'edit'])->name('edit');
    Route::put('/{timesheet}', [TimesheetController::class, 'update'])->name('update');
    Route::delete('/{timesheet}', [TimesheetController::class, 'destroy'])->name('destroy');
    Route::post('/{timesheet}/submit', [TimesheetController::class, 'submit'])->name('submit');
    Route::post('/{timesheet}/approve', [TimesheetController::class, 'approve'])->name('approve');
    Route::post('/{timesheet}/reject', [TimesheetController::class, 'reject'])->name('reject');
});

    Route::get('/projects/tasks/{task}', [ProjectTaskController::class, 'show'])->name('projects.tasks.show');
});
