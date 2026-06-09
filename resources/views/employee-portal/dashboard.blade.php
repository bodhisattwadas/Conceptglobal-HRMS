@extends('layouts.app', ['heading' => 'Employee Dashboard', 'subheading' => $employee->full_name])

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card metric p-3">
            <div class="text-secondary small">Assigned Projects</div>
            <div class="h3 mb-0">{{ $projects->count() }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card metric p-3">
            <div class="text-secondary small">Recent Timesheets</div>
            <div class="h3 mb-0">{{ $timesheets->count() }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card metric p-3">
            <div class="text-secondary small">Recent Hours</div>
            <div class="h3 mb-0">{{ number_format($totalHours, 2) }}</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card table-card">
            <div class="card-header bg-white fw-semibold">My Projects</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Project</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($projects as $project)
                            <tr><td>{{ $project->name }}</td><td>{{ ucfirst(str_replace('_', ' ', $project->status)) }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="text-secondary">No projects assigned yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card table-card">
            <div class="card-header bg-white fw-semibold">My Recent Timesheets</div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead><tr><th>Date</th><th>Project</th><th class="text-end">Hours</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($timesheets as $row)
                            <tr>
                                <td>{{ $row->date?->format('d/m/Y') }}</td>
                                <td>{{ $row->project?->name }}</td>
                                <td class="text-end">{{ number_format((float) $row->hours_spent, 2) }}</td>
                                <td>{{ ucfirst($row->status) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-secondary">No timesheets synced yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
