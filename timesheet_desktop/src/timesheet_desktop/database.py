from __future__ import annotations

from contextlib import contextmanager
from datetime import UTC, date, datetime, timedelta
from decimal import Decimal
from pathlib import Path
import hashlib
import os
import sqlite3

from .config import database_path


SCHEMA_VERSION = 1


def utc_now() -> str:
    return datetime.now(UTC).isoformat(timespec="seconds")


def connect(path: Path | None = None) -> sqlite3.Connection:
    db_path = path or database_path()
    db_path.parent.mkdir(parents=True, exist_ok=True)
    conn = sqlite3.connect(db_path)
    conn.row_factory = sqlite3.Row
    conn.execute("PRAGMA foreign_keys = ON")
    return conn


@contextmanager
def transaction(path: Path | None = None):
    conn = connect(path)
    try:
        yield conn
        conn.commit()
    except Exception:
        conn.rollback()
        raise
    finally:
        conn.close()


def hash_password(password: str, salt: bytes | None = None) -> str:
    salt = salt or os.urandom(16)
    digest = hashlib.pbkdf2_hmac("sha256", password.encode("utf-8"), salt, 120_000)
    return f"pbkdf2_sha256${salt.hex()}${digest.hex()}"


def verify_password(password: str, stored_hash: str) -> bool:
    try:
        algorithm, salt_hex, digest_hex = stored_hash.split("$", 2)
    except ValueError:
        return False
    if algorithm != "pbkdf2_sha256":
        return False
    expected = hash_password(password, bytes.fromhex(salt_hex))
    return expected == stored_hash


def initialize_database(path: Path | None = None) -> None:
    with transaction(path) as conn:
        apply_schema(conn)
        seed_database(conn)


def apply_schema(conn: sqlite3.Connection) -> None:
    conn.executescript(
        """
        CREATE TABLE IF NOT EXISTS schema_meta (
            key TEXT PRIMARY KEY,
            value TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS roles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            description TEXT,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            employee_id INTEGER,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            role_id INTEGER NOT NULL,
            is_active INTEGER NOT NULL DEFAULT 1,
            last_login_at TEXT,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL,
            FOREIGN KEY (role_id) REFERENCES roles(id)
        );

        CREATE TABLE IF NOT EXISTS departments (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            manager_employee_id INTEGER,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS employees (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            department_id INTEGER,
            employee_code TEXT NOT NULL UNIQUE,
            name TEXT NOT NULL,
            email TEXT,
            phone TEXT,
            job_title TEXT,
            manager_employee_id INTEGER,
            is_active INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL,
            FOREIGN KEY (department_id) REFERENCES departments(id)
        );

        CREATE TABLE IF NOT EXISTS projects (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            code TEXT NOT NULL UNIQUE,
            customer_name TEXT,
            manager_employee_id INTEGER,
            description TEXT,
            status TEXT NOT NULL DEFAULT 'active',
            start_date TEXT,
            end_date TEXT,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL,
            deleted_at TEXT
        );

        CREATE TABLE IF NOT EXISTS project_tasks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            project_id INTEGER NOT NULL,
            parent_task_id INTEGER,
            title TEXT NOT NULL,
            description TEXT,
            customer_name TEXT,
            deadline TEXT,
            planned_hours REAL NOT NULL DEFAULT 0,
            spent_hours REAL NOT NULL DEFAULT 0,
            remaining_hours REAL NOT NULL DEFAULT 0,
            extra_hours REAL NOT NULL DEFAULT 0,
            progress_percent REAL NOT NULL DEFAULT 0,
            status TEXT NOT NULL DEFAULT 'new',
            is_recurrent INTEGER NOT NULL DEFAULT 0,
            priority TEXT NOT NULL DEFAULT 'normal',
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL,
            deleted_at TEXT,
            FOREIGN KEY (project_id) REFERENCES projects(id),
            FOREIGN KEY (parent_task_id) REFERENCES project_tasks(id)
        );

        CREATE TABLE IF NOT EXISTS project_task_assignees (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            project_task_id INTEGER NOT NULL,
            employee_id INTEGER NOT NULL,
            assigned_at TEXT NOT NULL,
            UNIQUE(project_task_id, employee_id),
            FOREIGN KEY (project_task_id) REFERENCES project_tasks(id),
            FOREIGN KEY (employee_id) REFERENCES employees(id)
        );

        CREATE TABLE IF NOT EXISTS timesheets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            employee_id INTEGER NOT NULL,
            department_id INTEGER,
            project_id INTEGER NOT NULL,
            project_task_id INTEGER NOT NULL,
            project_sub_task_id INTEGER,
            date TEXT NOT NULL,
            start_time TEXT,
            end_time TEXT,
            hours_spent REAL NOT NULL,
            description TEXT,
            is_billable INTEGER NOT NULL DEFAULT 0,
            status TEXT NOT NULL DEFAULT 'draft',
            submitted_at TEXT,
            submitted_by INTEGER,
            approved_at TEXT,
            approved_by INTEGER,
            rejected_at TEXT,
            rejected_by INTEGER,
            rejection_reason TEXT,
            source TEXT NOT NULL DEFAULT 'manual',
            created_by INTEGER NOT NULL,
            updated_by INTEGER NOT NULL,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL,
            deleted_at TEXT,
            FOREIGN KEY (employee_id) REFERENCES employees(id),
            FOREIGN KEY (department_id) REFERENCES departments(id),
            FOREIGN KEY (project_id) REFERENCES projects(id),
            FOREIGN KEY (project_task_id) REFERENCES project_tasks(id)
        );

        CREATE INDEX IF NOT EXISTS idx_timesheets_employee_date ON timesheets(employee_id, date);
        CREATE INDEX IF NOT EXISTS idx_timesheets_project_date ON timesheets(project_id, date);
        CREATE INDEX IF NOT EXISTS idx_timesheets_task_date ON timesheets(project_task_id, date);
        CREATE INDEX IF NOT EXISTS idx_timesheets_status ON timesheets(status);

        CREATE TABLE IF NOT EXISTS timesheet_status_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            timesheet_id INTEGER NOT NULL,
            old_status TEXT,
            new_status TEXT NOT NULL,
            changed_by INTEGER NOT NULL,
            reason TEXT,
            changed_at TEXT NOT NULL,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL,
            FOREIGN KEY (timesheet_id) REFERENCES timesheets(id)
        );

        CREATE TABLE IF NOT EXISTS timesheet_settings (
            id INTEGER PRIMARY KEY CHECK (id = 1),
            allow_future_entries INTEGER NOT NULL DEFAULT 0,
            future_entry_limit_days INTEGER NOT NULL DEFAULT 0,
            allow_employee_edit_after_submit INTEGER NOT NULL DEFAULT 0,
            allow_employee_delete_after_submit INTEGER NOT NULL DEFAULT 0,
            require_approval INTEGER NOT NULL DEFAULT 0,
            minimum_hours_per_entry REAL NOT NULL DEFAULT 0.25,
            maximum_hours_per_day REAL NOT NULL DEFAULT 24,
            restrict_to_assigned_tasks INTEGER NOT NULL DEFAULT 0,
            lock_after_payroll INTEGER NOT NULL DEFAULT 1,
            direct_approve_entries INTEGER NOT NULL DEFAULT 1,
            created_at TEXT NOT NULL,
            updated_at TEXT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS audit_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            entity_type TEXT NOT NULL,
            entity_id INTEGER NOT NULL,
            action TEXT NOT NULL,
            old_values_json TEXT,
            new_values_json TEXT,
            created_at TEXT NOT NULL
        );
        """
    )
    conn.execute(
        "INSERT OR REPLACE INTO schema_meta(key, value) VALUES('schema_version', ?)",
        (str(SCHEMA_VERSION),),
    )


