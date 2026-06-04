<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Timesheet;
use App\Models\User;
use App\Services\TimesheetProgressService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DesktopTimesheetController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $this->ensureDesktopSchema();

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Invalid email or password.',
            ]);
        }

        $employee = $user->employee()->with('workInformation.department')->first();
        if (! $employee || ! $employee->is_active) {
            throw ValidationException::withMessages([
                'email' => 'No active employee profile is linked to this user.',
            ]);
        }

        $token = Str::random(80);
        $user->forceFill([
            'desktop_api_token_hash' => hash('sha256', $token),
            'desktop_api_token_last_used_at' => now(),
        ])->save();

        return response()->json([
            'token_type' => 'Bearer',
            'token' => $token,
            'user' => $this->userPayload($user, $employee),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        [$user, $employee] = $this->authenticatedEmployee($request);

        return response()->json([
            'user' => $this->userPayload($user, $employee),
        ]);
    }

    public function bootstrap(Request $request): JsonResponse
    {
        [$user, $employee] = $this->authenticatedEmployee($request);

        return response()->json([
            'user' => $this->userPayload($user, $employee),
            'projects' => Project::query()
                ->with(['tasks' => fn ($query) => $query->orderBy('title')])
                ->where('status', '!=', 'cancelled')
                ->orderBy('name')
                ->get()
                ->map(fn (Project $project): array => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'code' => $project->code,
                    'tasks' => $project->tasks->map(fn (ProjectTask $task): array => [
                        'id' => $task->id,
                        'title' => $task->title,
                        'planned_hours' => (float) $task->planned_hours,
                        'status' => $task->status,
                    ])->values(),
                ])->values(),
        ]);
    }

    public function timesheets(Request $request): JsonResponse
    {
        [, $employee] = $this->authenticatedEmployee($request);

        $rows = Timesheet::query()
            ->with('project', 'task')
            ->where('employee_id', $employee->id)
            ->latest('date')
            ->latest('id')
            ->get()
            ->map(fn (Timesheet $timesheet): array => $this->timesheetPayload($timesheet));

        return response()->json([
            'timesheets' => $rows,
        ]);
    }

    public function storeTimesheet(Request $request, TimesheetProgressService $progress): JsonResponse
    {
        [, $employee] = $this->authenticatedEmployee($request);

        $data = $request->validate([
            'desktop_uuid' => ['nullable', 'uuid'],
            'project_id' => ['required', 'exists:projects,id'],
            'project_task_id' => ['required', 'exists:project_tasks,id'],
            'date' => ['required', 'date'],
            'start_time' => ['nullable', 'string'],
            'end_time' => ['nullable', 'string'],
            'hours_spent' => ['required', 'numeric', 'gt:0', 'max:24'],
            'timer_elapsed_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'timer_logs' => ['nullable', 'array'],
            'timer_logs.*.action' => ['nullable', 'string', 'max:80'],
            'timer_logs.*.time' => ['nullable', 'string', 'max:80'],
            'timer_logs.*.elapsed' => ['nullable', 'string', 'max:40'],
            'timer_logs.*.total' => ['nullable', 'string', 'max:40'],
            'timer_logs.*.note' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_billable' => ['nullable', 'boolean'],
            'submit_final' => ['nullable', 'boolean'],
        ]);

        $task = ProjectTask::query()
            ->whereKey($data['project_task_id'])
            ->where('project_id', $data['project_id'])
            ->first();

        if (! $task) {
            throw ValidationException::withMessages([
                'project_task_id' => 'Selected task does not belong to selected project.',
            ]);
        }

        $payload = [
            'company_id' => $task->company_id,
            'employee_id' => $employee->id,
            'department_id' => $employee->workInformation?->department_id,
            'project_id' => $data['project_id'],
            'project_task_id' => $data['project_task_id'],
            'date' => $data['date'],
            'start_time' => $this->normalizeTime($data['start_time'] ?? null),
            'end_time' => $this->normalizeTime($data['end_time'] ?? null),
            'hours_spent' => $data['hours_spent'],
            'timer_elapsed_seconds' => $data['timer_elapsed_seconds'] ?? 0,
            'timer_logs' => $data['timer_logs'] ?? [],
            'description' => $data['description'] ?? null,
            'is_billable' => $data['is_billable'] ?? true,
            'status' => ($data['submit_final'] ?? false) ? 'submitted' : 'draft',
            'submitted_at' => ($data['submit_final'] ?? false) ? now() : null,
            'submitted_by' => ($data['submit_final'] ?? false) ? $employee->user_id : null,
            'source' => 'desktop',
        ];

        $timesheet = Timesheet::query()->updateOrCreate(
            ['desktop_uuid' => $data['desktop_uuid'] ?? (string) Str::uuid()],
            $payload,
        );

        $progress->recalculateTask($task);

        return response()->json([
            'timesheet' => $this->timesheetPayload($timesheet->load('project', 'task')),
        ], $timesheet->wasRecentlyCreated ? 201 : 200);
    }

    private function authenticatedEmployee(Request $request): array
    {
        $this->ensureDesktopSchema();

        $token = Str::after($request->header('Authorization', ''), 'Bearer ');
        abort_if($token === '', 401, 'Missing bearer token.');

        $user = User::query()
            ->where('desktop_api_token_hash', hash('sha256', $token))
            ->first();

        abort_if(! $user, 401, 'Invalid bearer token.');

        $user->forceFill(['desktop_api_token_last_used_at' => now()])->save();

        $employee = $user->employee()->with('workInformation.department')->first();
        abort_if(! $employee || ! $employee->is_active, 403, 'No active employee profile is linked to this user.');

        return [$user, $employee];
    }

    private function userPayload(User $user, Employee $employee): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->full_name,
                'email' => $employee->email,
                'department' => $employee->workInformation?->department?->name,
            ],
        ];
    }

    private function timesheetPayload(Timesheet $timesheet): array
    {
        return [
            'id' => $timesheet->id,
            'desktop_uuid' => $timesheet->desktop_uuid,
            'date' => $timesheet->date?->toDateString(),
            'project' => [
                'id' => $timesheet->project_id,
                'name' => $timesheet->project?->name,
            ],
            'task' => [
                'id' => $timesheet->project_task_id,
                'title' => $timesheet->task?->title,
            ],
            'start_time' => $timesheet->start_time,
            'end_time' => $timesheet->end_time,
            'hours_spent' => (float) $timesheet->hours_spent,
            'timer_elapsed_seconds' => (int) $timesheet->timer_elapsed_seconds,
            'timer_logs' => $timesheet->timer_logs ?? [],
            'description' => $timesheet->description,
            'is_billable' => (bool) $timesheet->is_billable,
            'status' => $timesheet->status,
            'source' => $timesheet->source,
            'web_url' => route('timesheets.show', $timesheet),
        ];
    }

    private function normalizeTime(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        foreach (['H:i', 'H:i:s', 'h:i A', 'h:i:s A'] as $format) {
            try {
                return Carbon::createFromFormat($format, trim($value))->format('H:i:s');
            } catch (\Throwable) {
                //
            }
        }

        return Carbon::parse($value)->format('H:i:s');
    }

    private function ensureDesktopSchema(): void
    {
        abort_unless(
            Schema::hasColumn('users', 'desktop_api_token_hash')
                && Schema::hasColumn('users', 'desktop_api_token_last_used_at')
                && Schema::hasColumn('timesheets', 'desktop_uuid'),
            503,
            'Desktop API database migrations are not applied. Run php artisan migrate.'
        );
    }
}
