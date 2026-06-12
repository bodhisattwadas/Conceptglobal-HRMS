<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\DocumentTemplate;
use App\Models\Employee;
use App\Models\EmployeeBankDetail;
use App\Models\EmployeeWorkInformation;
use App\Models\JobPosition;
use App\Models\MasterSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $employees = Employee::with([
            'workInformation.company',
            'workInformation.department',
            'workInformation.jobPosition',
        ])
            ->when($request->string('search')->toString(), function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('badge_id', 'like', "%{$search}%")
                        ->orWhereHas('workInformation.department', fn ($query) => $query->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('workInformation.jobPosition', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('company_id'), function ($query) use ($request): void {
                $query->whereHas('workInformation', function ($query) use ($request): void {
                    $query->where('company_id', $request->integer('company_id'));
                });
            })
            ->when($request->filled('department_id'), function ($query) use ($request): void {
                $query->whereHas('workInformation', function ($query) use ($request): void {
                    $query->where('department_id', $request->integer('department_id'));
                });
            })
            ->when($request->string('status')->toString() !== '', function ($query) use ($request): void {
                $query->where('is_active', $request->string('status')->toString() === 'active');
            })
            ->orderBy('first_name')
            ->paginate(24)
            ->withQueryString();

        return view('employees.index', [
            'employees' => $employees,
            'companies' => Company::active()->orderBy('name')->get(),
            'departments' => Department::active()->orderBy('name')->get(),
            'departmentCounts' => Department::orderBy('name')
                ->get()
                ->mapWithKeys(fn (Department $department) => [
                    $department->id => Employee::whereHas('workInformation', fn ($query) => $query->where('department_id', $department->id))->count(),
                ]),
            'companyCounts' => Company::orderBy('name')
                ->get()
                ->mapWithKeys(fn (Company $company) => [
                    $company->id => Employee::whereHas('workInformation', fn ($query) => $query->where('company_id', $company->id))->count(),
                ]),
            'viewMode' => $request->string('view', 'kanban')->toString(),
            'employeeTotal' => Employee::count(),
            'activeTotal' => Employee::active()->count(),
        ]);
    }

    public function create(): View
    {
        return view('employees.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedEmployee($request);
        $employee = Employee::create($data['employee']);
        $employeePayload = $data['employee'];
        $this->attachUploads($request, $employeePayload, $employee);
        $employee->update($employeePayload);
        $this->syncEmployeeUser($employee, $data['access_level'], $data['login_password']);
        EmployeeWorkInformation::create([
            ...$data['work'],
            'employee_id' => $employee->id,
        ]);
        EmployeeBankDetail::create([
            ...$data['bank'],
            'employee_id' => $employee->id,
        ]);

        return redirect()->route('employees.index')->with('status', 'Employee created.');
    }

    public function show(Employee $employee): View
    {
        $employee->load([
            'user',
            'workInformation.company',
            'workInformation.department',
            'workInformation.jobPosition',
            'workInformation.reportingManager',
            'workInformation.coach',
        ]);

        return view('employees.show', [
            'employee' => $employee,
        ]);
    }

    public function createDocument(Employee $employee): View
    {
        $employee->load('workInformation.jobPosition');
        $settings = MasterSetting::firstOrCreate([]);

        return view('employees.documents-create', [
            'employee' => $employee,
            'documentTypes' => $settings->employee_document_types ?: MasterSetting::DEFAULT_EMPLOYEE_DOCUMENT_TYPES,
        ]);
    }

    public function storeDocument(Request $request, Employee $employee): RedirectResponse
    {
        $data = $request->validate([
            'document_number' => ['nullable', 'string', 'max:100'],
            'document_type' => ['nullable', 'string', 'max:100'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'notification_type' => ['nullable', 'string', 'max:80'],
            'days' => ['nullable', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,png,jpg,jpeg', 'max:10240'],
        ]);

        if ($request->hasFile('attachment')) {
            $folder = $this->employeeFolder($employee).'/documents';
            $this->storeNamedPublicFile($request->file('attachment'), $folder, $employee, $data['document_type'] ?? 'document');
        }

        return redirect()->route('employees.show', $employee)->with('status', 'Document draft saved.');
    }

    public function downloadDocument(Employee $employee, int $documentIndex)
    {
        $documents = collect($employee->related_document_paths ?? []);
        $document = $documents->get($documentIndex);

        abort_unless(is_array($document) && ! empty($document['path']), 404);

        $downloadName = $document['download_name'] ?? basename((string) $document['path']);

        return Storage::disk('public')->download($document['path'], $downloadName);
    }

    public function downloadCv(Employee $employee)
    {
        abort_unless($employee->cv_file_path, 404);

        return Storage::disk('public')->download($employee->cv_file_path, basename($employee->cv_file_path));
    }

    public function timesheets(Employee $employee): View
    {
        $employee->load('workInformation.jobPosition');

        return view('employees.timesheets', [
            'employee' => $employee,
            'timesheets' => [
                ['date' => '02/10/2022', 'project' => 'Research & Development', 'task' => 'Unit Testing', 'description' => 'Requirements analysis', 'hours' => '03:00'],
                ['date' => '02/05/2022', 'project' => 'Office Design', 'task' => 'Room 2: Decoration', 'description' => 'Requirements analysis', 'hours' => '02:00'],
            ],
            'totalHours' => '05:00',
        ]);
    }

    public function documentTemplates(Request $request): View
    {
        $templates = DocumentTemplate::query()
            ->when($request->string('search')->toString(), function ($query, string $search): void {
                $query->where(fn ($query) => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('employees.document-templates.index', [
            'templates' => $templates,
        ]);
    }

    public function createDocumentTemplate(): View
    {
        return view('employees.document-templates.create');
    }

    public function storeDocumentTemplate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40', 'unique:document_templates,code'],
            'name' => ['required', 'string', 'max:140'],
            'description' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'is_active' => ['nullable'],
        ]);

        DocumentTemplate::create([
            'code' => strtoupper(trim($data['code'])),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'content' => $data['content'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('employees.document-templates.index')->with('status', 'Document template created.');
    }

    public function editDocumentTemplate(DocumentTemplate $documentTemplate): View
    {
        return view('employees.document-templates.edit', [
            'template' => $documentTemplate,
        ]);
    }

    public function updateDocumentTemplate(Request $request, DocumentTemplate $documentTemplate): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40', 'unique:document_templates,code,'.$documentTemplate->id],
            'name' => ['required', 'string', 'max:140'],
            'description' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'is_active' => ['nullable'],
        ]);

        $documentTemplate->update([
            'code' => strtoupper(trim($data['code'])),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'content' => $data['content'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('employees.document-templates.index')->with('status', 'Document template updated.');
    }

    public function edit(Employee $employee): View
    {
        $employee->load('workInformation', 'bankDetail', 'user');

        return view('employees.edit', [
            ...$this->formData(),
            'employee' => $employee,
        ]);
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $data = $this->validatedEmployee($request, $employee);
        $employeePayload = $data['employee'];
        $this->attachUploads($request, $employeePayload, $employee);

        $employee->update($employeePayload);
        $this->syncEmployeeUser($employee, $data['access_level'], $data['login_password']);
        $employee->workInformation()->updateOrCreate(
            ['employee_id' => $employee->id],
            $data['work']
        );
        $employee->bankDetail()->updateOrCreate(
            ['employee_id' => $employee->id],
            $data['bank']
        );

        return redirect()->route('employees.index')->with('status', 'Employee updated.');
    }

    public function archive(Employee $employee): RedirectResponse
    {
        $employee->update(['is_active' => false]);

        return back()->with('status', 'Employee archived.');
    }

    public function restore(Employee $employee): RedirectResponse
    {
        $employee->update(['is_active' => true]);

        return back()->with('status', 'Employee restored.');
    }

    private function formData(): array
    {
        return [
            'companies' => Company::active()->orderBy('name')->get(),
            'departments' => Department::active()->orderBy('name')->get(),
            'jobPositions' => JobPosition::active()->orderBy('name')->get(),
            'managers' => Employee::where('is_active', true)->orderBy('first_name')->get(),
            'documentTypes' => $this->documentTypes(),
            'timezones' => [
                'Asia/Kolkata',
                'America/Los_Angeles',
                'America/New_York',
                'Europe/Brussels',
                'Europe/London',
                'UTC',
            ],
        ];
    }

    private function validatedEmployee(Request $request, ?Employee $employee = null): array
    {
        $employeeId = $employee?->id ?? 'NULL';

        $data = $request->validate([
            'badge_id' => ['nullable', 'string', 'max:50'],
            'profile_photo_file' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
                'dimensions:min_width=100,min_height=100,max_width=3000,max_height=3000',
            ],
            'cv_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'related_document_types' => ['nullable', 'array'],
            'related_document_types.*' => ['nullable', 'string', 'max:100'],
            'related_documents' => ['nullable', 'array'],
            'related_documents.*' => ['nullable', 'file', 'mimes:pdf,doc,docx,png,jpg,jpeg', 'max:10240'],
            'card_color' => ['nullable', 'string', 'max:20'],
            'first_name' => ['required', 'string', 'max:200'],
            'last_name' => ['nullable', 'string', 'max:200'],
            'email' => [
                'required',
                'email',
                'max:255',
                "unique:employees,email,{$employeeId}",
                Rule::unique('users', 'email')->ignore($employee?->user_id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date'],
            'qualification' => ['nullable', 'string', 'max:100'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'marital_status' => ['nullable', 'in:single,married,divorced'],
            'children_count' => ['nullable', 'integer', 'min:0', 'max:30'],
            'emergency_contact_name' => ['nullable', 'string', 'max:100'],
            'emergency_contact' => ['nullable', 'string', 'max:30'],
            'emergency_contact_relation' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'country' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'zip' => ['nullable', 'string', 'max:30'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'job_position_id' => ['nullable', 'exists:job_positions,id'],
            'reporting_manager_id' => ['nullable', 'exists:employees,id'],
            'coach_id' => ['nullable', 'exists:employees,id'],
            'work_email' => ['nullable', 'email', 'max:255'],
            'work_mobile' => ['nullable', 'string', 'max:30'],
            'work_phone' => ['nullable', 'string', 'max:30'],
            'date_joining' => ['nullable', 'date'],
            'employment_type' => ['nullable', 'string', 'max:50'],
            'work_location' => ['nullable', 'string', 'max:150'],
            'working_hours' => ['nullable', 'string', 'max:100'],
            'timezone' => ['nullable', 'string', 'max:80'],
            'bank_name' => ['nullable', 'string', 'max:120'],
            'account_number' => ['nullable', 'string', 'max:80'],
            'account_holder_name' => ['nullable', 'string', 'max:160'],
            'ifsc_code' => ['nullable', 'string', 'max:40'],
            'branch' => ['nullable', 'string', 'max:120'],
            'access_level' => ['nullable', 'in:employee,super_admin'],
            'login_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        return [
            'employee' => [
                'badge_id' => $data['badge_id'] ?? null,
                'card_color' => $data['card_color'] ?? '#6f42c1',
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'gender' => $data['gender'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'qualification' => $data['qualification'] ?? null,
                'experience_years' => $data['experience_years'] ?? null,
                'marital_status' => $data['marital_status'] ?? null,
                'children_count' => $data['children_count'] ?? null,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_contact' => $data['emergency_contact'] ?? null,
                'emergency_contact_relation' => $data['emergency_contact_relation'] ?? null,
                'address' => $data['address'] ?? null,
                'country' => $data['country'] ?? null,
                'state' => $data['state'] ?? null,
                'city' => $data['city'] ?? null,
                'zip' => $data['zip'] ?? null,
            ],
            'work' => [
                'company_id' => $data['company_id'] ?? null,
                'department_id' => $data['department_id'] ?? null,
                'job_position_id' => $data['job_position_id'] ?? null,
                'reporting_manager_id' => $data['reporting_manager_id'] ?? null,
                'coach_id' => $data['coach_id'] ?? null,
                'email' => $data['work_email'] ?? null,
                'work_mobile' => $data['work_mobile'] ?? null,
                'work_phone' => $data['work_phone'] ?? null,
                'date_joining' => $data['date_joining'] ?? null,
                'employment_type' => $data['employment_type'] ?? null,
                'work_location' => $data['work_location'] ?? null,
                'working_hours' => $data['working_hours'] ?? null,
                'timezone' => $data['timezone'] ?? null,
            ],
            'bank' => [
                'bank_name' => $data['bank_name'] ?? null,
                'account_number' => $data['account_number'] ?? null,
                'account_holder_name' => $data['account_holder_name'] ?? null,
                'ifsc_code' => $data['ifsc_code'] ?? null,
                'branch' => $data['branch'] ?? null,
            ],
            'access_level' => $data['access_level'] ?? 'employee',
            'login_password' => $data['login_password'] ?? null,
        ];
    }

    private function syncEmployeeUser(Employee $employee, string $accessLevel, ?string $loginPassword = null): void
    {
        $user = $employee->user ?: User::firstOrNew(['email' => $employee->email]);

        $user->fill([
            'name' => $employee->full_name ?: $employee->email,
            'email' => $employee->email,
            'access_level' => $accessLevel,
        ]);

        if ($loginPassword) {
            $user->password = Hash::make($loginPassword);
        } elseif (! $user->exists) {
            $user->password = Hash::make('password');
        }

        $user->save();

        if ($employee->user_id !== $user->id) {
            $employee->forceFill(['user_id' => $user->id])->save();
        }
    }

    private function attachUploads(Request $request, array &$employeePayload, ?Employee $existing): void
    {
        if (! $existing) {
            return;
        }

        $baseFolder = $this->employeeFolder($existing);

        if ($request->hasFile('profile_photo_file')) {
            $this->deleteStoredPublicFile($existing->profile_photo_url);

            $file = $request->file('profile_photo_file');
            $extension = $file->extension() ?: $file->getClientOriginalExtension();
            $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'profile-photo';
            $path = $file->storeAs($baseFolder.'/photo', $filename.'-'.now()->format('YmdHis').'.'.$extension, 'public');

            // Store a disk-relative path so rendering is not tied to APP_URL host/port.
            $employeePayload['profile_photo_url'] = $path;
        }

        if ($request->hasFile('cv_file')) {
            if ($existing->cv_file_path) {
                Storage::disk('public')->delete($existing->cv_file_path);
            }

            $employeePayload['cv_file_path'] = $this->storeNamedPublicFile(
                $request->file('cv_file'),
                $baseFolder.'/cv',
                $existing,
                'cv'
            );
        }

        if ($request->hasFile('related_documents')) {
            $existingDocs = $existing?->related_document_paths ?? [];
            $newDocs = [];
            $documentTypes = $request->input('related_document_types', []);

            foreach ($request->file('related_documents', []) as $index => $file) {
                if ($file) {
                    $type = trim((string) ($documentTypes[$index] ?? ''));
                    $path = $this->storeNamedPublicFile($file, $baseFolder.'/related-documents', $existing, $type ?: 'document');
                    $downloadName = basename($path);

                    $newDocs[] = [
                        'type' => $type ?: 'Document',
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'download_name' => $downloadName,
                        'uploaded_at' => now()->toDateTimeString(),
                    ];
                }
            }
            $employeePayload['related_document_paths'] = array_values(array_merge($existingDocs, $newDocs));
        }
    }

    private function employeeFolder(Employee $employee): string
    {
        $name = trim($employee->full_name) !== '' ? $employee->full_name : 'Employee';
        $cleanName = preg_replace('/[^A-Za-z0-9]+/', '-', $name) ?: 'Employee';
        $cleanName = trim($cleanName, '-');

        return 'employees/'.$cleanName.'-'.$employee->id;
    }

    private function deleteStoredPublicFile(?string $url): void
    {
        if (! $url) {
            return;
        }

        $path = null;

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            $parsedPath = parse_url($url, PHP_URL_PATH);
            if (is_string($parsedPath) && str_starts_with($parsedPath, '/storage/')) {
                $path = Str::after($parsedPath, '/storage/');
            }
        } elseif (str_starts_with($url, '/storage/')) {
            $path = Str::after($url, '/storage/');
        } elseif (! str_starts_with($url, '/')) {
            // Backward/forward compatible: value already stored as public disk relative path.
            $path = $url;
        }

        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function documentTypes(): array
    {
        $settings = MasterSetting::firstOrCreate([]);

        return $settings->employee_document_types ?: MasterSetting::DEFAULT_EMPLOYEE_DOCUMENT_TYPES;
    }

    private function storeNamedPublicFile($file, string $folder, Employee $employee, string $fallbackName): string
    {
        $extension = $file->extension() ?: $file->getClientOriginalExtension();
        $employeeName = str_replace(' ', '_', trim($employee->full_name)) ?: 'Employee';
        $documentType = str_replace(' ', '_', trim($fallbackName)) ?: 'document';
        $timestamp = now()->format('YmdHis');
        $filename = $employeeName.'-'.$documentType.'-'.$timestamp.'.'.$extension;

        return $file->storeAs($folder, $filename, 'public');
    }

    public function view(Request $request, string $view): View
    {
        $employees = Employee::paginate(15);
        return view("hr.employees.index", [
            'employees' => $employees,
            'viewMode' => $view,
        ]);
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $action = $request->input('action');
        $employeeIds = $request->input('employee_ids', []);

        if ($action === 'archive') {
            Employee::whereIn('id', $employeeIds)->update(['is_active' => false]);
        } elseif ($action === 'unarchive') {
            Employee::whereIn('id', $employeeIds)->update(['is_active' => true]);
        } elseif ($action === 'delete') {
            Employee::whereIn('id', $employeeIds)->delete();
        }

        return redirect()->route('employees.index')->with('status', 'Bulk action completed.');
    }
}