def seed_database(conn: sqlite3.Connection) -> None:
    now = utc_now()
    role_count = conn.execute("SELECT COUNT(*) FROM roles").fetchone()[0]
    if role_count:
        return

    conn.executemany(
        "INSERT INTO roles(name, description, created_at, updated_at) VALUES(?, ?, ?, ?)",
        [
            ("Super Admin", "Full local application access", now, now),
            ("HR Admin", "Company-wide timesheet access", now, now),
            ("Project Manager", "Project and team timesheet access", now, now),
            ("Employee", "Own timesheet access", now, now),
        ],
    )

    conn.executemany(
        "INSERT INTO departments(name, created_at, updated_at) VALUES(?, ?, ?)",
        [("Design", now, now), ("Engineering", now, now), ("HR", now, now)],
    )

    employees = [
        ("E001", "Abigail Peterson", "Engineer"),
        ("E002", "Anita Oliver", "Engineer"),
        ("E003", "Audrey Peterson", "Designer"),
        ("E004", "Beth Evans", "HR Executive"),
        ("E005", "Jeffrey Kelly", "Project Manager"),
        ("E006", "Marc Demo", "Consultant"),
        ("E007", "Walter Horton", "Consultant"),
        ("E008", "Keith Byrd", "Consultant"),
    ]
    for index, (code, name, title) in enumerate(employees, start=1):
        department_id = 2 if "Engineer" in title or "Manager" in title else 1
        conn.execute(
            """
            INSERT INTO employees(department_id, employee_code, name, email, job_title, created_at, updated_at)
            VALUES(?, ?, ?, ?, ?, ?, ?)
            """,
            (department_id, code, name, f"{code.lower()}@example.com", title, now, now),
        )

    super_admin_role = conn.execute("SELECT id FROM roles WHERE name = 'Super Admin'").fetchone()[0]
    conn.execute(
        """
        INSERT INTO users(name, email, password_hash, role_id, created_at, updated_at)
        VALUES(?, ?, ?, ?, ?, ?)
        """,
        ("Administrator", "admin@example.com", hash_password("admin123"), super_admin_role, now, now),
    )

    projects = [
        ("Office Design", "OFFICE", "active"),
        ("Research & Development", "RND", "active"),
    ]
    conn.executemany(
        "INSERT INTO projects(name, code, status, created_at, updated_at) VALUES(?, ?, ?, ?, ?)",
        [(name, code, status, now, now) for name, code, status in projects],
    )

    office_id = conn.execute("SELECT id FROM projects WHERE code = 'OFFICE'").fetchone()[0]
    rnd_id = conn.execute("SELECT id FROM projects WHERE code = 'RND'").fetchone()[0]
    tasks = [
        (office_id, "Meeting Room Furnitures", 40, "in_progress"),
        (office_id, "Room 2: Decoration", 24, "new"),
        (office_id, "Office planning", 16, "new"),
        (rnd_id, "Unit Testing", 32, "in_progress"),
        (rnd_id, "User interface improvements", 30, "new"),
        (rnd_id, "Document management", 20, "new"),
    ]
    conn.executemany(
        """
        INSERT INTO project_tasks(project_id, title, planned_hours, status, created_at, updated_at)
        VALUES(?, ?, ?, ?, ?, ?)
        """,
        [(project_id, title, planned, status, now, now) for project_id, title, planned, status in tasks],
    )

    conn.execute(
        """
        INSERT INTO timesheet_settings(id, created_at, updated_at)
        VALUES(1, ?, ?)
        """,
        (now, now),
    )

    admin_id = conn.execute("SELECT id FROM users WHERE email = 'admin@example.com'").fetchone()[0]
    seed_timesheets(conn, admin_id)


