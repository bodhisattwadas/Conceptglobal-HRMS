from __future__ import annotations

from datetime import date, datetime
from decimal import Decimal, InvalidOperation, ROUND_HALF_UP
import ctypes
import sys
import time
import tkinter as tk
from tkinter import messagebox, ttk

from ..config import APP_NAME, database_path
from ..database import connect
from ..services import CurrentUser, LookupService, TimesheetService


INACTIVITY_SECONDS = 10
BG_COLOR = "#F4F6F8"
SURFACE_COLOR = "#FFFFFF"
TEXT_COLOR = "#182230"
MUTED_COLOR = "#667085"
ACCENT_COLOR = "#7C3E66"
ACCENT_DARK = "#5F2D4E"
TEAL_COLOR = "#0F766E"
WARNING_COLOR = "#B42318"
BORDER_COLOR = "#D0D5DD"


class LASTINPUTINFO(ctypes.Structure):
    _fields_ = [("cbSize", ctypes.c_uint), ("dwTime", ctypes.c_uint)]


def system_idle_seconds() -> float | None:
    if sys.platform != "win32":
        return None
    last_input = LASTINPUTINFO()
    last_input.cbSize = ctypes.sizeof(LASTINPUTINFO)
    if not ctypes.windll.user32.GetLastInputInfo(ctypes.byref(last_input)):
        return None
    tick_count = ctypes.windll.kernel32.GetTickCount64()
    return max(0, tick_count - last_input.dwTime) / 1000


