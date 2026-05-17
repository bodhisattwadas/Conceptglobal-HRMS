<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
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

Route::patch('/employees/{employee}/archive', [EmployeeController::class, 'archive'])->name('employees.archive');
Route::patch('/employees/{employee}/restore', [EmployeeController::class, 'restore'])->name('employees.restore');
Route::resource('employees', EmployeeController::class)->except(['destroy']);

Route::get('/attendances/check', [AttendanceController::class, 'checkInOut'])->name('attendance.check');
Route::post('/attendances/toggle', [AttendanceController::class, 'toggle'])->name('attendance.toggle');
Route::get('/attendances', [AttendanceController::class, 'records'])->name('attendance.records');
Route::get('/attendances/devices/{device}', [AttendanceController::class, 'device'])->name('attendance.devices.show');
Route::get('/attendances/regularization/{regularization}', [AttendanceController::class, 'regularization'])->name('attendance.regularization.show');
Route::patch('/attendances/regularization/{regularization}/approve', [AttendanceController::class, 'approveRegularization'])->name('attendance.regularization.approve');
Route::patch('/attendances/regularization/{regularization}/reject', [AttendanceController::class, 'rejectRegularization'])->name('attendance.regularization.reject');

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

Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
Route::patch('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
Route::get('/employees/view/{view}', [EmployeeController::class, 'view'])->name('employees.view');
Route::post('/employees/bulk-action', [EmployeeController::class, 'bulkAction'])->name('employees.bulk-action');
