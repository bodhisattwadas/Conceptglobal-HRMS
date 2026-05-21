<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\PayrollContract;
use App\Models\PayrollContributionRegister;
use App\Models\PayrollPayslipBatch;
use App\Models\PayrollPayslip;
use App\Models\PayrollSetting;
use App\Models\PayrollSalaryRule;
use App\Models\PayrollSalaryStructure;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function createContract(): View
    {
        return view('payroll.contract-form', [
            'employees' => Employee::with('workInformation.department', 'workInformation.jobPosition')->orderBy('first_name')->get(),
        ]);
    }
    public function storeContract(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'contract_name' => ['required', 'string', 'max:180'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'notice_period_days' => ['nullable', 'integer', 'min:0'],
            'employee_category' => ['nullable', 'string', 'max:80'],
            'salary_structure' => ['nullable', 'string', 'max:180'],
            'salary_structure_type' => ['nullable', 'string', 'max:80'],
            'working_schedule' => ['nullable', 'string', 'max:180'],
            'hr_responsible' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string'],
        ]);
        $employee = Employee::with('workInformation')->findOrFail($data['employee_id']);
        $contract = PayrollContract::create($data + [
            'department_id' => $employee->workInformation?->department_id,
            'job_position_id' => $employee->workInformation?->job_position_id,
        ]);
        return redirect()->route('payroll.contracts.show', $contract)->with('status', 'Payroll contract created.');
    }
    public function contracts(): View
    {
        return view('payroll.contracts-index', [
            'contracts' => PayrollContract::with('employee', 'department', 'jobPosition')->latest()->paginate(20),
        ]);
    }

    public function structures(): View
    {
        return view('payroll.structures-index', [
            'structures' => PayrollSalaryStructure::orderBy('name')->paginate(20),
        ]);
    }
    public function createStructure(): View { return view('payroll.structure-form', ['structure' => null]); }
    public function storeStructure(Request $request): RedirectResponse
    {
        PayrollSalaryStructure::create($request->validate([
            'name' => ['required', 'string', 'max:150'],
            'reference' => ['required', 'string', 'max:40', 'unique:payroll_salary_structures,reference'],
            'salary_rules_count' => ['nullable', 'integer', 'min:0'],
        ]) + ['salary_rules_count' => (int) $request->input('salary_rules_count', 0)]);
        return redirect()->route('payroll.structures.index')->with('status', 'Salary structure created.');
    }

    public function rule(PayrollSalaryRule $rule): View
    {
        return view('payroll.rule-show', ['rule' => $rule]);
    }

    public function rules(): View
    {
        return view('payroll.rules-index', [
            'rules' => PayrollSalaryRule::latest()->paginate(20),
        ]);
    }
    public function createRule(): View
    {
        return view('payroll.rule-form', [
            'rule' => null,
            'registers' => PayrollContributionRegister::orderBy('name')->get(),
        ]);
    }
    public function storeRule(Request $request): RedirectResponse
    {
        PayrollSalaryRule::create($this->validateRule($request));
        return redirect()->route('payroll.rules.index')->with('status', 'Salary rule created.');
    }
    public function editRule(PayrollSalaryRule $rule): View
    {
        return view('payroll.rule-form', [
            'rule' => $rule,
            'registers' => PayrollContributionRegister::orderBy('name')->get(),
        ]);
    }
    public function updateRule(Request $request, PayrollSalaryRule $rule): RedirectResponse
    {
        $rule->update($this->validateRule($request, $rule));
        return redirect()->route('payroll.rules.show', $rule)->with('status', 'Salary rule updated.');
    }

    public function batch(PayrollPayslipBatch $batch): View
    {
        return view('payroll.batch-show', [
            'batch' => $batch->load('payslips.employee'),
        ]);
    }

    public function batches(): View
    {
        return view('payroll.batches-index', [
            'batches' => PayrollPayslipBatch::withCount('payslips')->latest()->paginate(20),
        ]);
    }
    public function createBatch(): View
    {
        return view('payroll.batch-form');
    }
    public function storeBatch(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'credit_note' => ['nullable'],
        ]);
        $batch = PayrollPayslipBatch::create([
            ...$data,
            'credit_note' => $request->boolean('credit_note'),
            'state' => 'draft',
        ]);
        return redirect()->route('payroll.batches.show', $batch)->with('status', 'Payslip batch created.');
    }

    public function computeBatch(PayrollPayslipBatch $batch): RedirectResponse
    {
        if ($batch->state === 'closed') {
            return back()->with('status', 'Closed batch cannot be computed.');
        }

        $employees = Employee::orderBy('first_name')->get();
        foreach ($employees as $employee) {
            PayrollPayslip::firstOrCreate(
                [
                    'payroll_payslip_batch_id' => $batch->id,
                    'employee_id' => $employee->id,
                ],
                [
                    'reference' => 'SLIP'.str_pad((string) (PayrollPayslip::max('id') + 1), 4, '0', STR_PAD_LEFT),
                    'name' => 'Salary Slip of '.$employee->full_name.' for '.optional($batch->date_from)->format('F-Y'),
                    'date_from' => $batch->date_from,
                    'date_to' => $batch->date_to,
                    'status' => 'computed',
                ]
            );
        }

        $batch->payslips()->where('status', 'draft')->update(['status' => 'computed']);
        $batch->update(['state' => 'computed']);

        return back()->with('status', 'Payslips computed.');
    }

    public function approveBatch(PayrollPayslipBatch $batch): RedirectResponse
    {
        if (!in_array($batch->state, ['computed', 'approved'], true)) {
            return back()->with('status', 'Compute payslips before approval.');
        }
        $batch->payslips()->update(['status' => 'approved']);
        $batch->update(['state' => 'approved']);
        return back()->with('status', 'All payslips approved.');
    }

    public function closeBatch(PayrollPayslipBatch $batch): RedirectResponse
    {
        if ($batch->state !== 'approved') {
            return back()->with('status', 'Only approved batch can be closed.');
        }
        $batch->payslips()->update(['status' => 'done']);
        $batch->update(['state' => 'closed']);
        return back()->with('status', 'Batch closed.');
    }

    public function contract(PayrollContract $contract): View
    {
        return view('payroll.contract-show', [
            'contract' => $contract->load('employee', 'department', 'jobPosition'),
        ]);
    }

    public function downloadPayslip(PayrollPayslip $payslip): Response
    {
        $payslip->load('employee', 'batch');
        $filename = ($payslip->reference ?: 'payslip').'.pdf';

        $pdf = Pdf::loadView('payroll.payslip-pdf', [
            'payslip' => $payslip,
        ])->setPaper('a4');

        return $pdf->download($filename);
    }
    public function registers(): View
    {
        return view('payroll.registers-index', [
            'registers' => PayrollContributionRegister::orderBy('name')->paginate(20),
        ]);
    }
    public function storeRegister(Request $request): RedirectResponse
    {
        PayrollContributionRegister::create($request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['nullable', 'string', 'max:30'],
        ]) + ['active' => true]);
        return back()->with('status', 'Contribution register created.');
    }
    public function settings(): View
    {
        return view('payroll.settings', [
            'settings' => PayrollSetting::firstOrCreate([]),
        ]);
    }
    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'default_currency_code' => ['required', 'string', 'max:10'],
            'default_working_days_per_month' => ['required', 'numeric', 'min:1'],
            'default_working_hours_per_day' => ['required', 'numeric', 'min:1'],
        ]);
        PayrollSetting::firstOrCreate([])->update($data + [
            'payroll_approval_required' => $request->boolean('payroll_approval_required'),
            'include_attendance_in_payroll' => $request->boolean('include_attendance_in_payroll'),
            'include_leave_in_payroll' => $request->boolean('include_leave_in_payroll'),
            'include_timesheet_in_payroll' => $request->boolean('include_timesheet_in_payroll'),
        ]);
        return back()->with('status', 'Payroll settings saved.');
    }

    private function validateRule(Request $request, ?PayrollSalaryRule $rule = null): array
    {
        $id = $rule?->id ?? 'NULL';
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:40', "unique:payroll_salary_rules,code,{$id}"],
            'sequence' => ['required', 'integer', 'min:1'],
            'active' => ['nullable'],
            'appears_on_payslip' => ['nullable'],
            'condition_based_on' => ['nullable', 'string', 'max:100'],
            'amount_type' => ['nullable', 'string', 'max:100'],
            'python_code' => ['nullable', 'string'],
            'contribution_register' => ['nullable', 'string', 'max:120'],
        ]) + [
            'active' => $request->boolean('active', true),
            'appears_on_payslip' => $request->boolean('appears_on_payslip', true),
        ];
    }
}
