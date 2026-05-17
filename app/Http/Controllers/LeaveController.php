<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveSetting;
use App\Models\LeaveType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveController extends Controller
{
    public function types(): View
    {
        return view('leaves.types', [
            'types' => LeaveType::orderByRaw("CASE name WHEN 'Paid Time Off' THEN 1 WHEN 'Compensatory Days' THEN 2 WHEN 'Sick Time Off' THEN 3 WHEN 'Unpaid' THEN 4 WHEN 'Parental Leaves' THEN 5 WHEN 'Extra Hours' THEN 6 ELSE 99 END")
                ->orderBy('name')
                ->paginate(20),
        ]);
    }

    public function requests(): View
    {
        return view('leaves.requests', [
            'requests' => LeaveRequest::with('employee', 'leaveType', 'company')->latest()->paginate(20),
        ]);
    }

    public function createRequest(): View
    {
        return view('leaves.create-request', [
            'employees' => Employee::active()->orderBy('first_name')->get(),
            'types' => LeaveType::orderBy('name')->get(),
            'companies' => Company::orderBy('name')->get(),
        ]);
    }

    public function storeRequest(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'description' => ['nullable', 'string'],
        ]);

        $from = \Carbon\Carbon::parse($data['from_date']);
        $to = \Carbon\Carbon::parse($data['to_date']);
        $days = $from->diffInDays($to) + 1;

        $requestModel = LeaveRequest::create([
            ...$data,
            'duration_days' => $days,
            'duration_hours' => $days * 8,
            'remaining_legal_leaves' => 39,
            'status' => 'to_approve',
        ]);

        return redirect()->route('leaves.requests.show', $requestModel)->with('status', 'Leave request created.');
    }

    public function showRequest(LeaveRequest $leaveRequest): View
    {
        return view('leaves.request-show', [
            'leaveRequest' => $leaveRequest->load('employee', 'leaveType', 'company', 'pendingWorks'),
        ]);
    }

    public function approve(LeaveRequest $leaveRequest): RedirectResponse
    {
        $leaveRequest->update(['status' => 'approved']);

        return back()->with('status', 'Leave request approved.');
    }

    public function refuse(LeaveRequest $leaveRequest): RedirectResponse
    {
        $leaveRequest->update(['status' => 'refused']);

        return back()->with('status', 'Leave request refused.');
    }

    public function markDraft(LeaveRequest $leaveRequest): RedirectResponse
    {
        $leaveRequest->update(['status' => 'draft']);

        return back()->with('status', 'Leave request moved to draft.');
    }

    public function settings(): View
    {
        return view('leaves.settings', [
            'settings' => LeaveSetting::firstOrCreate([]),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $settings = LeaveSetting::firstOrCreate([]);
        $settings->update($request->validate([
            'email_alias_prefix' => ['nullable', 'string', 'max:120'],
            'email_alias_domain' => ['nullable', 'string', 'max:120'],
            'leave_reminder_days_before' => ['required', 'integer', 'min:0', 'max:365'],
            'leave_reminder_enabled' => ['nullable'],
            'employee_shift_enabled' => ['nullable'],
            'vacation_management_enabled' => ['nullable'],
        ]) + [
            'leave_reminder_enabled' => $request->boolean('leave_reminder_enabled'),
            'employee_shift_enabled' => $request->boolean('employee_shift_enabled'),
            'vacation_management_enabled' => $request->boolean('vacation_management_enabled'),
        ]);

        return back()->with('status', 'Leave settings saved.');
    }
}
