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
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $loans = EmployeeLoan::with('employee', 'department', 'jobPosition', 'company')
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('loans.index', [
            'loans' => $loans,
            'status' => $status,
        ]);
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'loan_ids' => ['required', 'array', 'min:1'],
            'loan_ids.*' => ['integer', 'exists:employee_loans,id'],
        ]);

        EmployeeLoan::whereIn('id', $data['loan_ids'])->delete();

        return back()->with('status', 'Selected loans deleted.');
    }

    public function create(): View
    {
        $employees = Employee::with('workInformation.department', 'workInformation.jobPosition', 'workInformation.company')->orderBy('first_name')->get();

        return view('loans.create', [
            'employees' => $employees,
            'companies' => Company::orderBy('name')->get(),
            'defaultCurrencyCode' => 'INR',
            'employeeMeta' => $employees->mapWithKeys(fn (Employee $employee) => [
                $employee->id => [
                    'department' => $employee->workInformation?->department?->name,
                    'job_position' => $employee->workInformation?->jobPosition?->name,
                    'company_id' => $employee->workInformation?->company_id,
                ],
            ]),
            'loan' => null,
        ]);
    }

    public function edit(EmployeeLoan $loan): View
    {
        $employees = Employee::with('workInformation.department', 'workInformation.jobPosition', 'workInformation.company')->orderBy('first_name')->get();
        $loan->load('installments');

        return view('loans.create', [
            'employees' => $employees,
            'companies' => Company::orderBy('name')->get(),
            'defaultCurrencyCode' => 'INR',
            'employeeMeta' => $employees->mapWithKeys(fn (Employee $employee) => [
                $employee->id => [
                    'department' => $employee->workInformation?->department?->name,
                    'job_position' => $employee->workInformation?->jobPosition?->name,
                    'company_id' => $employee->workInformation?->company_id,
                ],
            ]),
            'loan' => $loan,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateLoan($request);
        $employee = Employee::with('workInformation')->findOrFail($data['employee_id']);
        $action = $request->string('action', 'draft')->toString();

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
            'currency_code' => 'INR',
            'status' => $action === 'submit' ? 'submitted' : 'draft',
            'total_amount' => $data['loan_amount'],
            'total_paid_amount' => 0,
            'balance_amount' => $data['loan_amount'],
            'notes' => $data['notes'] ?? null,
            'submitted_at' => $action === 'submit' ? now() : null,
            'submitted_by' => $action === 'submit' ? 'Mitchell Admin' : null,
            'submitted_ip' => $action === 'submit' ? $request->ip() : null,
        ]);

        $loan->update(['loan_number' => $this->nextLoanNumber($loan->id)]);
        $installments = $data['installments'] ?? [];
        if (!empty($installments)) {
            $loan->installments()->delete();
            foreach ($installments as $index => $inst) {
                $amount = (float) ($inst['amount'] ?? 0);
                if ($amount <= 0 || empty($inst['payment_date'])) {
                    continue;
                }
                $loan->installments()->create([
                    'installment_no' => $index + 1,
                    'payment_date' => $inst['payment_date'],
                    'amount' => $amount,
                    'paid_amount' => 0,
                    'remaining_amount' => $amount,
                    'status' => 'pending',
                ]);
            }
        } elseif ($action === 'submit') {
            $this->buildInstallments($loan);
        }

        return redirect()->route('loans.show', $loan)->with('status', $action === 'submit' ? 'Loan submitted.' : 'Loan draft saved.');
    }

    public function show(EmployeeLoan $loan): View
    {
        $loan->load('employee.workInformation.department', 'employee.workInformation.jobPosition', 'company', 'installments');

        return view('loans.show', [
            'loan' => $loan,
        ]);
    }

    public function update(Request $request, EmployeeLoan $loan): RedirectResponse
    {
        $data = $this->validateLoan($request);
        $employee = Employee::with('workInformation')->findOrFail($data['employee_id']);
        $action = $request->string('action', 'draft')->toString();

        $loan->update([
            'employee_id' => $employee->id,
            'department_id' => $employee->workInformation?->department_id,
            'job_position_id' => $employee->workInformation?->job_position_id,
            'company_id' => $data['company_id'] ?? $employee->workInformation?->company_id,
            'request_date' => $data['request_date'],
            'loan_amount' => $data['loan_amount'],
            'number_of_installments' => $data['number_of_installments'],
            'payment_start_date' => $data['payment_start_date'],
            'currency_code' => 'INR',
            'status' => $action === 'submit' ? 'submitted' : 'draft',
            'total_amount' => $data['loan_amount'],
            'balance_amount' => $data['loan_amount'],
            'notes' => $data['notes'] ?? null,
            'submitted_at' => $action === 'submit' ? now() : $loan->submitted_at,
            'submitted_by' => $action === 'submit' ? 'Mitchell Admin' : $loan->submitted_by,
            'submitted_ip' => $action === 'submit' ? $request->ip() : $loan->submitted_ip,
        ]);

        $installments = $data['installments'] ?? [];
        $loan->installments()->delete();
        if (!empty($installments)) {
            foreach ($installments as $index => $inst) {
                $amount = (float) ($inst['amount'] ?? 0);
                if ($amount <= 0 || empty($inst['payment_date'])) continue;
                $loan->installments()->create([
                    'installment_no' => $index + 1,
                    'payment_date' => $inst['payment_date'],
                    'amount' => $amount,
                    'paid_amount' => 0,
                    'remaining_amount' => $amount,
                    'status' => 'pending',
                ]);
            }
        } elseif ($action === 'submit') {
            $this->buildInstallments($loan);
        }

        return redirect()->route('loans.show', $loan)->with('status', $action === 'submit' ? 'Loan submitted.' : 'Loan draft updated.');
    }

    public function computeInstallments(EmployeeLoan $loan): RedirectResponse
    {
        if (! $loan->payment_start_date || $loan->number_of_installments < 1 || $loan->loan_amount <= 0) {
            return back()->with('status', 'Set loan amount, number of installments, and payment start date first.');
        }

        $this->buildInstallments($loan);

        return back()->with('status', 'Installments computed.');
    }

    public function submit(EmployeeLoan $loan): RedirectResponse
    {
        if ($loan->installments()->count() === 0) {
            return back()->with('status', 'Compute installments before submit.');
        }
        $loan->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'submitted_by' => 'Mitchell Admin',
            'submitted_ip' => request()->ip(),
        ]);
        return back()->with('status', 'Loan submitted.');
    }

    public function approve(Request $request, EmployeeLoan $loan): RedirectResponse
    {
        $data = $request->validate([
            'treasury_account' => ['required', 'string', 'max:120'],
            'loan_account' => ['required', 'string', 'max:120'],
            'journal' => ['required', 'string', 'max:120'],
        ]);
        $loan->update([
            ...$data,
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => 'Mitchell Admin',
            'approved_ip' => $request->ip(),
        ]);
        return back()->with('status', 'Loan approved.');
    }

    public function refuse(Request $request, EmployeeLoan $loan): RedirectResponse
    {
        $data = $request->validate(['refusal_reason' => ['nullable', 'string', 'max:1000']]);
        $loan->update([
            'status' => 'refused',
            'refusal_reason' => $data['refusal_reason'] ?? null,
            'refused_at' => now(),
            'refused_by' => 'Mitchell Admin',
            'refused_ip' => $request->ip(),
        ]);
        return back()->with('status', 'Loan refused.');
    }

    public function cancel(Request $request, EmployeeLoan $loan): RedirectResponse
    {
        $loan->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => 'Mitchell Admin',
            'cancelled_ip' => $request->ip(),
        ]);
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
            'currency_code' => ['nullable', 'string', 'max:10'],
            'notes' => ['nullable', 'string'],
            'installments' => ['nullable', 'array'],
            'installments.*.payment_date' => ['nullable', 'date'],
            'installments.*.amount' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    private function nextLoanNumber(int $id): string
    {
        return 'LO/'.str_pad((string) $id, 4, '0', STR_PAD_LEFT);
    }

    private function buildInstallments(EmployeeLoan $loan): void
    {
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
    }
}
