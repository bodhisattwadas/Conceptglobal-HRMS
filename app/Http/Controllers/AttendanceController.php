<?php

namespace App\Http\Controllers;

use App\Models\AttendanceDevice;
use App\Models\AttendanceRecord;
use App\Models\AttendanceRegularizationRequest;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function checkInOut(): View
    {
        $employee = Employee::with('workInformation.jobPosition')->firstOrFail();
        $today = now()->toDateString();
        $record = AttendanceRecord::query()
            ->where('employee_id', $employee->id)
            ->where('attendance_date', $today)
            ->latest()
            ->first();

        return view('attendance.check', [
            'employee' => $employee,
            'record' => $record,
            'worked' => $record ? $this->formatMinutes($record->worked_minutes) : '00:00',
            'isCheckedIn' => $record && $record->check_in_at && ! $record->check_out_at,
        ]);
    }

    public function toggle(): RedirectResponse
    {
        $employee = Employee::firstOrFail();
        $record = AttendanceRecord::firstOrCreate(
            ['employee_id' => $employee->id, 'attendance_date' => now()->toDateString()],
            ['check_in_at' => now(), 'source' => 'portal', 'status' => 'open']
        );

        if ($record->check_out_at) {
            return back()->with('status', 'Attendance for today is already closed.');
        }

        if (! $record->check_in_at) {
            $record->update(['check_in_at' => now(), 'status' => 'open']);

            return back()->with('status', 'Checked in.');
        }

        $minutes = Carbon::parse($record->check_in_at)->diffInMinutes(now());
        $record->update([
            'check_out_at' => now(),
            'worked_minutes' => $minutes,
            'status' => 'closed',
        ]);

        return back()->with('status', 'Checked out.');
    }

    public function records(): View
    {
        return view('attendance.records', [
            'records' => AttendanceRecord::with('employee.workInformation.department')->latest('attendance_date')->paginate(20),
        ]);
    }

    public function device(AttendanceDevice $device): View
    {
        return view('attendance.device-show', ['device' => $device->load('company')]);
    }

    public function regularization(AttendanceRegularizationRequest $regularization): View
    {
        return view('attendance.regularization-show', [
            'regularization' => $regularization->load('employee'),
        ]);
    }

    public function approveRegularization(AttendanceRegularizationRequest $regularization): RedirectResponse
    {
        $regularization->update(['status' => 'approved']);

        return back()->with('status', 'Regularization approved.');
    }

    public function rejectRegularization(AttendanceRegularizationRequest $regularization): RedirectResponse
    {
        $regularization->update(['status' => 'rejected']);

        return back()->with('status', 'Regularization rejected.');
    }

    private function formatMinutes(int $minutes): string
    {
        return sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
    }
}
