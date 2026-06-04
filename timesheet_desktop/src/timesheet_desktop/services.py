from __future__ import annotations

from dataclasses import dataclass
from datetime import date
from decimal import Decimal
import sqlite3

from .database import recalculate_task_progress, utc_now, verify_password


@dataclass(frozen=True)
class CurrentUser:
    id: int
    name: str
    email: str
    role: str


class AuthService:
    def __init__(self, conn: sqlite3.Connection):
        self.conn = conn

    def authenticate(self, email: str, password: str) -> CurrentUser | None:
        row = self.conn.execute(
            """
            SELECT users.id, users.name, users.email, users.password_hash, roles.name AS role
            FROM users
            JOIN roles ON roles.id = users.role_id
            WHERE users.email = ? AND users.is_active = 1
            """,
            (email.strip().lower(),),
        ).fetchone()
        if not row or not verify_password(password, row["password_hash"]):
            return None
        self.conn.execute(
            "UPDATE users SET last_login_at = ?, updated_at = ? WHERE id = ?",
            (utc_now(), utc_now(), row["id"]),
        )
        self.conn.commit()
        return CurrentUser(row["id"], row["name"], row["email"], row["role"])


class TimesheetService:
    def __init__(self, conn: sqlite3.Connection):
        self.conn = conn

    def create_timesheet(
        self,
        *,
        employee_id: int,
        project_id: int,
        project_task_id: int,
        entry_date: date,
        hours_spent: Decimal,
        description: str,
        current_user: CurrentUser,
        status: str = "saved",
        is_billable: bool = False,
        start_time: str | None = None,
        end_time: str | None = None,
    ) -> int:
        if hours_spent <= 0:
            raise ValueError("Hours spent must be greater than zero.")

        settings = self.conn.execute("SELECT * FROM timesheet_settings WHERE id = 1").fetchone()
        maximum = Decimal(str(settings["maximum_hours_per_day"]))

        existing = self.conn.execute(
            """
            SELECT COALESCE(SUM(hours_spent), 0)
            FROM timesheets
            WHERE employee_id = ? AND date = ? AND deleted_at IS NULL
            """,
            (employee_id, entry_date.isoformat()),
        ).fetchone()[0]
        if Decimal(str(existing or 0)) + hours_spent > maximum:
            raise ValueError(f"Daily hours cannot exceed {maximum}.")

        task = self.conn.execute(
            "SELECT project_id, status FROM project_tasks WHERE id = ? AND deleted_at IS NULL",
            (project_task_id,),
        ).fetchone()
        if not task:
            raise ValueError("Task does not exist.")
        if task["project_id"] != project_id:
            raise ValueError("Task must belong to the selected project.")
        if task["status"] in {"done", "cancelled"}:
            raise ValueError("Cannot log time on completed or cancelled tasks.")

        employee = self.conn.execute(
            "SELECT department_id FROM employees WHERE id = ? AND is_active = 1",
            (employee_id,),
        ).fetchone()
        if not employee:
            raise ValueError("Employee does not exist or is inactive.")

        now = utc_now()
        cursor = self.conn.execute(
            """
            INSERT INTO timesheets(
                employee_id, department_id, project_id, project_task_id, date, start_time, end_time, hours_spent,
                description, is_billable, status, created_by, updated_by, created_at, updated_at
            )
            VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            """,
            (
                employee_id,
                employee["department_id"],
                project_id,
                project_task_id,
                entry_date.isoformat(),
                start_time,
                end_time,
                float(hours_spent),
                description.strip(),
                1 if is_billable else 0,
                status,
                current_user.id,
                current_user.id,
                now,
                now,
            ),
        )
        timesheet_id = cursor.lastrowid
        self.conn.execute(
            """
            INSERT INTO audit_logs(user_id, entity_type, entity_id, action, new_values_json, created_at)
            VALUES(?, 'timesheet', ?, 'created', ?, ?)
            """,
            (current_user.id, timesheet_id, f'{{"hours_spent": {float(hours_spent)}}}', now),
        )
        recalculate_task_progress(self.conn, project_task_id)
        self.conn.commit()
        return int(timesheet_id)


class LookupService:
    def __init__(self, conn: sqlite3.Connection):
        self.conn = conn

    def employees(self) -> list[sqlite3.Row]:
        return self.conn.execute("SELECT id, name FROM employees WHERE is_active = 1 ORDER BY name").fetchall()

    def projects(self) -> list[sqlite3.Row]:
        return self.conn.execute("SELECT id, name FROM projects WHERE deleted_at IS NULL ORDER BY name").fetchall()

    def tasks_for_project(self, project_id: int) -> list[sqlite3.Row]:
        return self.conn.execute(
            "SELECT id, title FROM project_tasks WHERE project_id = ? AND deleted_at IS NULL ORDER BY title",
            (project_id,),
        ).fetchall()

    def timesheets(self) -> list[sqlite3.Row]:
        return self.conn.execute(
            """
            SELECT
                timesheets.id,
                timesheets.date,
                employees.name AS employee,
                projects.name AS project,
                project_tasks.title AS task,
                timesheets.description,
                timesheets.start_time,
                timesheets.end_time,
                timesheets.hours_spent,
                timesheets.is_billable,
                timesheets.status
            FROM timesheets
            JOIN employees ON employees.id = timesheets.employee_id
            JOIN projects ON projects.id = timesheets.project_id
            JOIN project_tasks ON project_tasks.id = timesheets.project_task_id
            WHERE timesheets.deleted_at IS NULL
            ORDER BY timesheets.date DESC, timesheets.id DESC
            """
        ).fetchall()

    def dashboard_metrics(self) -> dict[str, float]:
        today = date.today().isoformat()
        row = self.conn.execute(
            """
            SELECT
                COALESCE(SUM(CASE WHEN date = ? THEN hours_spent ELSE 0 END), 0) AS today_hours,
                COALESCE(SUM(CASE WHEN status = 'submitted' THEN hours_spent ELSE 0 END), 0) AS submitted_hours,
                COALESCE(SUM(CASE WHEN status = 'approved' THEN hours_spent ELSE 0 END), 0) AS approved_hours
            FROM timesheets
            WHERE deleted_at IS NULL
            """,
            (today,),
        ).fetchone()
        over_planned = self.conn.execute(
            "SELECT COUNT(*) FROM project_tasks WHERE extra_hours > 0 AND deleted_at IS NULL"
        ).fetchone()[0]
        return {
            "today_hours": float(row["today_hours"]),
            "submitted_hours": float(row["submitted_hours"]),
            "approved_hours": float(row["approved_hours"]),
            "over_planned": float(over_planned),
        }

    def task_progress(self) -> list[sqlite3.Row]:
        return self.conn.execute(
            """
            SELECT projects.name AS project, project_tasks.title, planned_hours, spent_hours,
                   remaining_hours, extra_hours, progress_percent, status
            FROM project_tasks
            JOIN projects ON projects.id = project_tasks.project_id
            WHERE project_tasks.deleted_at IS NULL
            ORDER BY projects.name, project_tasks.title
            """
        ).fetchall()
