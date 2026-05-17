@extends('layouts.app', [
    'heading' => 'Employee Profile',
    'subheading' => $employee->full_name,
])

@section('content')
    @include('employees._module_nav')

    <div class="employee-profile-workspace">
        <div class="profile-actionbar">
            <div class="d-flex gap-2">
                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm btn-primary">Edit</a>
                <a href="{{ route('employees.index') }}" class="btn btn-sm btn-outline-secondary">Discard</a>
            </div>
            <div class="d-flex align-items-center gap-3 small text-secondary">
                <span>{{ $employee->id }} / {{ \App\Models\Employee::count() }}</span>
                <a href="{{ route('employees.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-chevron-left"></i></a>
                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm btn-light border"><i class="bi bi-chevron-right"></i></a>
            </div>
        </div>

        <div class="smart-button-strip">
            @foreach ($smartButtons as $button)
                <button class="smart-button" type="button">
                    <i class="bi {{ $button['icon'] }}"></i>
                    <span class="smart-value">{{ $button['value'] }}</span>
                    <span>{{ $button['label'] }}</span>
                </button>
            @endforeach
            <button class="smart-button" type="button">
                <i class="bi bi-three-dots"></i>
                <span>More</span>
            </button>
        </div>

        <div class="profile-sheet">
            <div class="profile-main">
                <div class="profile-header">
                    <div class="flex-grow-1">
                        <h2>{{ $employee->full_name }}</h2>
                        <div class="profile-job">{{ $employee->workInformation?->jobPosition?->name ?? 'Employee' }}</div>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <span class="badge text-bg-danger">Employee</span>
                            <span class="badge {{ $employee->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $employee->is_active ? 'Connected' : 'Archived' }}
                            </span>
                        </div>
                    </div>
                    <div class="profile-photo" style="background: {{ $employee->card_color }}">
                        @if ($employee->profile_photo_url)
                            <img src="{{ $employee->profile_photo_url }}" alt="{{ $employee->full_name }}">
                        @else
                            <span>{{ $employee->initials }}</span>
                        @endif
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <x-profile-field label="Work Mobile" :value="$employee->workInformation?->work_mobile" />
                    <x-profile-field label="Work Phone" :value="$employee->workInformation?->work_phone" />
                    <x-profile-field label="Work Email" :value="$employee->workInformation?->email ?? $employee->email" />
                    <x-profile-field label="Company" :value="$employee->workInformation?->company?->name" />
                    <x-profile-field label="Department" :value="$employee->workInformation?->department?->name" />
                    <x-profile-field label="Manager" :value="$employee->workInformation?->reportingManager?->full_name" />
                    <x-profile-field label="Coach" :value="$employee->workInformation?->coach?->full_name" />
                </div>

                <ul class="nav nav-tabs mt-4" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#about" type="button">Work Information</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#private" type="button">Private Information</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#hr" type="button">HR Settings</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#bank" type="button">Bank</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#documents" type="button">Documents / Notes</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#history" type="button">History</button></li>
                </ul>

                <div class="tab-content profile-tabs">
                    <div class="tab-pane fade show active" id="about">
                        <div class="row g-3">
                            <x-profile-field label="Work Address" :value="collect([$employee->workInformation?->company?->name, $employee->workInformation?->work_location])->filter()->join(' - ')" wide />
                            <x-profile-field label="Work Location" :value="$employee->workInformation?->work_location" />
                            <x-profile-field label="Working Hours" :value="$employee->workInformation?->working_hours" />
                            <x-profile-field label="Timezone" :value="$employee->workInformation?->timezone" />
                        </div>
                    </div>
                    <div class="tab-pane fade" id="private">
                        <div class="row g-3">
                            <x-profile-field label="Private Email" :value="$employee->email" />
                            <x-profile-field label="Private Phone" :value="$employee->phone" />
                            <x-profile-field label="Date of Birth" :value="$employee->date_of_birth?->format('d M Y')" />
                            <x-profile-field label="Gender" :value="$employee->gender ? ucfirst($employee->gender) : null" />
                            <x-profile-field label="Qualification" :value="$employee->qualification" />
                            <x-profile-field label="Experience" :value="$employee->experience_years !== null ? $employee->experience_years . ' years' : null" />
                            <x-profile-field label="Marital Status" :value="$employee->marital_status ? ucfirst($employee->marital_status) : null" />
                            <x-profile-field label="Children" :value="$employee->children_count" />
                            <x-profile-field label="Address" :value="collect([$employee->address, $employee->city, $employee->state, $employee->country, $employee->zip])->filter()->join(', ')" wide />
                            <x-profile-field label="Emergency Contact" :value="collect([$employee->emergency_contact_name, $employee->emergency_contact, $employee->emergency_contact_relation])->filter()->join(' / ')" wide />
                        </div>
                    </div>
                    <div class="tab-pane fade" id="hr">
                        <div class="row g-3">
                            <x-profile-field label="Badge ID" :value="$employee->badge_id" />
                            <x-profile-field label="Joining Date" :value="$employee->workInformation?->date_joining?->format('d M Y')" />
                            <x-profile-field label="Employment Type" :value="$employee->workInformation?->employment_type" />
                        </div>
                    </div>
                    <div class="tab-pane fade" id="bank">
                        <div class="row g-3">
                            <x-profile-field label="Bank" :value="$employee->bankDetail?->bank_name" />
                            <x-profile-field label="Account Holder" :value="$employee->bankDetail?->account_holder_name" />
                            <x-profile-field label="Account Number" :value="$employee->bankDetail?->account_number" />
                            <x-profile-field label="IFSC / Routing Code" :value="$employee->bankDetail?->ifsc_code" />
                            <x-profile-field label="Branch" :value="$employee->bankDetail?->branch" />
                        </div>
                    </div>
                    <div class="tab-pane fade" id="documents">
                        <div class="module-placeholder">
                            Documents, admin requests, notes, and approvals will be wired here after the employee foundation is complete.
                        </div>
                    </div>
                    <div class="tab-pane fade" id="history">
                        <div class="module-placeholder">
                            Audit history will show profile, work-info, and bank-detail changes.
                        </div>
                    </div>
                </div>
            </div>

            <aside class="org-chart-panel">
                <div class="fw-semibold mb-3">Organization Chart</div>
                @if ($employee->workInformation?->reportingManager)
                    <div class="org-node manager">
                        <div class="org-dot">{{ $employee->workInformation->reportingManager->initials }}</div>
                        <div>
                            <div class="fw-semibold">{{ $employee->workInformation->reportingManager->full_name }}</div>
                            <div class="small text-secondary">{{ $employee->workInformation->reportingManager->workInformation?->jobPosition?->name ?? 'Manager' }}</div>
                        </div>
                    </div>
                    <div class="org-line"></div>
                @endif
                <div class="org-node">
                    <div class="org-dot current">{{ $employee->initials }}</div>
                    <div>
                        <div class="fw-semibold">{{ $employee->full_name }}</div>
                        <div class="small text-secondary">{{ $employee->workInformation?->jobPosition?->name ?? 'Employee' }}</div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .employee-profile-workspace {
            background: #fff;
            border: 1px solid #d9dee7;
        }
        .profile-actionbar,
        .smart-button-strip {
            align-items: center;
            border-bottom: 1px solid #e4e7ef;
            display: flex;
            justify-content: space-between;
            padding: .55rem .75rem;
        }
        .smart-button-strip {
            justify-content: flex-start;
            gap: .35rem;
            overflow-x: auto;
        }
        .smart-button {
            align-items: center;
            background: #fff;
            border: 1px solid #dfe3eb;
            color: #4b5563;
            display: flex;
            gap: .4rem;
            min-height: 42px;
            padding: .35rem .65rem;
            white-space: nowrap;
        }
        .smart-value {
            color: #111827;
            font-weight: 700;
        }
        .profile-sheet {
            display: grid;
            grid-template-columns: 1fr 310px;
            margin: 0 auto;
            max-width: 1220px;
            min-height: 620px;
        }
        .profile-main {
            border-right: 1px solid #e4e7ef;
            padding: 2rem;
        }
        .profile-header {
            display: flex;
            gap: 1.5rem;
        }
        .profile-header h2 {
            background: #ded6ff;
            border: 1px solid #a391db;
            font-size: 1.6rem;
            margin: 0 0 .5rem;
            padding: .15rem .35rem;
        }
        .profile-job {
            border: 1px solid #d8dde7;
            color: #374151;
            max-width: 580px;
            padding: .35rem .5rem;
        }
        .profile-photo {
            align-items: center;
            color: #fff;
            display: flex;
            flex: 0 0 150px;
            font-size: 3rem;
            font-weight: 700;
            height: 150px;
            justify-content: center;
            overflow: hidden;
        }
        .profile-photo img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }
        .profile-tabs {
            border: 1px solid #dee2e6;
            border-top: 0;
            padding: 1rem;
        }
        .org-chart-panel {
            padding: 2rem 1.25rem;
        }
        .org-node {
            align-items: center;
            display: flex;
            gap: .75rem;
        }
        .org-dot {
            align-items: center;
            background: #8b7bb7;
            border-radius: 50%;
            color: #fff;
            display: flex;
            flex: 0 0 42px;
            font-weight: 700;
            height: 42px;
            justify-content: center;
            width: 42px;
        }
        .org-dot.current {
            background: #6f5b9a;
            outline: 3px solid #e6e1f3;
        }
        .org-line {
            border-left: 2px solid #cbd5e1;
            height: 32px;
            margin-left: 20px;
        }
        .module-placeholder {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            color: #64748b;
            padding: 1.5rem;
        }
        @media (max-width: 1100px) {
            .profile-sheet { grid-template-columns: 1fr; }
            .profile-main { border-right: 0; }
            .org-chart-panel { border-top: 1px solid #e4e7ef; }
        }
    </style>
@endpush