def seed_timesheets(conn: sqlite3.Connection, admin_id: int) -> None:
    today = date.today()
    rows = [
        ("Abigail Peterson", "Research & Development", "Unit Testing", "Requirements analysis", Decimal("3"), today - timedelta(days=2)),
        ("Abigail Peterson", "Office Design", "Room 2: Decoration", "Requirements analysis", Decimal("2"), today - timedelta(days=1)),
        ("Marc Demo", "Office Design", "Meeting Room Furnitures", "Requirements analysis", Decimal("1"), today),
        ("Walter Horton", "Office Design", "Meeting Room Furnitures", "Requirements analysis", Decimal("1"), today),
        ("Keith Byrd", "Office Design", "Meeting Room Furnitures", "On Site Visit", Decimal("2"), today),
    ]
    now = utc_now()
    for employee_name, project_name, task_title, description, hours, entry_date in rows:
        employee = conn.execute("SELECT id, department_id FROM employees WHERE name = ?", (employee_name,)).fetchone()
        project_id = conn.execute("SELECT id FROM projects WHERE name = ?", (project_name,)).fetchone()[0]
        task_id = conn.execute(
            "SELECT id FROM project_tasks WHERE title = ? AND project_id = ?",
            (task_title, project_id),
        ).fetchone()[0]
        conn.execute(
            """
            INSERT INTO timesheets(
                employee_id, department_id, project_id, project_task_id, date, hours_spent,
                description, status, created_by, updated_by, created_at, updated_at
            )
            VALUES(?, ?, ?, ?, ?, ?, ?, 'saved', ?, ?, ?, ?)
            """,
            (
                employee["id"],
                employee["department_id"],
                project_id,
                task_id,
                entry_date.isoformat(),
                float(hours),
                description,
                admin_id,
                admin_id,
                now,
                now,
            ),
        )

    task_ids = [row["id"] for row in conn.execute("SELECT id FROM project_tasks").fetchall()]
    for task_id in task_ids:
        recalculate_task_progress(conn, task_id)


def recalculate_task_progress(conn: sqlite3.Connection, task_id: int) -> None:
    task = conn.execute("SELECT planned_hours FROM project_tasks WHERE id = ?", (task_id,)).fetchone()
    if not task:
        return
    spent = conn.execute(
        """
        SELECT COALESCE(SUM(hours_spent), 0)
        FROM timesheets
        WHERE project_task_id = ? AND deleted_at IS NULL AND status IN ('saved', 'draft', 'submitted', 'approved')
        """,
        (task_id,),
    ).fetchone()[0]
    planned = Decimal(str(task["planned_hours"] or 0))
    spent_decimal = Decimal(str(spent or 0))
    progress = Decimal("0") if planned <= 0 else min((spent_decimal / planned) * 100, Decimal("100"))
    remaining = max(planned - spent_decimal, Decimal("0"))
    extra = max(spent_decimal - planned, Decimal("0"))
    conn.execute(
        """
        UPDATE project_tasks
        SET spent_hours = ?, remaining_hours = ?, extra_hours = ?, progress_percent = ?, updated_at = ?
        WHERE id = ?
        """,
        (
            float(spent_decimal),
            float(remaining),
            float(extra),
            float(progress),
            utc_now(),
            task_id,
        ),
    )
