@extends('layouts.app', ['heading' => 'Master Settings', 'subheading' => 'General Settings'])

@section('content')
    @include('leaves._nav', ['appTitle' => 'Leaves'])
    <form method="post" action="{{ route('settings.master.update') }}">
        @csrf
        <div class="oh-page-title"><h1>Settings</h1></div>
        <div class="oh-actions">
            <div class="d-flex gap-2">
                <button class="btn btn-oh">Save</button>
                <a href="{{ route('settings.master.edit') }}" class="btn btn-oh-light">Discard</a>
            </div>
            <div></div><div></div>
        </div>
        <div class="settings-shell">
            <aside class="settings-sidebar">
                @foreach (['General Settings', 'Website', 'Leaves', 'Inventory', 'Invoicing', 'Payroll', 'Project', 'Timesheets', 'Events', 'Employees', 'Recruitment', 'Attendances', 'Expenses'] as $item)
                    @if($item === 'General Settings')
                        <a href="{{ route('settings.master.edit') }}" class="settings-link active"><i class="bi bi-gear-fill"></i>{{ $item }}</a>
                    @elseif($item === 'Leaves')
                        <a href="{{ route('leaves.settings') }}" class="settings-link"><i class="bi bi-gear-fill"></i>{{ $item }}</a>
                    @else
                        <div class="settings-link"><i class="bi bi-gear-fill"></i>{{ $item }}</div>
                    @endif
                @endforeach
            </aside>
            <main class="settings-content">
                <div class="settings-band">General Settings</div>
                <div class="p-4" style="max-width: 500px;">
                    <label class="form-label fw-bold">Default Currency</label>
                    <select name="default_currency_code" class="form-select">
                        @foreach($currencies as $currency)
                            <option value="{{ $currency }}" @selected($settings->default_currency_code === $currency)>{{ $currency }}</option>
                        @endforeach
                    </select>
                    <div class="text-secondary mt-2">Used as default currency in modules like Loan Management.</div>
                </div>
            </main>
        </div>
    </form>
@endsection

@push('styles')
    <style>
        .settings-shell { display: grid; grid-template-columns: 178px 1fr; min-height: calc(100vh - 126px); }
        .settings-sidebar { background: #22282d; color: #e5e7eb; padding-top: 8px; }
        .settings-link { align-items: center; color: inherit; display: flex; gap: 10px; padding: 9px 16px; text-decoration: none; }
        .settings-link.active { background: #35404a; color: #fff; }
        .settings-content { background: #fff; }
        .settings-band { background: #e9ecef; font-size: 16px; font-weight: 700; padding: 4px 32px; }
        .content > section { padding-bottom: 0 !important; }
    </style>
@endpush