class TimesheetDesktopApp:
    def __init__(self) -> None:
        self.conn = connect()
        self.root = tk.Tk()
        self.root.title(APP_NAME)
        self.root.geometry("1140x760")
        self.root.minsize(980, 650)
        self.user = CurrentUser(1, "User", "user@example.com", "Employee")
        self.lookup = LookupService(self.conn)
        self.timesheets = TimesheetService(self.conn)

        self.timer_running = False
        self.timer_started_at: float | None = None
        self.current_elapsed_seconds = 0
        self.total_elapsed_seconds = 0
        self.last_activity_at = time.monotonic()
        self.timer_after_id: str | None = None

        self._configure_style()
        self._bind_activity_tracking()

    def run(self) -> None:
        self._show_main()
        self.root.mainloop()

    def _configure_style(self) -> None:
        style = ttk.Style(self.root)
        style.theme_use("clam")
        style.configure(".", font=("Segoe UI", 10), background=BG_COLOR, foreground=TEXT_COLOR)
        style.configure("App.TFrame", background=BG_COLOR)
        style.configure("Surface.TFrame", background=SURFACE_COLOR, bordercolor=BORDER_COLOR, relief="solid")
        style.configure("Toolbar.TFrame", background=SURFACE_COLOR)
        style.configure("Accent.TButton", background=ACCENT_COLOR, foreground="white", padding=(14, 8), borderwidth=0)
        style.map("Accent.TButton", background=[("active", ACCENT_DARK), ("disabled", "#D6BBCD")])
        style.configure("Ghost.TButton", background="#F2F4F7", foreground=TEXT_COLOR, padding=(12, 8), borderwidth=0)
        style.map("Ghost.TButton", background=[("active", "#E4E7EC")])
        style.configure("Header.TLabel", background=BG_COLOR, foreground=TEXT_COLOR, font=("Segoe UI", 20, "bold"))
        style.configure("Subtle.TLabel", background=BG_COLOR, foreground=MUTED_COLOR)
        style.configure("SurfaceTitle.TLabel", background=SURFACE_COLOR, foreground=TEXT_COLOR, font=("Segoe UI", 13, "bold"))
        style.configure("Section.TLabel", background=SURFACE_COLOR, foreground=TEXT_COLOR, font=("Segoe UI", 10, "bold"))
        style.configure("Timer.TLabel", background=SURFACE_COLOR, foreground=TEXT_COLOR, font=("Consolas", 28, "bold"))
        style.configure("Total.TLabel", background=SURFACE_COLOR, foreground=ACCENT_COLOR, font=("Segoe UI", 11, "bold"))
        style.configure("TimerMeta.TLabel", background=SURFACE_COLOR, foreground=ACCENT_COLOR, font=("Segoe UI", 12, "bold"))
        style.configure("Treeview", rowheight=30, background=SURFACE_COLOR, fieldbackground=SURFACE_COLOR, foreground=TEXT_COLOR, borderwidth=0)
        style.configure("Treeview.Heading", background="#EEF2F6", foreground=TEXT_COLOR, font=("Segoe UI", 10, "bold"), padding=(8, 8))
        style.map("Treeview", background=[("selected", "#D9E7F2")], foreground=[("selected", TEXT_COLOR)])
        style.configure("TNotebook", background=BG_COLOR, borderwidth=0)
        style.configure("TNotebook.Tab", padding=(18, 9), background="#EAECF0", foreground=MUTED_COLOR)
        style.map("TNotebook.Tab", background=[("selected", SURFACE_COLOR)], foreground=[("selected", ACCENT_COLOR)])
        self.root.configure(bg=BG_COLOR)

    def _bind_activity_tracking(self) -> None:
        for event_name in ("<KeyPress>", "<ButtonPress>", "<Motion>"):
            self.root.bind_all(event_name, self._mark_active, add="+")

    def _mark_active(self, _event=None) -> None:
        self.last_activity_at = time.monotonic()

    def _clear(self) -> None:
        for child in self.root.winfo_children():
            child.destroy()

    def _show_main(self) -> None:
        self._clear()
        container = ttk.Frame(self.root, padding=16, style="App.TFrame")
        container.pack(fill="both", expand=True)

        header = tk.Frame(container, bg=ACCENT_COLOR, padx=22, pady=16, highlightthickness=0)
        header.pack(fill="x", pady=(0, 14))
        title_group = tk.Frame(header, bg=ACCENT_COLOR)
        title_group.pack(side="left")
        tk.Label(title_group, text="Timesheet Tracker", bg=ACCENT_COLOR, fg="white", font=("Segoe UI", 22, "bold")).pack(anchor="w")
        tk.Label(title_group, text="Track focused work against projects and tasks", bg=ACCENT_COLOR, fg="#FCE7F3", font=("Segoe UI", 10)).pack(anchor="w")
        tk.Label(header, text="Single user desktop", bg=ACCENT_DARK, fg="white", padx=12, pady=7, font=("Segoe UI", 9, "bold")).pack(side="right")

        notebook = ttk.Notebook(container)
        notebook.pack(fill="both", expand=True)

        self.all_frame = ttk.Frame(notebook, padding=16, style="App.TFrame")
        self.create_frame = ttk.Frame(notebook, style="App.TFrame")
        notebook.add(self.all_frame, text="All Timesheets")
        notebook.add(self.create_frame, text="Create New")

        self._build_all_timesheets()
        self._build_create_new()

    def _build_all_timesheets(self) -> None:
        for child in self.all_frame.winfo_children():
            child.destroy()

        rows = self.lookup.timesheets()
        total_hours = sum(float(row["hours_spent"] or 0) for row in rows)
        billable_count = sum(1 for row in rows if row["is_billable"])

        toolbar = ttk.Frame(self.all_frame, padding=16, style="Surface.TFrame")
        toolbar.pack(fill="x", pady=(0, 12))
        ttk.Label(toolbar, text="All Timesheets", style="SurfaceTitle.TLabel").pack(side="left")
        ttk.Button(toolbar, text="Refresh", style="Ghost.TButton", command=self._build_all_timesheets).pack(side="right")
        self._metric(toolbar, "Entries", str(len(rows))).pack(side="right", padx=(0, 16))
        self._metric(toolbar, "Billable", str(billable_count)).pack(side="right", padx=(0, 10))
        self._metric(toolbar, "Total Hours", f"{total_hours:g}").pack(side="right", padx=(0, 10))

        tree = self._timesheet_tree(self.all_frame, rows)
        tree.pack(fill="both", expand=True)

    def _metric(self, parent: ttk.Frame, label: str, value: str) -> tk.Frame:
        box = tk.Frame(parent, bg="#F8FAFC", padx=12, pady=6, highlightbackground=BORDER_COLOR, highlightthickness=1)
        tk.Label(box, text=label, bg="#F8FAFC", fg=MUTED_COLOR, font=("Segoe UI", 8, "bold")).pack(anchor="w")
        tk.Label(box, text=value, bg="#F8FAFC", fg=ACCENT_COLOR, font=("Segoe UI", 13, "bold")).pack(anchor="w")
        return box

    def _timesheet_tree(self, parent: ttk.Frame, rows) -> ttk.Treeview:
        columns = ("date", "employee", "project", "task", "start", "end", "hours", "billable", "description")
        tree = ttk.Treeview(parent, columns=columns, show="headings")
        headings = {
            "date": "Date",
            "employee": "Employee",
            "project": "Project",
            "task": "Task",
            "start": "Start",
            "end": "End",
            "hours": "Hours",
            "billable": "Billable",
            "description": "Description",
        }
        widths = {
            "date": 100,
            "employee": 160,
            "project": 150,
            "task": 220,
            "start": 80,
            "end": 80,
            "hours": 80,
            "billable": 80,
            "description": 260,
        }
        for key, label in headings.items():
            tree.heading(key, text=label)
            tree.column(key, width=widths[key], anchor="w")
        for row in rows:
            tag = "even" if len(tree.get_children()) % 2 == 0 else "odd"
            tree.insert(
                "",
                "end",
                values=(
                    row["date"],
                    row["employee"],
                    row["project"],
                    row["task"],
                    row["start_time"] or "",
                    row["end_time"] or "",
                    f'{row["hours_spent"]:g}',
                    "Yes" if row["is_billable"] else "No",
                    row["description"] or "",
                ),
                tags=(tag,),
            )
        tree.tag_configure("even", background=SURFACE_COLOR)
        tree.tag_configure("odd", background="#F8FAFC")
        return tree

    def _build_create_new(self) -> None:
        for child in self.create_frame.winfo_children():
            child.destroy()

        canvas = tk.Canvas(self.create_frame, bg=BG_COLOR, highlightthickness=0)
        scrollbar = ttk.Scrollbar(self.create_frame, orient="vertical", command=canvas.yview)
        scroll_content = ttk.Frame(canvas, padding=16, style="App.TFrame")
        scroll_window = canvas.create_window((0, 0), window=scroll_content, anchor="nw")
        canvas.configure(yscrollcommand=scrollbar.set)
        canvas.pack(side="left", fill="both", expand=True)
        scrollbar.pack(side="right", fill="y")

        def update_scroll_region(_event=None) -> None:
            canvas.configure(scrollregion=canvas.bbox("all"))

        def update_content_width(event) -> None:
            canvas.itemconfigure(scroll_window, width=event.width)

        def on_mousewheel(event) -> None:
            canvas.yview_scroll(int(-1 * (event.delta / 120)), "units")

        scroll_content.bind("<Configure>", update_scroll_region)
        canvas.bind("<Configure>", update_content_width)
        canvas.bind_all("<MouseWheel>", on_mousewheel, add="+")

        employees = self.lookup.employees()
        projects = self.lookup.projects()
        self.employee_map = {row["name"]: row["id"] for row in employees}
        self.project_map = {row["name"]: row["id"] for row in projects}
        self.task_map: dict[str, int] = {}

        self.employee_var = tk.StringVar(value=employees[0]["name"] if employees else "")
        self.project_var = tk.StringVar(value=projects[0]["name"] if projects else "")
        self.task_var = tk.StringVar()
        self.date_var = tk.StringVar(value=date.today().strftime("%d/%m/%Y"))
        self.start_time_var = tk.StringVar(value="--:-- --")
        self.end_time_var = tk.StringVar(value="--:-- --")
        self.hours_var = tk.StringVar(value="1.00")
        self.billable_var = tk.StringVar(value="Yes")
        self.note_var = tk.StringVar()
        self.timer_text_var = tk.StringVar(value="00:00:00")
        self.timer_status_var = tk.StringVar(value="Stopped")
        self.timer_total_var = tk.StringVar(value="Total Time Spent: 0 hr 0 min 0 sec")

        form = ttk.Frame(scroll_content, padding=18, style="Surface.TFrame")
        form.pack(fill="x")
        form.columnconfigure(1, weight=1)
        form.columnconfigure(3, weight=1)

        ttk.Label(form, text="Create New Timesheet", style="SurfaceTitle.TLabel").grid(row=0, column=0, columnspan=4, sticky="w", pady=(0, 16))

        self._field(form, "Employee", self._combo(form, self.employee_var, list(self.employee_map)), 1, 0)
        self._field(form, "Date", ttk.Entry(form, textvariable=self.date_var), 1, 2)
        project_combo = self._combo(form, self.project_var, list(self.project_map))
        self._field(form, "Project", project_combo, 2, 0)
        self.task_combo = self._combo(form, self.task_var, [])
        self._field(form, "Task", self.task_combo, 2, 2)
        self._field(form, "Billable", self._combo(form, self.billable_var, ["No", "Yes"]), 3, 0)

        ttk.Label(form, text="Description", style="Section.TLabel").grid(row=8, column=0, sticky="w", pady=(12, 4))
        self.description = tk.Text(form, height=4, wrap="word", relief="solid", bd=1, highlightthickness=1, highlightbackground=BORDER_COLOR, font=("Segoe UI", 10))
        self.description.grid(row=9, column=0, columnspan=4, sticky="nsew", pady=(0, 14))

        self._build_timer(form)

        actions = ttk.Frame(scroll_content, style="App.TFrame")
        actions.pack(fill="x", pady=(12, 0))
        ttk.Button(actions, text="Clear", style="Ghost.TButton", command=self._clear_form).pack(side="right")
        ttk.Button(actions, text="Save Timesheet", style="Accent.TButton", command=self._save_timesheet).pack(side="right", padx=(0, 8))

        self.project_var.trace_add("write", self._load_tasks)
        self._load_tasks()

    def _field(self, parent: ttk.Frame, label: str, widget: tk.Widget, row: int, column: int) -> None:
        ttk.Label(parent, text=label, style="Section.TLabel").grid(row=row * 2, column=column, sticky="w", padx=(0, 28), pady=(0, 4))
        widget.grid(row=row * 2 + 1, column=column, columnspan=2 if column == 2 else 1, sticky="ew", padx=(0, 28), pady=(0, 12), ipady=3)

    def _combo(self, parent: ttk.Frame, variable: tk.StringVar, values: list[str]) -> ttk.Combobox:
        return ttk.Combobox(parent, textvariable=variable, values=values, state="readonly")

    def _build_timer(self, parent: ttk.Frame) -> None:
        ttk.Label(parent, text="Work Timer", style="SurfaceTitle.TLabel").grid(row=10, column=0, sticky="w", pady=(4, 8))
        timer = ttk.Frame(parent, padding=16, style="Surface.TFrame")
        timer.grid(row=11, column=0, columnspan=4, sticky="ew")
        timer.columnconfigure(2, weight=1)

        ttk.Label(timer, textvariable=self.timer_text_var, style="Timer.TLabel").grid(row=0, column=0, sticky="w")
        self.timer_status_label = tk.Label(timer, textvariable=self.timer_status_var, bg=SURFACE_COLOR, fg=MUTED_COLOR, font=("Segoe UI", 9, "bold"))
        self.timer_status_label.grid(row=1, column=0, sticky="w")
        ttk.Label(timer, textvariable=self.timer_total_var, style="Total.TLabel").grid(row=2, column=0, sticky="w")

        ttk.Label(timer, text="Notes", style="Section.TLabel").grid(row=0, column=1, sticky="e", padx=(28, 8))
        ttk.Entry(timer, textvariable=self.note_var).grid(row=0, column=2, sticky="ew", padx=(0, 10), ipady=4)
        self.run_button = ttk.Button(timer, text="Run", style="Accent.TButton", command=self._start_timer)
        self.run_button.grid(row=0, column=3, padx=(0, 6))
        self.stop_button = ttk.Button(timer, text="Stop", style="Ghost.TButton", command=self._stop_timer_by_button)
        self.stop_button.grid(row=0, column=4)
        self._set_timer_buttons(running=False)

        details = ttk.Frame(timer, style="Toolbar.TFrame")
        details.grid(row=3, column=0, columnspan=5, sticky="ew", pady=(14, 0))
        details.columnconfigure(1, weight=1)
        details.columnconfigure(3, weight=1)
        self._timer_detail(details, "Start Time", self.start_time_var, 0, 0)
        self._timer_detail(details, "End Time", self.end_time_var, 0, 2)

        self.action_tree = ttk.Treeview(parent, columns=("action", "time", "elapsed", "total", "note"), show="headings", height=5)
        for column, width in {"action": 130, "time": 170, "elapsed": 130, "total": 130, "note": 430}.items():
            self.action_tree.heading(column, text=column.title(), anchor="center")
            self.action_tree.column(column, width=width, anchor="center")
        self.action_tree.grid(row=12, column=0, columnspan=4, sticky="ew", pady=(12, 0))

    def _timer_detail(self, parent: ttk.Frame, label: str, variable: tk.StringVar, row: int, column: int) -> None:
        ttk.Label(parent, text=label, style="Section.TLabel").grid(row=row, column=column, sticky="w", padx=(0, 8))
        ttk.Label(parent, textvariable=variable, style="TimerMeta.TLabel").grid(row=row, column=column + 1, sticky="w", padx=(0, 28))

    def _load_tasks(self, *_args) -> None:
        project_id = self.project_map.get(self.project_var.get())
        task_rows = self.lookup.tasks_for_project(project_id) if project_id else []
        self.task_map = {f"{self.project_var.get()} / {row['title']}": row["id"] for row in task_rows}
        self.task_combo["values"] = list(self.task_map)
        self.task_var.set(next(iter(self.task_map), ""))

    def _start_timer(self) -> None:
        if self.timer_running:
            return
        self.timer_running = True
        self.timer_started_at = time.monotonic()
        self.current_elapsed_seconds = 0
        now_text = datetime.now().strftime("%I:%M %p")
        if self.start_time_var.get().startswith("--"):
            self.start_time_var.set(now_text)
        self.end_time_var.set("--:-- --")
        self.timer_status_var.set("Running")
        self._set_timer_buttons(running=True)
        self._mark_active()
        self._refresh_timer_labels()
        self._sync_hours_from_timer()
        self._add_action("Started", self.current_elapsed_seconds)
        self._tick_timer()

    def _stop_timer_by_button(self) -> None:
        self._stop_timer("Stopped by button")

    def _stop_timer(self, reason: str = "Stopped") -> None:
        if not self.timer_running:
            return
        if self.timer_after_id:
            self.root.after_cancel(self.timer_after_id)
            self.timer_after_id = None
        self._update_current_elapsed()
        self.timer_running = False
        self.total_elapsed_seconds += self.current_elapsed_seconds
        self.end_time_var.set(datetime.now().strftime("%I:%M %p"))
        self.timer_status_var.set(reason)
        self._set_timer_buttons(running=False)
        self._refresh_timer_labels()
        self._sync_hours_from_timer()
        self._add_action(reason, self.current_elapsed_seconds)
        self.current_elapsed_seconds = 0

    def _tick_timer(self) -> None:
        if not self.timer_running or self.timer_started_at is None:
            return
        now = time.monotonic()
        idle_seconds = system_idle_seconds()
        if idle_seconds is None:
            idle_seconds = now - self.last_activity_at
        if idle_seconds >= INACTIVITY_SECONDS:
            self._stop_timer("Stopped by timeout")
            return
        self._update_current_elapsed()
        self._refresh_timer_labels()
        self._sync_hours_from_timer()
        self.timer_after_id = self.root.after(1000, self._tick_timer)

    def _update_current_elapsed(self) -> None:
        if self.timer_started_at is None:
            self.current_elapsed_seconds = 0
            return
        self.current_elapsed_seconds = int(time.monotonic() - self.timer_started_at)

    def _refresh_timer_labels(self) -> None:
        self.timer_text_var.set(self._format_hms(self.current_elapsed_seconds))
        total = self.total_elapsed_seconds + (self.current_elapsed_seconds if self.timer_running else 0)
        hours, remainder = divmod(total, 3600)
        minutes, seconds = divmod(remainder, 60)
        self.timer_total_var.set(f"Total Time Spent: {hours} hr {minutes} min {seconds} sec")

    def _sync_hours_from_timer(self) -> None:
        total = self.total_elapsed_seconds + (self.current_elapsed_seconds if self.timer_running else 0)
        if total <= 0:
            return
        hours = (Decimal(total) / Decimal(3600)).quantize(Decimal("0.0001"), rounding=ROUND_HALF_UP)
        self.hours_var.set(str(hours))

    def _add_action(self, action: str, elapsed_seconds: int | None = None) -> None:
        elapsed = self._format_hms(elapsed_seconds if elapsed_seconds is not None else self.current_elapsed_seconds)
        total = self._format_hms(self.total_elapsed_seconds + (self.current_elapsed_seconds if self.timer_running else 0))
        tag = "even" if len(self.action_tree.get_children()) % 2 == 0 else "odd"
        self.action_tree.insert(
            "",
            "end",
            values=(action, datetime.now().strftime("%d/%m/%Y %I:%M:%S %p"), elapsed, total, self.note_var.get().strip()),
            tags=(tag,),
        )
        self.action_tree.tag_configure("even", background=SURFACE_COLOR)
        self.action_tree.tag_configure("odd", background="#F8FAFC")

    def _format_hms(self, seconds: int) -> str:
        hours, remainder = divmod(max(seconds, 0), 3600)
        minutes, seconds = divmod(remainder, 60)
        return f"{hours:02}:{minutes:02}:{seconds:02}"

    def _set_timer_buttons(self, *, running: bool) -> None:
        self.run_button.configure(state="disabled" if running else "normal")
        self.stop_button.configure(state="normal" if running else "disabled")
        self.timer_status_label.configure(fg=TEAL_COLOR if running else MUTED_COLOR)

    def _parse_date(self) -> date:
        value = self.date_var.get().strip()
        for fmt in ("%d/%m/%Y", "%Y-%m-%d"):
            try:
                return datetime.strptime(value, fmt).date()
            except ValueError:
                pass
        raise ValueError("Date must be dd/mm/yyyy.")

    def _save_timesheet(self) -> None:
        try:
            hours = Decimal(self.hours_var.get())
            description = self.description.get("1.0", "end").strip()
            note = self.note_var.get().strip()
            if note:
                description = f"{description}\nTimer note: {note}".strip()
            self.timesheets.create_timesheet(
                employee_id=self.employee_map[self.employee_var.get()],
                project_id=self.project_map[self.project_var.get()],
                project_task_id=self.task_map[self.task_var.get()],
                entry_date=self._parse_date(),
                start_time=None if self.start_time_var.get().startswith("--") else self.start_time_var.get(),
                end_time=None if self.end_time_var.get().startswith("--") else self.end_time_var.get(),
                hours_spent=hours,
                description=description,
                current_user=self.user,
                is_billable=self.billable_var.get() == "Yes",
            )
        except (KeyError, ValueError, InvalidOperation) as exc:
            messagebox.showerror("Save Timesheet", str(exc))
            return
        messagebox.showinfo("Save Timesheet", "Timesheet saved.")
        self._add_action("Saved")
        self._build_all_timesheets()
        self._clear_form()

    def _clear_form(self) -> None:
        if self.timer_running:
            self._stop_timer_by_button()
        self._add_action("Cleared")
        self.date_var.set(date.today().strftime("%d/%m/%Y"))
        self.start_time_var.set("--:-- --")
        self.end_time_var.set("--:-- --")
        self.hours_var.set("1.00")
        self.billable_var.set("Yes")
        self.note_var.set("")
        self.description.delete("1.0", "end")
        self.current_elapsed_seconds = 0
        self.total_elapsed_seconds = 0
        self.timer_text_var.set("00:00:00")
        self.timer_status_var.set("Stopped")
        self.timer_total_var.set("Total Time Spent: 0 hr 0 min 0 sec")
        self._set_timer_buttons(running=False)
