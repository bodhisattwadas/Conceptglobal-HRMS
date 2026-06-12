@extends('layouts.app', [
    'heading' => 'Employees',
    'subheading' => $employee->full_name,
])

@section('content')
    @include('employees._module_nav')
    @php
        $relatedDocs = collect($employee->related_document_paths ?? [])->map(function ($doc) {
            return is_array($doc) ? $doc : ['type' => 'Document', 'name' => basename((string) $doc), 'path' => (string) $doc];
        });
    @endphp

    <div class="odoo-form-title">Employees / {{ $employee->full_name }}</div>
    <div class="odoo-actionbar">
        <div>
            <a href="{{ route('employees.edit', $employee) }}" class="odoo-primary">Edit</a>
            <a href="{{ route('employees.create') }}" class="odoo-secondary">Create</a>
        </div>
        <div class="odoo-record-pager">
            <span>{{ $employee->id }} / {{ \App\Models\Employee::count() }}</span>
        </div>
    </div>

    <div class="odoo-pattern-page">
        <section class="odoo-profile-sheet">
            <div class="odoo-profile-head">
                <div>
                    <h1>{{ $employee->full_name }}</h1>
                    <h2>{{ $employee->workInformation?->jobPosition?->name ?? 'Employee' }}</h2>
                </div>
                <div class="odoo-profile-photo">
                    @if ($employee->profile_photo_url)
                        <img src="{{ $employee->profile_photo_url }}" alt="{{ $employee->full_name }}">
                    @else
                        <span>{{ $employee->initials }}</span>
                    @endif
                </div>
            </div>

            <div class="odoo-profile-body">
                <div class="odoo-tabs-area">
                    <ul class="odoo-tabs" role="tablist">
                        <li><button class="active" data-bs-toggle="tab" data-bs-target="#work" type="button">Work Information</button></li>
                        <li><button data-bs-toggle="tab" data-bs-target="#private" type="button">Private Information</button></li>
                        <li><button data-bs-toggle="tab" data-bs-target="#hr" type="button">HR Settings</button></li>
                        <li><button data-bs-toggle="tab" data-bs-target="#documents" type="button">Documents</button></li>
                        <li><button data-bs-toggle="tab" data-bs-target="#bank" type="button">Bank</button></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="work">
                            <div class="odoo-section-title">Basic</div>
                            <div class="odoo-form-line"><b>Badge ID</b><span>{{ $employee->badge_id ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Card Color</b><span>{{ $employee->card_color ?? '-' }}</span></div>
                            <div class="odoo-section-title">Contact</div>
                            <div class="odoo-form-line"><b>Email</b><span>{{ $employee->email ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Phone</b><span>{{ $employee->phone ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Work Mobile</b><span>{{ $employee->workInformation?->work_mobile ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Work Phone</b><span>{{ $employee->workInformation?->work_phone ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Work Email</b><span>{{ $employee->workInformation?->email ?? '-' }}</span></div>
                            <div class="odoo-section-title">Employment</div>
                            <div class="odoo-form-line"><b>Company</b><span>{{ $employee->workInformation?->company?->name ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Department</b><span>{{ $employee->workInformation?->department?->name ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Job Position</b><span>{{ $employee->workInformation?->jobPosition?->name ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Manager</b><span>{{ $employee->workInformation?->reportingManager?->full_name ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Coach</b><span>{{ $employee->workInformation?->coach?->full_name ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Work Location</b><span>{{ $employee->workInformation?->work_location ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Working Hours</b><span>{{ $employee->workInformation?->working_hours ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Timezone</b><span>{{ $employee->workInformation?->timezone ?? '-' }}</span></div>
                        </div>
                        <div class="tab-pane fade" id="private">
                            <div class="odoo-section-title">Personal</div>
                            <div class="odoo-form-line"><b>First Name</b><span>{{ $employee->first_name ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Last Name</b><span>{{ $employee->last_name ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Gender</b><span>{{ $employee->gender ? ucfirst($employee->gender) : '' }}</span></div>
                            <div class="odoo-form-line"><b>Date of Birth</b><span>{{ $employee->date_of_birth?->format('m/d/Y') }}</span></div>
                            <div class="odoo-form-line"><b>Qualification</b><span>{{ $employee->qualification ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Experience Years</b><span>{{ $employee->experience_years ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Marital Status</b><span>{{ $employee->marital_status ? ucfirst($employee->marital_status) : '-' }}</span></div>
                            <div class="odoo-form-line"><b>Children</b><span>{{ $employee->children_count ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Emergency Contact Name</b><span>{{ $employee->emergency_contact_name ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Emergency Contact</b><span>{{ $employee->emergency_contact ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Emergency Relation</b><span>{{ $employee->emergency_contact_relation ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Address</b><span>{{ $employee->address ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Country</b><span>{{ $employee->country ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>State</b><span>{{ $employee->state ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>City</b><span>{{ $employee->city ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>ZIP</b><span>{{ $employee->zip ?? '-' }}</span></div>
                        </div>
                        <div class="tab-pane fade" id="hr">
                            <div class="odoo-section-title">Status</div>
                            <div class="odoo-form-line"><b>Employee Type</b><span>{{ $employee->workInformation?->employment_type ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Joining Date</b><span>{{ $employee->workInformation?->date_joining?->format('m/d/Y') ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Access Role</b><span>{{ $employee->user?->access_level ?? '-' }}</span></div>
                        </div>
                        <div class="tab-pane fade" id="documents">
                            <div class="odoo-section-title">Files</div>
                            <div class="odoo-form-line">
                                <b>Profile Photo</b>
                                <span>
                                    @if ($employee->profile_photo_url)
                                        <a href="{{ $employee->profile_photo_url }}" target="_blank">View photo</a>
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            <div class="odoo-form-line">
                                <b>CV</b>
                                <span>
                                    @if ($employee->cv_file_path)
                                        <a href="{{ route('employees.cv.download', $employee) }}" class="btn btn-sm btn-outline-primary">Download CV</a>
                                    @else
                                        -
                                    @endif
                                </span>
                            </div>
                            <div class="odoo-form-line">
                                <b>Related Documents</b>
                                <span>{{ $relatedDocs->count() }}</span>
                            </div>
                            <div class="odoo-section-title">Uploaded Documents</div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>File</th>
                                            <th>Uploaded At</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($relatedDocs as $doc)
                                            <tr>
                                                <td>{{ $doc['type'] ?? 'Document' }}</td>
                                                <td>{{ $doc['name'] ?? ( !empty($doc['path']) ? basename($doc['path']) : '-' ) }}</td>
                                                <td>{{ $doc['uploaded_at'] ?? '-' }}</td>
                                                <td>
                                                    @if (!empty($doc['path']))
                                                        <a href="{{ route('employees.documents.download', [$employee, $loop->index]) }}" class="btn btn-sm btn-outline-primary">Download</a>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-muted">No uploaded documents found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="bank">
                            <div class="odoo-section-title">Bank Details</div>
                            <div class="odoo-form-line"><b>Bank Name</b><span>{{ $employee->bankDetail?->bank_name ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Account Number</b><span>{{ $employee->bankDetail?->account_number ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Account Holder</b><span>{{ $employee->bankDetail?->account_holder_name ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>IFSC / Routing Code</b><span>{{ $employee->bankDetail?->ifsc_code ?? '-' }}</span></div>
                            <div class="odoo-form-line"><b>Branch</b><span>{{ $employee->bankDetail?->branch ?? '-' }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="odoo-profile-sheet mt-3 p-3">
            <div class="odoo-section-title">Uploaded Documents</div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>File</th>
                            <th>Uploaded At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($relatedDocs as $doc)
                            <tr>
                                <td>{{ $doc['type'] ?? 'Document' }}</td>
                                <td>
                                    {{ $doc['name'] ?? ( !empty($doc['path']) ? basename($doc['path']) : '-' ) }}
                                </td>
                                <td>{{ $doc['uploaded_at'] ?? '-' }}</td>
                                <td>
                                    @if (!empty($doc['path']))
                                        <a href="{{ route('employees.documents.download', [$employee, $loop->index]) }}" class="btn btn-sm btn-outline-primary">Download</a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-muted">No uploaded documents found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        body { background: #fff; font-family: Arial, Helvetica, sans-serif; font-size: 13px; }
        .odoo-topbar { margin: -1.5rem -1.5rem 0; }
        .content > section { padding-bottom: 0 !important; }
        .odoo-form-title { color: #6e4c94; font-size: 18px; padding: 13px 15px 8px; }
        .odoo-actionbar {
            align-items: center;
            background: #fff;
            border-bottom: 1px solid #d9dde4;
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            padding: 5px 14px 10px;
        }
        .odoo-primary, .odoo-secondary {
            background: #7e57a3;
            border: 1px solid #7e57a3;
            color: #fff;
            display: inline-block;
            font-size: 13px;
            padding: 7px 12px;
            text-decoration: none;
        }
        .odoo-secondary { background: #fff; border-color: #d8dde6; color: #111827; }
        .odoo-record-pager { align-items: center; display: flex; gap: 14px; justify-content: flex-end; }
        .odoo-record-pager a { color: #111827; font-size: 22px; }
        .odoo-pattern-page {
            background-color: #f4f4f4;
            background-image: radial-gradient(#d3d3d3 .6px, transparent .6px);
            background-size: 3px 3px;
            min-height: calc(100vh - 132px);
            padding: 5px 0 18px;
        }
        .odoo-profile-sheet { background: #fff; border: 1px solid #c8ced8; margin: 0 auto; max-width: 1140px; min-height: 936px; }
        .odoo-profile-head { align-items: start; display: flex; justify-content: space-between; padding: 118px 15px 0; }
        .odoo-profile-head h1 { color: #07111f; font-size: 27px; font-weight: 700; margin: 0 0 22px; }
        .odoo-profile-head h2 { font-size: 20px; font-weight: 700; margin: 0; }
        .odoo-profile-photo { background: #dfe3e8; color: #fff; display: grid; font-size: 28px; height: 89px; margin-right: 1px; place-items: center; width: 89px; }
        .odoo-profile-photo img, .org-avatar img { height: 100%; object-fit: cover; width: 100%; }
        .odoo-profile-body { display: grid; grid-template-columns: 1fr 332px; padding: 4px 15px 40px; }
        .odoo-tabs { border-bottom: 1px solid #d8dde6; display: flex; list-style: none; margin: 0 0 16px; padding: 0; }
        .odoo-tabs button { background: #fff; border: 0; color: #6e36a2; padding: 8px 13px; }
        .odoo-tabs button.active { border: 1px solid #d8dde6; border-bottom-color: #fff; color: #1f2a44; margin-bottom: -1px; }
        .odoo-section-title { border-bottom: 1px solid #eff1f4; color: #6e36a2; font-size: 11px; font-weight: 700; margin: 24px 40px 9px 0; padding-bottom: 4px; }
        .odoo-form-line { display: grid; grid-template-columns: 150px 1fr; margin-bottom: 12px; }
        .odoo-form-line span { border-left: 1px solid #d8dde6; color: #6e36a2; min-height: 24px; padding-left: 9px; }
        @media (max-width: 900px) {
            .odoo-actionbar, .odoo-contact-grid, .odoo-profile-body { grid-template-columns: 1fr; }
            .odoo-profile-head { padding-top: 35px; }
            .odoo-profile-sheet { min-height: 0; }
        }
    </style>
@endpush
