@csrf
@php
    $employee = $employee ?? null;
    $work = $employee?->workInformation;
    $bank = $employee?->bankDetail;
    $showExtendedWorkFields = $showExtendedWorkFields ?? true;
    $documentTypes = $documentTypes ?? [];
    $relatedDocs = collect($employee?->related_document_paths ?? [])->map(function ($doc) {
        return is_array($doc) ? $doc : ['type' => 'Document', 'name' => basename((string) $doc), 'path' => (string) $doc];
    });
@endphp

@if($showModuleShortcuts ?? true)
    <div class="d-flex flex-wrap gap-2 mb-3">
        @foreach ([
            ['Contracts', 'bi-file-earmark-text'],
            ['Time Off', 'bi-calendar2-week'],
            ['Documents', 'bi-folder2-open'],
            ['Payslips', 'bi-receipt'],
            ['Timesheets', 'bi-clock-history'],
            ['Loans', 'bi-bank'],
        ] as [$label, $icon])
            <button type="button" class="btn btn-sm btn-light border">
                <i class="bi {{ $icon }}"></i>
                {{ $label }}
            </button>
        @endforeach
    </div>
@endif

<div class="row g-3 align-items-start">
    <div class="col-xl-9">
        <div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Badge ID</label>
        <input name="badge_id" value="{{ old('badge_id', $employee->badge_id ?? '') }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">First Name</label>
        <input name="first_name" value="{{ old('first_name', $employee->first_name ?? '') }}" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Last Name</label>
        <input name="last_name" value="{{ old('last_name', $employee->last_name ?? '') }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Gender</label>
        <select name="gender" class="form-select">
            <option value="">Select</option>
            @foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $value => $label)
                <option value="{{ $value }}" @selected(old('gender', $employee->gender ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Profile Image Upload</label>
        <input name="profile_photo_file" type="file" class="form-control" accept="image/jpeg,image/png,image/webp">
        <div class="form-text">JPG, PNG, or WebP. 100x100 to 3000x3000 px. Max 2 MB.</div>
    </div>
    <div class="col-md-4">
        <label class="form-label">Card Color</label>
        <input name="card_color" type="color" value="{{ old('card_color', $employee->card_color ?? '#6f42c1') }}" class="form-control form-control-color w-100">
    </div>
        </div>
    </div>
    <div class="col-xl-3">
        <div class="employee-form-avatar" style="background: {{ old('card_color', $employee->card_color ?? '#6f42c1') }}">
            @if (!empty($employee?->profile_photo_url))
                <img src="{{ $employee->profile_photo_url }}" alt="{{ $employee->full_name }}">
            @else
                <span>{{ $employee->initials ?? 'HR' }}</span>
            @endif
        </div>
    </div>
</div>

<ul class="nav nav-tabs mt-4" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#work-info" type="button">Work Information</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#private-info" type="button">Private Information</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#hr-settings" type="button">HR Settings</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#documents-info" type="button">Documents</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#bank-info" type="button">Bank</button>
    </li>
</ul>

<div class="tab-content border border-top-0 p-3 bg-white">
    <div class="tab-pane fade show active" id="work-info">
        <div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Email</label>
        <input name="email" type="email" value="{{ old('email', $employee->email ?? '') }}" class="form-control" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Phone</label>
        <input name="phone" value="{{ old('phone', $employee->phone ?? '') }}" class="form-control">
    </div>
            <div class="col-md-4">
                <label class="form-label">Work Mobile</label>
                <input name="work_mobile" value="{{ old('work_mobile', $work->work_mobile ?? '') }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Work Phone</label>
                <input name="work_phone" value="{{ old('work_phone', $work->work_phone ?? '') }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Work Email</label>
                <input name="work_email" type="email" value="{{ old('work_email', $work->email ?? '') }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Company</label>
                <select name="company_id" class="form-select">
                    <option value="">Select company</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" @selected((int) old('company_id', $work->company_id ?? 0) === $company->id)>{{ $company->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">Select department</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected((int) old('department_id', $work->department_id ?? 0) === $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Job Position</label>
                <select name="job_position_id" class="form-select">
                    <option value="">Select position</option>
                    @foreach ($jobPositions as $position)
                        <option value="{{ $position->id }}" @selected((int) old('job_position_id', $work->job_position_id ?? 0) === $position->id)>{{ $position->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Manager</label>
                <select name="reporting_manager_id" class="form-select">
                    <option value="">Select manager</option>
                    @foreach ($managers as $manager)
                        <option value="{{ $manager->id }}" @selected((int) old('reporting_manager_id', $work->reporting_manager_id ?? 0) === $manager->id)>{{ $manager->full_name }}</option>
                    @endforeach
                </select>
            </div>
            @if($showExtendedWorkFields)
                <div class="col-md-4">
                    <label class="form-label">Coach</label>
                    <select name="coach_id" class="form-select">
                        <option value="">Select coach</option>
                        @foreach ($managers as $manager)
                            <option value="{{ $manager->id }}" @selected((int) old('coach_id', $work->coach_id ?? 0) === $manager->id)>{{ $manager->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Work Location</label>
                    <input name="work_location" value="{{ old('work_location', $work->work_location ?? '') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Working Hours</label>
                    <input name="working_hours" value="{{ old('working_hours', $work->working_hours ?? '') }}" class="form-control" placeholder="Standard 40 hours/week">
                </div>
            @endif
            <div class="col-md-4">
                <label class="form-label">Timezone</label>
                <select name="timezone" class="form-select">
                    <option value="">Select timezone</option>
                    @foreach ($timezones as $timezone)
                        <option value="{{ $timezone }}" @selected(old('timezone', $work->timezone ?? '') === $timezone)>{{ $timezone }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="private-info">
        <div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Date of Birth</label>
        <input name="date_of_birth" type="date" value="{{ old('date_of_birth', optional($employee->date_of_birth ?? null)->format('Y-m-d')) }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Qualification</label>
        <input name="qualification" value="{{ old('qualification', $employee->qualification ?? '') }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Experience Years</label>
        <input name="experience_years" type="number" min="0" value="{{ old('experience_years', $employee->experience_years ?? '') }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Marital Status</label>
        <select name="marital_status" class="form-select">
            <option value="">Select</option>
            @foreach (['single' => 'Single', 'married' => 'Married', 'divorced' => 'Divorced'] as $value => $label)
                <option value="{{ $value }}" @selected(old('marital_status', $employee->marital_status ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label">Children</label>
        <input name="children_count" type="number" min="0" value="{{ old('children_count', $employee->children_count ?? '') }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Emergency Contact Name</label>
        <input name="emergency_contact_name" value="{{ old('emergency_contact_name', $employee->emergency_contact_name ?? '') }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Emergency Contact</label>
        <input name="emergency_contact" value="{{ old('emergency_contact', $employee->emergency_contact ?? '') }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Emergency Relation</label>
        <input name="emergency_contact_relation" value="{{ old('emergency_contact_relation', $employee->emergency_contact_relation ?? '') }}" class="form-control">
    </div>
    <div class="col-12">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control" rows="2">{{ old('address', $employee->address ?? '') }}</textarea>
    </div>
    <div class="col-md-3">
        <label class="form-label">Country</label>
        <input name="country" value="{{ old('country', $employee->country ?? '') }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">State</label>
        <input name="state" value="{{ old('state', $employee->state ?? '') }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">City</label>
        <input name="city" value="{{ old('city', $employee->city ?? '') }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">ZIP</label>
        <input name="zip" value="{{ old('zip', $employee->zip ?? '') }}" class="form-control">
    </div>
        </div>
    </div>

    <div class="tab-pane fade" id="hr-settings">
        <div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Date Joining</label>
        <input name="date_joining" type="date" value="{{ old('date_joining', optional($work->date_joining ?? null)->format('Y-m-d')) }}" class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label">Employment Type</label>
        <input name="employment_type" value="{{ old('employment_type', $work->employment_type ?? '') }}" class="form-control" placeholder="Permanent, contract, intern">
    </div>
    <div class="col-md-4">
        <label class="form-label">Access Role</label>
        <select name="access_level" class="form-select">
            <option value="employee" @selected(old('access_level', $employee?->user?->access_level ?? 'employee') === 'employee')>Employee</option>
            <option value="super_admin" @selected(old('access_level', $employee?->user?->access_level ?? 'employee') === 'super_admin')>Admin</option>
        </select>
        <div class="form-text">Employee is the default. Admin gives super admin access.</div>
    </div>
        </div>
    </div>

    <div class="tab-pane fade" id="documents-info">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">CV Upload</label>
                <input name="cv_file" type="file" class="form-control" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                <div class="form-text">PDF, DOC, or DOCX. Max 10 MB.</div>
                @if(isset($employee) && $employee?->cv_file_path)
                    <div class="mt-2">
                        <a href="{{ \Illuminate\Support\Facades\Storage::url($employee->cv_file_path) }}" target="_blank">View current CV</a>
                    </div>
                @endif
            </div>

            <div class="col-12">
                <label class="form-label">Employee Documents</label>
                <div class="employee-doc-upload-list" data-doc-upload-list>
                    <div class="employee-doc-upload-row">
                        <select name="related_document_types[]" class="form-select">
                            <option value="">Select document type</option>
                            @foreach($documentTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                        <input name="related_documents[]" type="file" class="form-control" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg">
                        <button type="button" class="btn btn-outline-secondary" data-remove-doc-row aria-label="Remove document row">&times;</button>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-light border mt-2" data-add-doc-row>
                    <i class="bi bi-plus-lg"></i>
                    Add Document
                </button>
                <div class="form-text">Upload documents one by one. Allowed: PDF, DOC, DOCX, PNG, JPG, JPEG. Max 10 MB each.</div>
            </div>

            @if($relatedDocs->isNotEmpty())
                <div class="col-12">
                    <div class="fw-semibold mb-2">Current Documents</div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>File</th>
                                    <th>Uploaded</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($relatedDocs as $doc)
                                    <tr>
                                        <td>{{ $doc['type'] ?? 'Document' }}</td>
                                        <td>
                                            @if(!empty($doc['path']))
                                                <a href="{{ \Illuminate\Support\Facades\Storage::url($doc['path']) }}" target="_blank">{{ $doc['name'] ?? basename($doc['path']) }}</a>
                                            @else
                                                {{ $doc['name'] ?? '-' }}
                                            @endif
                                        </td>
                                        <td>{{ $doc['uploaded_at'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="tab-pane fade" id="bank-info">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Bank Name</label>
                <input name="bank_name" value="{{ old('bank_name', $bank->bank_name ?? '') }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Account Number</label>
                <input name="account_number" value="{{ old('account_number', $bank->account_number ?? '') }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Account Holder</label>
                <input name="account_holder_name" value="{{ old('account_holder_name', $bank->account_holder_name ?? '') }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">IFSC / Routing Code</label>
                <input name="ifsc_code" value="{{ old('ifsc_code', $bank->ifsc_code ?? '') }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Branch</label>
                <input name="branch" value="{{ old('branch', $bank->branch ?? '') }}" class="form-control">
            </div>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <button class="btn btn-danger">{{ $submitLabel }}</button>
    <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">Cancel</a>
</div>

@push('styles')
    <style>
        .employee-form-avatar {
            align-items: center;
            color: #fff;
            display: flex;
            font-size: 3rem;
            font-weight: 700;
            height: 150px;
            justify-content: center;
            margin-left: auto;
            overflow: hidden;
            width: 150px;
        }
        .employee-form-avatar img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }
        .employee-doc-upload-list {
            display: grid;
            gap: 10px;
        }
        .employee-doc-upload-row {
            display: grid;
            gap: 10px;
            grid-template-columns: minmax(180px, 260px) minmax(220px, 1fr) auto;
        }
        @media (max-width: 900px) {
            .employee-doc-upload-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('click', function (event) {
            const addButton = event.target.closest('[data-add-doc-row]');
            const removeButton = event.target.closest('[data-remove-doc-row]');

            if (addButton) {
                const list = document.querySelector('[data-doc-upload-list]');
                const row = list.querySelector('.employee-doc-upload-row').cloneNode(true);

                row.querySelectorAll('select, input').forEach(function (field) {
                    field.value = '';
                });

                list.appendChild(row);
            }

            if (removeButton) {
                const list = removeButton.closest('[data-doc-upload-list]');
                const rows = list.querySelectorAll('.employee-doc-upload-row');

                if (rows.length > 1) {
                    removeButton.closest('.employee-doc-upload-row').remove();
                }
            }
        });
    </script>
@endpush
