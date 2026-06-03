@extends('layouts.app', ['heading' => 'Timesheets', 'subheading' => $timesheet ? 'Edit Timesheet' : 'New Timesheet'])

@section('content')
@include('timesheets._nav')
<div class="timesheet-page">
    <div class="timesheet-title">{{ $timesheet ? 'Timesheets / '.$timesheet->employee?->full_name : 'Timesheets / New' }}</div>
    <form method="post" action="{{ $timesheet ? route('timesheets.update', $timesheet) : route('timesheets.store') }}">
        @csrf
        @if($timesheet) @method('PUT') @endif
        <input type="hidden" name="timer_elapsed_seconds" id="timesheet_timer_elapsed_seconds" value="{{ old('timer_elapsed_seconds', $timesheet?->timer_elapsed_seconds ?? 0) }}">
        <input type="hidden" name="timer_logs" id="timesheet_timer_logs" value="{{ old('timer_logs', json_encode($timesheet?->timer_logs ?? [])) }}">
        <div class="px-3 pb-2 d-flex gap-2">
            <button name="save_action" value="draft" class="btn btn-oh btn-sm">Save as Draft</button>
            <button name="save_action" value="submit" class="btn btn-oh btn-sm">Submit</button>
            <a href="{{ route('timesheets.index') }}" class="btn btn-oh-light btn-sm">Discard</a>
        </div>
        <section class="timesheet-sheet">
            <div class="timesheet-form-grid">
                <div class="timesheet-field">
                    <label>Employee</label>
                    <select name="employee_id" required>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(old('employee_id', $timesheet?->employee_id) == $employee->id)>{{ $employee->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="timesheet-field">
                    <label>Date</label>
                    <input type="date" name="date" value="{{ old('date', $timesheet?->date?->format('Y-m-d') ?? now()->toDateString()) }}" required>
                </div>
                <div class="timesheet-field">
                    <label>Project</label>
                    <select name="project_id" id="timesheet_project_id" required>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" @selected(old('project_id', $timesheet?->project_id ?? request('project_id')) == $project->id)>{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="timesheet-field">
                    <label>Task</label>
                    <select name="project_task_id" id="timesheet_task_id" required>
                        @foreach($tasks as $task)
                            <option value="{{ $task->id }}" data-project-id="{{ $task->project_id }}" @selected(old('project_task_id', $timesheet?->project_task_id ?? request('project_task_id')) == $task->id)>{{ $task->project?->name }} / {{ $task->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="timesheet-field">
                    <label>Start Time</label>
                    <input type="time" name="start_time" value="{{ old('start_time', $timesheet?->start_time) }}">
                </div>
                <div class="timesheet-field">
                    <label>End Time</label>
                    <input type="time" name="end_time" value="{{ old('end_time', $timesheet?->end_time) }}">
                </div>
                <div class="timesheet-field">
                    <label>Hours Spent</label>
                    <input type="number" step="0.25" min="0.25" max="24" name="hours_spent" id="timesheet_hours_spent" value="{{ old('hours_spent', $timesheet?->hours_spent ?? '1.00') }}" required>
                </div>
                <div class="timesheet-field">
                    <label>Billable</label>
                    <select name="is_billable">
                        <option value="0" @selected(!old('is_billable', $timesheet?->is_billable))>No</option>
                        <option value="1" @selected(old('is_billable', $timesheet?->is_billable))>Yes</option>
                    </select>
                </div>
                <div class="timesheet-field" style="grid-column: 1 / -1;">
                    <label>Description</label>
                    <textarea name="description" rows="4">{{ old('description', $timesheet?->description) }}</textarea>
                </div>
                <div class="timesheet-field timesheet-timer" style="grid-column: 1 / -1;">
                    <label>Work Timer</label>
                    <div class="timer-panel">
                        <div>
                            <div class="timer-display" id="timesheet_timer_display">00:00:00</div>
                            <div class="timer-state" id="timesheet_timer_state">Stopped</div>
                            <div class="timer-total" id="timesheet_timer_total">Total: 0 hr 0 min 0 sec</div>
                        </div>
                        <div class="timer-note">
                            <label for="timesheet_timer_note">Notes</label>
                            <input type="text" id="timesheet_timer_note" placeholder="What are you working on?">
                        </div>
                        <div class="timer-actions">
                            <button type="button" class="btn btn-oh btn-sm" id="timesheet_timer_start">Start</button>
                            <button type="button" class="btn btn-oh-light btn-sm" id="timesheet_timer_stop">Stop</button>
                        </div>
                    </div>
                    <table class="timer-log-table">
                        <thead><tr><th>Action</th><th>Time</th><th>Elapsed</th><th>Note</th></tr></thead>
                        <tbody id="timesheet_timer_log_rows"></tbody>
                    </table>
                </div>
            </div>
        </section>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const projectSelect = document.getElementById('timesheet_project_id');
    const taskSelect = document.getElementById('timesheet_task_id');
    if (!projectSelect || !taskSelect) return;

    const syncTasks = () => {
        const projectId = projectSelect.value;
        let firstVisibleValue = null;
        let selectedStillVisible = false;

        Array.from(taskSelect.options).forEach((option) => {
            const visible = option.dataset.projectId === projectId;
            option.hidden = !visible;
            option.disabled = !visible;
            if (visible && firstVisibleValue === null) firstVisibleValue = option.value;
            if (visible && option.selected) selectedStillVisible = true;
        });

        if (!selectedStillVisible && firstVisibleValue !== null) {
            taskSelect.value = firstVisibleValue;
        }
    };

    projectSelect.addEventListener('change', syncTasks);
    syncTasks();

    const display = document.getElementById('timesheet_timer_display');
    const startBtn = document.getElementById('timesheet_timer_start');
    const stopBtn = document.getElementById('timesheet_timer_stop');
    const hoursInput = document.getElementById('timesheet_hours_spent');
    const elapsedInput = document.getElementById('timesheet_timer_elapsed_seconds');
    const logsInput = document.getElementById('timesheet_timer_logs');
    const stateText = document.getElementById('timesheet_timer_state');
    const totalText = document.getElementById('timesheet_timer_total');
    const noteInput = document.getElementById('timesheet_timer_note');
    const logRows = document.getElementById('timesheet_timer_log_rows');
    const startTimeInput = document.querySelector('input[name="start_time"]');
    const endTimeInput = document.querySelector('input[name="end_time"]');

    if (!display || !startBtn || !stopBtn || !hoursInput || !elapsedInput || !logsInput || !stateText || !totalText || !logRows) return;

    const inactivityLimitSeconds = 30;
    const storageKey = `timesheetTimer:${window.location.pathname}`;
    let elapsedSeconds = Number(elapsedInput.value || sessionStorage.getItem(`${storageKey}:seconds`) || 0);
    let runSeconds = 0;
    let timerLogs = [];
    let timerId = null;
    let idleSeconds = 0;

    try {
        timerLogs = JSON.parse(logsInput.value || '[]');
        if (!Array.isArray(timerLogs)) timerLogs = [];
    } catch {
        timerLogs = [];
    }

    const pad = (value) => String(value).padStart(2, '0');
    const formatElapsed = (seconds) => {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const rest = seconds % 60;
        return `${pad(hours)}:${pad(minutes)}:${pad(rest)}`;
    };
    const formatTotal = (seconds) => {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const rest = seconds % 60;
        return `Total: ${hours} hr ${minutes} min ${rest} sec`;
    };
    const nowLabel = () => new Date().toLocaleString([], {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
    const render = () => {
        display.textContent = formatElapsed(timerId ? runSeconds : 0);
        totalText.textContent = formatTotal(elapsedSeconds);
        elapsedInput.value = String(elapsedSeconds);
        logsInput.value = JSON.stringify(timerLogs);
        logRows.innerHTML = timerLogs.map((log) => `
            <tr>
                <td>${log.action}</td>
                <td>${log.time}</td>
                <td>${log.elapsed}</td>
                <td>${log.note || ''}</td>
            </tr>
        `).join('');
    };
    const currentTime = () => {
        const now = new Date();
        return `${pad(now.getHours())}:${pad(now.getMinutes())}`;
    };
    const updateHoursSpent = () => {
        const hours = elapsedSeconds > 0 ? elapsedSeconds / 3600 : Number(hoursInput.value || 0);
        if (hours <= 0) return;
        hoursInput.value = Math.min(hours, 24).toFixed(2);
    };
    const addLog = (action, note = '') => {
        timerLogs.unshift({
            action,
            time: nowLabel(),
            elapsed: formatElapsed(elapsedSeconds),
            note,
        });
        timerLogs = timerLogs.slice(0, 100);
        render();
    };
    const stopTimer = (note = 'Stopped manually') => {
        if (!timerId) return;
        window.clearInterval(timerId);
        timerId = null;
        idleSeconds = 0;
        runSeconds = 0;
        stateText.textContent = 'Stopped';
        startBtn.textContent = 'Start';
        if (endTimeInput) endTimeInput.value = currentTime();
        updateHoursSpent();
        addLog('Stop', note);
        sessionStorage.setItem(`${storageKey}:seconds`, String(elapsedSeconds));
    };

    startBtn.addEventListener('click', () => {
        if (timerId) return;
        if (startTimeInput && !startTimeInput.value) startTimeInput.value = currentTime();
        idleSeconds = 0;
        runSeconds = 0;
        stateText.textContent = `Running - auto stops after ${inactivityLimitSeconds}s idle`;
        addLog('Start', noteInput?.value || '');
        timerId = window.setInterval(() => {
            elapsedSeconds += 1;
            runSeconds += 1;
            idleSeconds += 1;
            sessionStorage.setItem(`${storageKey}:seconds`, String(elapsedSeconds));
            updateHoursSpent();
            render();
            if (idleSeconds >= inactivityLimitSeconds) {
                stopTimer(`Auto-stopped after ${inactivityLimitSeconds} seconds of no activity`);
            }
        }, 1000);
        startBtn.textContent = 'Running';
    });

    stopBtn.addEventListener('click', () => stopTimer());

    ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart'].forEach((eventName) => {
        window.addEventListener(eventName, () => {
            if (timerId) idleSeconds = 0;
        }, { passive: true });
    });

    updateHoursSpent();
    render();
});
</script>
@endpush

@push('styles')
<style>
    .timer-panel {
        align-items: center;
        background: #f8fafc;
        border: 1px solid #dfe3e8;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        justify-content: space-between;
        padding: 10px 12px;
    }
    .timer-display {
        color: #2f3a56;
        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        font-size: 24px;
        font-weight: 700;
        min-width: 130px;
    }
    .timer-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .timer-state {
        color: #64748b;
        font-size: 12px;
        margin-top: 2px;
    }
    .timer-note {
        align-items: center;
        display: inline-flex;
        gap: 8px;
    }
    .timer-note label {
        color: #475569;
        font-size: 12px;
        font-weight: 700;
        margin: 0;
    }
    .timer-note input {
        border: 1px solid #cfd6df;
        height: 30px;
        padding: 4px 8px;
        width: 260px;
    }
    .timer-log-table {
        border-collapse: collapse;
        margin-top: 8px;
        width: 100%;
    }
    .timer-log-table th,
    .timer-log-table td {
        border: 1px solid #dfe3e8;
        font-size: 12px;
        padding: 5px 7px;
    }
    .timer-log-table th {
        background: #eef1f5;
    }
</style>
@endpush
