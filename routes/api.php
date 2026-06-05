<?php

use App\Http\Controllers\Api\DesktopTimesheetController;
use Illuminate\Support\Facades\Route;

Route::prefix('desktop')->name('api.desktop.')->group(function (): void {
    Route::post('/login', [DesktopTimesheetController::class, 'login'])->name('login');
    Route::get('/me', [DesktopTimesheetController::class, 'me'])->name('me');
    Route::get('/bootstrap', [DesktopTimesheetController::class, 'bootstrap'])->name('bootstrap');
    Route::get('/timesheets', [DesktopTimesheetController::class, 'timesheets'])->name('timesheets.index');
    Route::post('/timesheets', [DesktopTimesheetController::class, 'storeTimesheet'])->name('timesheets.store');
    Route::delete('/timesheets/{timesheet}', [DesktopTimesheetController::class, 'deleteTimesheet'])->name('timesheets.destroy');
});
