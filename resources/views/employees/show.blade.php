@extends('layouts.app', [
    'heading' => 'Employees',
    'subheading' => $employee->full_name,
])

@section('content')
    @include('employees._module_nav')

    <div class="odoo-form-title">Employees / {{ $employee->full_name }}</div>
    <div class="odoo-actionbar">
        <div>
            <a href="{{ route('employees.edit', $employee) }}" class="odoo-primary">Edit</a>
            <a href="{{ route('employees.create') }}" class="odoo-secondary">Create</a>
        </div>
        <div>
            <button class="odoo-secondary"><i class="bi bi-printer-fill"></i> Print</button>
            <button class="odoo-secondary"><i class="bi bi-gear-fill"></i> Action</button>
        </div>
        <div class="odoo-record-pager">
            <span>{{ $employee->id }} / {{ \App\Models\Employee::count() }}</span>
            <a href="#"><i class="bi bi-chevron-left"></i></a>
            <a href="#"><i class="bi bi-chevron-right"></i></a>
        </div>
    </div>

    <div class="odoo-pattern-page">
        <section class="odoo-profile-sheet">
            <div class="odoo-smart-row">
                @foreach ($smartButtons as $button)
                    <a href="{{ $button['url'] ?? '#' }}" class="odoo-smart-button">
                        <i class="bi {{ $button['icon'] }}"></i>
                        <span class="smart-value">{{ $button['value'] }}</span>
                        <span>{{ $button['label'] }}</span>
                    </a>
                @endforeach
                <a href="#" class="odoo-smart-button more">More <i class="bi bi-caret-down-fill"></i></a>
            </div>

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

            <div class="odoo-contact-grid">
                <div>
                    <div class="odoo-field"><b>Work Mobile</b><span>{{ $employee->workInformation?->work_mobile }}</span></div>
                    <div class="odoo-field"><b>Work Phone</b><span>{{ $employee->workInformation?->work_phone }}</span></div>
                    <div class="odoo-field"><b>Work Email</b><span>{{ $employee->workInformation?->email ?? $employee->email }}</span></div>
                </div>
                <div>
                    <div class="odoo-field"><b>Department</b><span>{{ $employee->workInformation?->department?->name }}</span></div>
                    <div class="odoo-field"><b>Manager</b><span>{{ $employee->workInformation?->reportingManager?->full_name ?? 'Mitchell Admin' }}</span></div>
                    <div class="odoo-field"><b>Coach</b><span>{{ $employee->workInformation?->coach?->full_name ?? 'Mitchell Admin' }}</span></div>
                </div>
            </div>

            <div class="odoo-profile-body">
                <div class="odoo-tabs-area">
                    <ul class="odoo-tabs" role="tablist">
                        <li><button class="active" data-bs-toggle="tab" data-bs-target="#work" type="button">Work Information</button></li>
                        <li><button data-bs-toggle="tab" data-bs-target="#private" type="button">Private Information</button></li>
                        <li><button data-bs-toggle="tab" data-bs-target="#hr" type="button">HR Settings</button></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="work">
                            <div class="odoo-section-title">Location</div>
                            <div class="odoo-form-line"><b>Work Address</b><span>YourCompany<br>250 Executive Park Blvd, Suite 3400<br>San Francisco CA 94134<br>United States</span></div>
                            <div class="odoo-form-line"><b>Work Location</b><span>{{ $employee->workInformation?->work_location ?? 'Building 1, Second Floor' }}</span></div>
                            <div class="odoo-section-title">Approvers</div>
                            <div class="odoo-form-line"><b>Time Off</b><span>Mitchell Admin</span></div>
                            <div class="odoo-form-line"><b>Expense</b><span>Mitchell Admin</span></div>
                            <div class="odoo-section-title">Schedule</div>
                            <div class="odoo-form-line"><b>Working Hours</b><span>{{ $employee->workInformation?->working_hours }}</span></div>
                            <div class="odoo-form-line"><b>Timezone</b><span>{{ $employee->workInformation?->timezone ?? 'Europe/Brussels' }}</span></div>
                        </div>
                        <div class="tab-pane fade" id="private">
                            <div class="odoo-section-title">Contact</div>
                            <div class="odoo-form-line"><b>Private Email</b><span>{{ $employee->email }}</span></div>
                            <div class="odoo-form-line"><b>Private Phone</b><span>{{ $employee->phone }}</span></div>
                            <div class="odoo-form-line"><b>Address</b><span>{{ collect([$employee->address, $employee->city, $employee->state, $employee->country, $employee->zip])->filter()->join(', ') }}</span></div>
                            <div class="odoo-section-title">Personal</div>
                            <div class="odoo-form-line"><b>Gender</b><span>{{ $employee->gender ? ucfirst($employee->gender) : '' }}</span></div>
                            <div class="odoo-form-line"><b>Date of Birth</b><span>{{ $employee->date_of_birth?->format('m/d/Y') }}</span></div>
                            <div class="odoo-form-line"><b>Marital Status</b><span>{{ $employee->marital_status ? ucfirst($employee->marital_status) : '' }}</span></div>
                            <div class="odoo-section-title">Documents</div>
                            <div class="odoo-form-line">
                                <b>CV</b>
                                <span>
                                    @if($employee->cv_file_path)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($employee->cv_file_path) }}" target="_blank">Download CV</a>
                                    @endif
                                </span>
                            </div>
                            <div class="odoo-form-line">
                                <b>Related Docs</b>
                                <span>
                                    @if(!empty($employee->related_document_paths))
                                        @foreach($employee->related_document_paths as $docPath)
                                            <a href="{{ \Illuminate\Support\Facades\Storage::url($docPath) }}" target="_blank">{{ basename($docPath) }}</a>@if(!$loop->last), @endif
                                        @endforeach
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="hr">
                            <div class="odoo-section-title">Status</div>
                            <div class="odoo-form-line"><b>Employee Type</b><span>{{ $employee->workInformation?->employment_type ?? 'Employee' }}</span></div>
                            <div class="odoo-form-line"><b>Joining Date</b><span>{{ $employee->workInformation?->date_joining?->format('m/d/Y') }}</span></div>
                            <div class="odoo-section-title">Tracking</div>
                            <div class="odoo-form-line"><b>Badge ID</b><span>{{ $employee->badge_id }}</span></div>
                        </div>
                    </div>
                </div>

                <aside class="odoo-org-chart">
                    <h3>Organization Chart</h3>
                    <div class="org-node">
                        <span class="org-avatar">MA</span>
                        <span><b>Mitchell Admin</b><small>Chief Executive Officer</small></span>
                        <em>21</em>
                    </div>
                    <div class="org-branch">
                        <div class="org-node current">
                            <span class="org-avatar">
                                @if ($employee->profile_photo_url)
                                    <img src="{{ $employee->profile_photo_url }}" alt="">
                                @else
                                    {{ $employee->initials }}
                                @endif
                            </span>
                            <span><b>{{ $employee->full_name }}</b><small>{{ $employee->workInformation?->jobPosition?->name ?? 'Employee' }}</small></span>
                            <em>2</em>
                        </div>
                        @forelse ($orgReports as $report)
                            <div class="org-node child">
                                <span class="org-avatar">{{ $report->initials }}</span>
                                <span><b>{{ $report->full_name }}</b><small>{{ $report->workInformation?->jobPosition?->name ?? 'Employee' }}</small></span>
                            </div>
                        @empty
                            <div class="org-node child"><span class="org-avatar">AO</span><span><b>Anita Oliver</b><small>Experienced Developer</small></span></div>
                            <div class="org-node child"><span class="org-avatar">AP</span><span><b>Audrey Peterson</b><small>Consultant</small></span></div>
                        @endforelse
                    </div>
                </aside>
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
        .odoo-smart-row { border-bottom: 1px solid #d8dde6; display: flex; height: 44px; }
        .odoo-smart-button {
            align-items: center;
            border-right: 1px solid #d8dde6;
            color: #5f6775;
            display: flex;
            gap: 9px;
            min-width: 142px;
            padding: 7px 16px;
            text-decoration: none;
        }
        .odoo-smart-button i { color: #9a8abf; font-size: 22px; }
        .odoo-smart-button:first-child i { color: #ffb52e; font-size: 18px; }
        .smart-value { color: #7e57a3; font-weight: 700; }
        .odoo-smart-button.more { justify-content: center; margin-left: auto; min-width: 140px; }
        .odoo-profile-head { align-items: start; display: flex; justify-content: space-between; padding: 118px 15px 0; }
        .odoo-profile-head h1 { color: #07111f; font-size: 27px; font-weight: 700; margin: 0 0 22px; }
        .odoo-profile-head h2 { font-size: 20px; font-weight: 700; margin: 0; }
        .odoo-profile-photo { background: #dfe3e8; color: #fff; display: grid; font-size: 28px; height: 89px; margin-right: 1px; place-items: center; width: 89px; }
        .odoo-profile-photo img, .org-avatar img { height: 100%; object-fit: cover; width: 100%; }
        .odoo-contact-grid { display: grid; gap: 70px; grid-template-columns: 1fr 1fr; padding: 50px 15px 0; }
        .odoo-field { display: grid; grid-template-columns: 150px 1fr; margin-bottom: 16px; }
        .odoo-field span { border-left: 1px solid #d8dde6; color: #6e36a2; min-height: 18px; padding-left: 9px; }
        .odoo-profile-body { display: grid; grid-template-columns: 1fr 332px; padding: 4px 15px 40px; }
        .odoo-tabs { border-bottom: 1px solid #d8dde6; display: flex; list-style: none; margin: 0 0 16px; padding: 0; }
        .odoo-tabs button { background: #fff; border: 0; color: #6e36a2; padding: 8px 13px; }
        .odoo-tabs button.active { border: 1px solid #d8dde6; border-bottom-color: #fff; color: #1f2a44; margin-bottom: -1px; }
        .odoo-section-title { border-bottom: 1px solid #eff1f4; color: #6e36a2; font-size: 11px; font-weight: 700; margin: 24px 40px 9px 0; padding-bottom: 4px; }
        .odoo-form-line { display: grid; grid-template-columns: 150px 1fr; margin-bottom: 12px; }
        .odoo-form-line span { border-left: 1px solid #d8dde6; color: #6e36a2; min-height: 24px; padding-left: 9px; }
        .odoo-org-chart { border-left: 1px solid #e4e7ec; margin-top: 17px; padding-left: 22px; }
        .odoo-org-chart h3 { color: #64748b; font-size: 16px; font-weight: 700; margin-bottom: 14px; }
        .org-node { align-items: center; display: grid; gap: 9px; grid-template-columns: 42px 1fr auto; margin: 7px 0; position: relative; }
        .org-node small { color: #64748b; display: block; font-size: 11px; }
        .org-avatar { background: #a986c5; border-radius: 50%; color: #fff; display: grid; font-size: 12px; font-weight: 700; height: 38px; overflow: hidden; place-items: center; width: 38px; }
        .org-node em { border: 1px solid #9aa6b2; border-radius: 9px; color: #64748b; font-size: 11px; font-style: normal; padding: 0 7px; }
        .org-branch { border-left: 1px solid #bfc6d1; margin-left: 19px; padding-left: 11px; }
        .org-node.current .org-avatar { outline: 2px solid #8a5eb0; }
        .org-node.child { margin-left: 32px; }
        @media (max-width: 900px) {
            .odoo-actionbar, .odoo-contact-grid, .odoo-profile-body { grid-template-columns: 1fr; }
            .odoo-smart-row { height: auto; overflow-x: auto; }
            .odoo-profile-head { padding-top: 35px; }
            .odoo-profile-sheet { min-height: 0; }
        }
    </style>
@endpush
