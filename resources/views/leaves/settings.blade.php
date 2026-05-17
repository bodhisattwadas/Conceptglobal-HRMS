@extends('layouts.app', ['heading' => 'Leaves', 'subheading' => 'Settings'])


@section('content')
    @include('leaves._nav', ['appTitle' => 'Leaves'])

    <form method="post" action="{{ route('leaves.settings.update') }}">
        @csrf
        <div class="oh-page-title">
            <h1>Settings</h1>
            <div class="oh-searchbar">
                <input class="form-control form-control-sm" placeholder="Search...">
                <i class="bi bi-search"></i>
            </div>
        </div>
        <div class="oh-actions">
            <div class="d-flex gap-2">
                <button class="btn btn-oh">Save</button>
                <a href="{{ route('leaves.types') }}" class="btn btn-oh-light">Discard</a>
            </div>
            <div></div>
            <div></div>
        </div>
        <div class="settings-shell">
            <aside class="settings-sidebar">
                @foreach (['General Settings', 'Website', 'Leaves', 'Inventory', 'Invoicing', 'Payroll', 'Project', 'Timesheets', 'Events', 'Employees', 'Recruitment', 'Attendances', 'Expenses'] as $item)
                    @if($item === 'General Settings')
                        <a href="{{ route('settings.master.edit') }}" class="settings-link"><i class="bi bi-gear-fill"></i>{{ $item }}</a>
                    @elseif($item === 'Leaves')
                        <a href="{{ route('leaves.settings') }}" class="settings-link active"><i class="bi bi-gear-fill"></i>{{ $item }}</a>
                    @else
                        <div class="settings-link"><i class="bi bi-gear-fill"></i>{{ $item }}</div>
                    @endif
                @endforeach
            </aside>
            <main class="settings-content">
                <div class="settings-band">Leaves</div>
                <div class="row g-5 p-4">
                    <div class="col-lg-5 border-start">
                        <div class="form-check float-end">
                            <input class="form-check-input" type="checkbox" name="leave_reminder_enabled" value="1" @checked($settings->leave_reminder_enabled)>
                        </div>
                        <h2 class="h6 fw-bold">Leave Email Alias</h2>
                        <p class="text-secondary">You can setup a generic email alias to create incoming leave request easily. Write an email with the desired format to create leave request in one click.</p>
                        <label class="form-label fw-bold">Prefix</label>
                        <input name="email_alias_prefix" value="{{ $settings->email_alias_prefix }}" class="form-control mb-3">
                        <label class="form-label fw-bold">Domain</label>
                        <input name="email_alias_domain" value="{{ $settings->email_alias_domain }}" class="form-control">
                        <hr>
                        <h2 class="h6 fw-bold">Flight Ticket</h2>
                        <p class="text-secondary">Choose the expense account to post the flight tickets accounting entries</p>
                        <label class="form-label fw-bold">Expense Account</label>
                        <select class="form-select"><option></option></select>
                    </div>
                    <div class="col-lg-5 border-start">
                        <h2 class="h6 fw-bold">Leaves Reminder</h2>
                        <p class="text-secondary">Send leave remainder emails to holiday managers</p>
                        <label class="form-label fw-bold">Days Before</label>
                        <input name="leave_reminder_days_before" type="number" value="{{ $settings->leave_reminder_days_before }}" class="form-control w-50">
                    </div>
                </div>
                <div class="settings-band">Advanced Features</div>
                <div class="row g-5 p-4">
                    <div class="col-lg-5 border-start">
                        <input type="checkbox" name="employee_shift_enabled" value="1" @checked($settings->employee_shift_enabled)> Employee Shift
                        <div class="fw-bold ms-4">Manage different type of shifts</div>
                    </div>
                    <div class="col-lg-5 border-start">
                        <input type="checkbox" name="vacation_management_enabled" value="1" @checked($settings->vacation_management_enabled)> Vacation Management
                        <div class="fw-bold ms-4">Manage employee vacation</div>
                    </div>
                </div>
            </main>
        </div>
    </form>
@endsection

@push('styles')
    <style>
        .settings-shell {
            display: grid;
            grid-template-columns: 178px 1fr;
            min-height: calc(100vh - 126px);
        }
        .settings-sidebar {
            background: #22282d;
            color: #e5e7eb;
            padding-top: 8px;
        }
        .settings-link {
            align-items: center;
            color: inherit;
            display: flex;
            gap: 10px;
            padding: 9px 16px;
            text-decoration: none;
        }
        .settings-link.active {
            background: #35404a;
            color: #fff;
        }
        .settings-content {
            background: #fff;
        }
        .settings-band {
            background: #e9ecef;
            font-size: 16px;
            font-weight: 700;
            padding: 4px 32px;
        }
        .text-purple {
            color: #6f4da1;
        }
        .content > section { padding-bottom: 0 !important; }
    </style>
@endpush
