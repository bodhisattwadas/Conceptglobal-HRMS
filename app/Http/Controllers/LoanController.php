<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Models\MasterSetting;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoanController extends Controller
{
    public function create(): View
    {
        return view('loans.create', [
            'employees' => Employee::with('workInformation.department', 'workInformation.jobPosition', 'workInformation.company')->orderBy('first_name')->get(),
            'companies' => Company::orderBy('name')->get(),
            'defaultCurrencyCode' => MasterSetting::firstOrCreate([])->default_currency_code,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateLoan($request);
        $employee = Employee::with('workInformation')->findOrFail($data['employee_id']);

        $loan = EmployeeLoan::create([
            'loan_number' => '/',
            'employee_id' => $employee->id,
            'department_id' => $employee->workInformation?->department_id,
            'job_position_id' => $employee->workInformation?->job_position_id,
            'company_id' => $data['company_id'] ?? $employee->workInformation?->company_id,
            'request_date' => $data['request_date'],
            'loan_amount' => $data['loan_amount'],
            'number_of_installments' => $data['number_of_installments'],
            'payment_start_date' => $data['payment_start_date'],
            'currency_code' => $data['currency_code'],
            'status' => 'draft',
            'total_amount' => $data['loan_amount'],
            'total_paid_amount' => 0,
            'balance_amount' => $data['loan_amount'],
        ]);

        $loan->update(['loan_number' => $this->nextLoanNumber($loan->id)]);

        return redirect()->route('loans.show', $loan)->with('status', 'Loan request created.');
    }

    public function show(EmployeeLoan $loan): View
    {
        $loan->load('employee.workInformation.department', 'employee.workInformation.jobPosition', 'company', 'installments');

        return view('loans.show', [
            'loan' => $loan,
        ]);
    }

    public function computeInstallments(EmployeeLoan $loan): RedirectResponse
    {
        if (! $loan->payment_start_date || $loan->number_of_installments < 1 || $loan->loan_amount <= 0) {
            return back()->with('status', 'Set loan amount, number of installments, and payment start date first.');
        }

        $loan->installments()->delete();
        $start = Carbon::parse($loan->payment_start_date);
        $base = round(((float) $loan->loan_amount) / $loan->number_of_installments, 2);
        $sum = 0.0;

        for ($i = 1; $i <= $loan->number_of_installments; $i++) {
            $date = $i === 1 ? $start->copy() : $start->copy()->addMonthsNoOverflow($i - 1);
            $amount = $i === $loan->number_of_installments
                ? round(((float) $loan->loan_amount) - $sum, 2)
                : $base;
            $sum += $amount;

            $loan->installments()->create([
                'installment_no' => $i,
                'payment_date' => $date->toDateString(),
                'amount' => $amount,
                'paid_amount' => 0,
                'remaining_amount' => $amount,
                'status' => 'pending',
            ]);
        }

        return back()->with('status', 'Installments computed.');
    }

    public function submit(EmployeeLoan $loan): RedirectResponse
    {
        if ($loan->installments()->count() === 0) {
            return back()->with('status', 'Compute installments before submit.');
        }
        $loan->update(['status' => 'submitted']);
        return back()->with('status', 'Loan submitted.');
    }

    public function approve(Request $request, EmployeeLoan $loan): RedirectResponse
    {
        $data = $request->validate([
            'treasury_account' => ['required', 'string', 'max:120'],
            'loan_account' => ['required', 'string', 'max:120'],
            'journal' => ['required', 'string', 'max:120'],
        ]);
        $loan->update([...$data, 'status' => 'approved']);
        return back()->with('status', 'Loan approved.');
    }

    public function refuse(Request $request, EmployeeLoan $loan): RedirectResponse
    {
        $data = $request->validate(['refusal_reason' => ['nullable', 'string', 'max:1000']]);
        $loan->update(['status' => 'refused', 'refusal_reason' => $data['refusal_reason'] ?? null]);
        return back()->with('status', 'Loan refused.');
    }

    public function cancel(EmployeeLoan $loan): RedirectResponse
    {
        $loan->update(['status' => 'cancelled']);
        return back()->with('status', 'Loan cancelled.');
    }

    private function validateLoan(Request $request): array
    {
        return $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'request_date' => ['required', 'date'],
            'loan_amount' => ['required', 'numeric', 'min:1'],
            'number_of_installments' => ['required', 'integer', 'min:1'],
            'payment_start_date' => ['required', 'date'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'currency_code' => ['required', 'string', 'max:10'],
        ]);
    }

    private function nextLoanNumber(int $id): string
    {
        return 'LO/'.str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    }
}
