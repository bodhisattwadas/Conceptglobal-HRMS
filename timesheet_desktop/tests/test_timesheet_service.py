from datetime import date
from decimal import Decimal
from pathlib import Path
import sqlite3
import sys
import unittest

sys.path.insert(0, str(Path(__file__).resolve().parents[1] / "src"))

from timesheet_desktop.database import apply_schema, seed_database
from timesheet_desktop.services import CurrentUser, LookupService, TimesheetService


class TimesheetServiceTest(unittest.TestCase):
    def setUp(self):
        self.conn = sqlite3.connect(":memory:")
        self.conn.row_factory = sqlite3.Row
        self.conn.execute("PRAGMA foreign_keys = ON")
        apply_schema(self.conn)
        seed_database(self.conn)
        self.conn.commit()
        self.user = CurrentUser(1, "Administrator", "admin@example.com", "Super Admin")

    def tearDown(self):
        self.conn.close()

    def test_create_timesheet_updates_task_progress(self):
        employee_id = self.conn.execute("SELECT id FROM employees LIMIT 1").fetchone()[0]
        task = self.conn.execute(
            "SELECT id, project_id, spent_hours FROM project_tasks WHERE planned_hours > 0 LIMIT 1"
        ).fetchone()
        before = Decimal(str(task["spent_hours"]))

        TimesheetService(self.conn).create_timesheet(
            employee_id=employee_id,
            project_id=task["project_id"],
            project_task_id=task["id"],
            entry_date=date.today(),
            hours_spent=Decimal("2.5"),
            description="Test entry",
            current_user=self.user,
            start_time="09:00 AM",
            end_time="11:30 AM",
        )

        updated = self.conn.execute("SELECT spent_hours FROM project_tasks WHERE id = ?", (task["id"],)).fetchone()
        saved = self.conn.execute(
            "SELECT start_time, end_time FROM timesheets ORDER BY id DESC LIMIT 1"
        ).fetchone()
        self.assertEqual(Decimal(str(updated["spent_hours"])), before + Decimal("2.5"))
        self.assertEqual(saved["start_time"], "09:00 AM")
        self.assertEqual(saved["end_time"], "11:30 AM")

    def test_daily_hour_limit_is_enforced(self):
        employee_id = self.conn.execute("SELECT id FROM employees LIMIT 1").fetchone()[0]
        task = self.conn.execute("SELECT id, project_id FROM project_tasks LIMIT 1").fetchone()

        with self.assertRaises(ValueError):
            TimesheetService(self.conn).create_timesheet(
                employee_id=employee_id,
                project_id=task["project_id"],
                project_task_id=task["id"],
                entry_date=date.today(),
                hours_spent=Decimal("25"),
                description="Too much",
                current_user=self.user,
            )

    def test_lookup_returns_seed_timesheets(self):
        rows = LookupService(self.conn).timesheets()
        self.assertGreaterEqual(len(rows), 5)


if __name__ == "__main__":
    unittest.main()
