<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\MasterSetting;
use App\Models\Timesheet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EmployeePortalController extends Controller
{
    public function dashboard(Request $request): View
    {
        $employee = $request->user()->employee()
            ->with('workInformation.department', 'assignedProjects')
            ->firstOrFail();

        $timesheets = Timesheet::desktopSynced()
            ->with('project')
            ->where('employee_id', $employee->id)
            ->latest('date')
            ->latest('id')
            ->limit(20)
            ->get();

        return view('employee-portal.dashboard', [
            'employee' => $employee,
            'projects' => $employee->assignedProjects()->orderBy('name')->get(),
            'timesheets' => $timesheets,
            'totalHours' => $timesheets->sum(fn (Timesheet $row) => (float) $row->hours_spent),
        ]);
    }

    public function editProfile(Request $request): View
    {
        $employee = $this->employeeFor($request);

        return view('employee-portal.profile-edit', [
            'employee' => $employee,
            'documentTypes' => $this->documentTypes(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $employee = $this->employeeFor($request);

        $data = $request->validate([
            'profile_photo_file' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
                'dimensions:min_width=100,min_height=100,max_width=3000,max_height=3000',
            ],
            'first_name' => ['required', 'string', 'max:200'],
            'last_name' => ['nullable', 'string', 'max:200'],
            'phone' => ['nullable', 'string', 'max:30'],
            'gender' => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date'],
            'address' => ['nullable', 'string'],
            'country' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'zip' => ['nullable', 'string', 'max:30'],
            'cv_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'related_document_types' => ['nullable', 'array'],
            'related_document_types.*' => ['nullable', 'string', 'max:100'],
            'related_documents' => ['nullable', 'array'],
            'related_documents.*' => ['nullable', 'file', 'mimes:pdf,doc,docx,png,jpg,jpeg', 'max:10240'],
        ]);

        if ($request->hasFile('profile_photo_file')) {
            $this->deleteStoredPublicFile($employee->profile_photo_url);
            $data['profile_photo_url'] = Storage::url($this->storeProfilePhoto($request->file('profile_photo_file'), $employee));
        }

        if ($request->hasFile('cv_file')) {
            if ($employee->cv_file_path) {
                Storage::disk('public')->delete($employee->cv_file_path);
            }

            $data['cv_file_path'] = $this->storeNamedPublicFile($request->file('cv_file'), $this->employeeFolder($employee).'/cv', 'cv');
        }

        if ($request->hasFile('related_documents')) {
            $existingDocs = $employee->related_document_paths ?? [];
            $newDocs = [];
            $documentTypes = $request->input('related_document_types', []);

            foreach ($request->file('related_documents', []) as $index => $file) {
                if ($file) {
                    $type = trim((string) ($documentTypes[$index] ?? ''));
                    $path = $this->storeNamedPublicFile($file, $this->employeeFolder($employee).'/related-documents', $type ?: 'document');

                    $newDocs[] = [
                        'type' => $type ?: 'Document',
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'uploaded_at' => now()->toDateTimeString(),
                    ];
                }
            }

            $data['related_document_paths'] = array_values(array_merge($existingDocs, $newDocs));
        }

        unset($data['profile_photo_file'], $data['cv_file'], $data['related_document_types'], $data['related_documents']);

        $employee->update($data);
        $request->user()->update(['name' => $employee->fresh()->full_name ?: $request->user()->name]);

        return redirect()->route('employee.dashboard')->with('status', 'Profile updated.');
    }

    private function employeeFor(Request $request): Employee
    {
        return $request->user()->employee()->firstOrFail();
    }

    private function storeProfilePhoto($file, Employee $employee): string
    {
        $extension = $file->extension() ?: $file->getClientOriginalExtension();
        $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'profile-photo';

        return $file->storeAs($this->employeeFolder($employee).'/photo', $filename.'-'.now()->format('YmdHis').'.'.$extension, 'public');
    }

    private function storeNamedPublicFile($file, string $folder, string $fallbackName): string
    {
        $extension = $file->extension() ?: $file->getClientOriginalExtension();
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: Str::slug($fallbackName);

        return $file->storeAs($folder, $name.'-'.now()->format('YmdHis').'.'.$extension, 'public');
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
        if (! $url || ! str_starts_with($url, '/storage/')) {
            return;
        }

        Storage::disk('public')->delete(Str::after($url, '/storage/'));
    }

    private function documentTypes(): array
    {
        $settings = MasterSetting::firstOrCreate([]);

        return $settings->employee_document_types ?: MasterSetting::DEFAULT_EMPLOYEE_DOCUMENT_TYPES;
    }
}
