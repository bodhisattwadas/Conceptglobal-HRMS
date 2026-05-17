<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HrAdministrationController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\OrganizationController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::get('/organization', [OrganizationController::class, 'index'])->name('organization.index');
Route::post('/organization/companies', [OrganizationController::class, 'storeCompany'])->name('organization.companies.store');
Route::post('/organization/departments', [OrganizationController::class, 'storeDepartment'])->name('organization.departments.store');
Route::post('/organization/job-positions', [OrganizationController::class, 'storeJobPosition'])->name('organization.job-positions.store');
Route::post('/organization/job-roles', [OrganizationController::class, 'storeJobRole'])->name('organization.job-roles.store');

Route::prefix('admin/hr')->name('hr-admin.')->group(function (): void {
    Route::get('/departments', [HrAdministrationController::class, 'departments'])->name('departments.index');
    Route::get('/employees', [HrAdministrationController::class, 'employees'])->name('employees.index');
    Route::get('/announcements/create', [HrAdministrationController::class, 'announcementCreate'])->name('announcements.create');
    Route::get('/transfers/create', [HrAdministrationController::class, 'transferCreate'])->name('transfers.create');
    Route::get('/legal-cases/LC0001', [HrAdministrationController::class, 'legalCase'])->name('legal-cases.show');
    Route::get('/resignations/RES001', [HrAdministrationController::class, 'resignation'])->name('resignations.show');
    Route::get('/custodies/create', [HrAdministrationController::class, 'custodyCreate'])->name('custodies.create');
    Route::get('/shifts/working-times', [HrAdministrationController::class, 'shiftWorkingTimes'])->name('shifts.working-times.index');
});

Route::patch('/employees/{employee}/archive', [EmployeeController::class, 'archive'])->name('employees.archive');
Route::patch('/employees/{employee}/restore', [EmployeeController::class, 'restore'])->name('employees.restore');
Route::get('/employees/{employee}/documents/create', [EmployeeController::class, 'createDocument'])->name('employees.documents.create');
Route::post('/employees/{employee}/documents', [EmployeeController::class, 'storeDocument'])->name('employees.documents.store');
Route::get('/employees/{employee}/timesheets', [EmployeeController::class, 'timesheets'])->name('employees.timesheets.index');
Route::resource('employees', EmployeeController::class)->except(['destroy']);

Route::get('/attendances/check', [AttendanceController::class, 'checkInOut'])->name('attendance.check');
Route::post('/attendances/toggle', [AttendanceController::class, 'toggle'])->name('attendance.toggle');
Route::get('/attendances', [AttendanceController::class, 'records'])->name('attendance.records');
Route::get('/attendances/devices/{device}', [AttendanceController::class, 'device'])->name('attendance.devices.show');
Route::get('/attendances/regularization/{regularization}', [AttendanceController::class, 'regularization'])->name('attendance.regularization.show');
Route::patch('/attendances/regularization/{regularization}/approve', [AttendanceController::class, 'approveRegularization'])->name('attendance.regularization.approve');
Route::patch('/attendances/regularization/{regularization}/reject', [AttendanceController::class, 'rejectRegularization'])->name('attendance.regularization.reject');
Route::get('/attendances/reporting', [AttendanceController::class, 'reporting'])->name('attendance.reporting');
Route::get('/attendance/check', [AttendanceController::class, 'checkInOut']);
Route::get('/attendance/machines/{device}', [AttendanceController::class, 'device']);
Route::get('/attendance/regularizations/{regularization}', [AttendanceController::class, 'regularization']);
Route::get('/attendance/reporting', [AttendanceController::class, 'reporting']);

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

Route::get('/employees/view/{view}', [EmployeeController::class, 'view'])->name('employees.view');
Route::post('/employees/bulk-action', [EmployeeController::class, 'bulkAction'])->name('employees.bulk-action');
