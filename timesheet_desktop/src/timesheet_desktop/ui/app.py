from __future__ import annotations

from datetime import date, datetime, timedelta
from decimal import Decimal, InvalidOperation, ROUND_HALF_UP
import ctypes
import sys
import time
import tkinter as tk
from tkinter import messagebox, ttk
import uuid

from ..api_client import ApiError, LaravelTimesheetApi
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
STATUS_STYLES = {
    "draft": ("Draft", "#7C3E66", "#FDF2FA"),
    "running": ("Running", "#B54708", "#FFFAEB"),
    "submitted": ("Submitted", "#175CD3", "#EFF8FF"),
    "approved": ("Approved", "#027A48", "#ECFDF3"),
    "rejected": ("Rejected", "#B42318", "#FEF3F2"),
    "cancelled": ("Cancelled", "#475467", "#F2F4F7"),
}


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
        self.api = LaravelTimesheetApi("http://127.0.0.1:8000")
        self.remote_user: dict | None = None
        self.remote_projects: list[dict] = []
        self.remote_timesheets: list[dict] = []
        self.current_timesheet_id: int | None = None
        self.current_desktop_uuid: str | None = None
        self.status_badge_images: dict[str, tk.PhotoImage] = {}
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
        self._show_login()
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
        style.configure("Refresh.TButton", background=TEAL_COLOR, foreground="white", padding=(12, 8), borderwidth=0)
        style.map("Refresh.TButton", background=[("active", "#0B5F59")], foreground=[("active", "white")])
        style.configure("Danger.TButton", background=WARNING_COLOR, foreground="white", padding=(12, 8), borderwidth=0)
        style.map(
            "Danger.TButton",
            background=[("disabled", "#F2F4F7"), ("active", "#912018")],
            foreground=[("disabled", "#98A2B3"), ("active", "white")],
        )
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

    def _show_login(self) -> None:
        self._clear()
        wrapper = ttk.Frame(self.root, padding=24, style="App.TFrame")
        wrapper.pack(fill="both", expand=True)

        panel = ttk.Frame(wrapper, padding=28, style="Surface.TFrame")
        panel.place(relx=0.5, rely=0.5, anchor="center", width=460)

        ttk.Label(panel, text="Timesheet Tracker", style="SurfaceTitle.TLabel").pack(anchor="w")
        ttk.Label(panel, text="Login with your Laravel employee account", style="Section.TLabel").pack(anchor="w", pady=(4, 20))

        api_url_var = tk.StringVar(value=self.api.base_url)
        email_var = tk.StringVar()
        password_var = tk.StringVar()

        self._login_field(panel, "Laravel URL", ttk.Entry(panel, textvariable=api_url_var))
        self._login_field(panel, "Email", ttk.Entry(panel, textvariable=email_var))
        password_entry = ttk.Entry(panel, textvariable=password_var, show="*")
        self._login_field(panel, "Password", password_entry)

        status_var = tk.StringVar(value="")
        ttk.Label(panel, textvariable=status_var, style="Section.TLabel").pack(anchor="w", pady=(4, 12))

        def submit() -> None:
            try:
                status_var.set("Connecting...")
                self.root.update_idletasks()
                self.api = LaravelTimesheetApi(api_url_var.get().strip())
                session = self.api.login(email_var.get().strip(), password_var.get())
                bootstrap = self.api.bootstrap()
                self.remote_user = session.user
                self.remote_projects = bootstrap.get("projects", [])
                self.remote_timesheets = self.api.timesheets()
            except ApiError as exc:
                status_var.set("")
                messagebox.showerror("Login failed", str(exc))
                return
            self._show_main()

        ttk.Button(panel, text="Login", style="Accent.TButton", command=submit).pack(fill="x", pady=(10, 0))
        password_entry.bind("<Return>", lambda _event: submit())

    def _login_field(self, parent: ttk.Frame, label: str, widget: tk.Widget) -> None:
        ttk.Label(parent, text=label, style="Section.TLabel").pack(anchor="w", pady=(0, 4))
        widget.pack(fill="x", pady=(0, 12), ipady=4)

    def _show_main(self) -> None:
        self._clear()
        container = ttk.Frame(self.root, padding=16, style="App.TFrame")
        container.pack(fill="both", expand=True)

        header = tk.Frame(container, bg=ACCENT_COLOR, padx=22, pady=16, highlightthickness=0)
        header.pack(fill="x", pady=(0, 14))
        title_group = tk.Frame(header, bg=ACCENT_COLOR)
        title_group.pack(side="left")
        tk.Label(title_group, text="Timesheet Tracker", bg=ACCENT_COLOR, fg="white", font=("Segoe UI", 22, "bold")).pack(anchor="w")
        employee_name = self.remote_user.get("employee", {}).get("name", "Employee") if self.remote_user else "Employee"
        tk.Label(title_group, text=f"Track focused work for {employee_name}", bg=ACCENT_COLOR, fg="#FCE7F3", font=("Segoe UI", 10)).pack(anchor="w")
        tk.Label(header, text="Laravel synced", bg=ACCENT_DARK, fg="white", padx=12, pady=7, font=("Segoe UI", 9, "bold")).pack(side="right")

        self.notebook = ttk.Notebook(container)
        self.notebook.pack(fill="both", expand=True)

        self.all_frame = ttk.Frame(self.notebook, padding=16, style="App.TFrame")
        self.create_frame = ttk.Frame(self.notebook, style="App.TFrame")
        self.notebook.add(self.all_frame, text="All Timesheets")
        self.notebook.add(self.create_frame, text="Create New")

        self._build_all_timesheets()
        self._build_create_new()

    def _build_all_timesheets(self) -> None:
        for child in self.all_frame.winfo_children():
            child.destroy()

        rows = self.remote_timesheets
        total_hours = sum(float(row.get("hours_spent") or 0) for row in rows)
        billable_count = sum(1 for row in rows if row.get("is_billable"))

        toolbar = ttk.Frame(self.all_frame, padding=16, style="Surface.TFrame")
        toolbar.pack(fill="x", pady=(0, 12))
        ttk.Label(toolbar, text="All Timesheets", style="SurfaceTitle.TLabel").pack(side="left")
        self.refresh_button = ttk.Button(toolbar, text="⟳ Refresh", style="Refresh.TButton", command=self._refresh_remote_timesheets)
        self.refresh_button.pack(side="right")
        self._metric(toolbar, "Entries", str(len(rows))).pack(side="right", padx=(0, 16))
        self._metric(toolbar, "Billable", str(billable_count)).pack(side="right", padx=(0, 10))
        self._metric(toolbar, "Total Hours", f"{total_hours:g}").pack(side="right", padx=(0, 10))

        tree = self._timesheet_tree(self.all_frame, rows)
        tree.bind("<ButtonRelease-1>", lambda _event: self._open_selected_draft(tree, notify=False))
        tree.bind("<Double-1>", lambda _event: self._open_selected_draft(tree, notify=True))
        tree.bind("<Return>", lambda _event: self._open_selected_draft(tree, notify=True))
        tree.pack(fill="both", expand=True)

    def _metric(self, parent: ttk.Frame, label: str, value: str) -> tk.Frame:
        box = tk.Frame(parent, bg="#F8FAFC", padx=12, pady=6, highlightbackground=BORDER_COLOR, highlightthickness=1)
        tk.Label(box, text=label, bg="#F8FAFC", fg=MUTED_COLOR, font=("Segoe UI", 8, "bold")).pack(anchor="w")
        tk.Label(box, text=value, bg="#F8FAFC", fg=ACCENT_COLOR, font=("Segoe UI", 13, "bold")).pack(anchor="w")
        return box

    def _timesheet_tree(self, parent: ttk.Frame, rows) -> ttk.Treeview:
        columns = ("date", "employee", "project", "task", "start", "end", "hours", "billable", "description")
        tree = ttk.Treeview(parent, columns=columns, show=("tree", "headings"))
        tree.heading("#0", text="Status", anchor="center")
        tree.column("#0", width=130, anchor="center", stretch=False)
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
        self._ensure_status_badge_images()
        for key, label in headings.items():
            tree.heading(key, text=label, anchor="center")
            tree.column(key, width=widths[key], anchor="center")
        for row in rows:
            stripe_tag = "even" if len(tree.get_children()) % 2 == 0 else "odd"
            status = self._status_key(row)
            status_label, _status_fg, _status_bg = self._status_style(status)
            tree.insert(
                "",
                "end",
                iid=str(row.get("desktop_uuid") or row.get("id")),
                text=f" {status_label}",
                image=self.status_badge_images.get(status) or self.status_badge_images["unknown"],
                values=(
                    row["date"],
                    self._row_employee_name(row),
                    self._row_project_name(row),
                    self._row_task_title(row),
                    row.get("start_time") or "",
                    row.get("end_time") or "",
                    f'{float(row.get("hours_spent") or 0):g}',
                    "Yes" if row.get("is_billable") else "No",
                    row.get("description") or "",
                ),
                tags=(stripe_tag, f"status-{status}"),
            )
        tree.tag_configure("even", background=SURFACE_COLOR)
        tree.tag_configure("odd", background="#F8FAFC")
        for status in [*STATUS_STYLES, "unknown"]:
            _label, color, _background = self._status_style(status)
            tree.tag_configure(f"status-{status}", foreground=color)
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

        projects = self.remote_projects
        employee_name = self.remote_user.get("employee", {}).get("name", "Employee") if self.remote_user else "Employee"
        employee_id = self.remote_user.get("employee", {}).get("id", 0) if self.remote_user else 0
        self.employee_map = {employee_name: employee_id}
        self.project_map = {row["name"]: row["id"] for row in projects}
        self.project_lookup = {row["id"]: row for row in projects}
        self.task_map: dict[str, int] = {}

        self.employee_var = tk.StringVar(value=employee_name)
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
        self.current_timesheet_id = None
        self.current_desktop_uuid = None

        form = ttk.Frame(scroll_content, padding=18, style="Surface.TFrame")
        form.pack(fill="x")
        form.columnconfigure(1, weight=1)
        form.columnconfigure(3, weight=1)

        self.form_title_var = tk.StringVar(value="Create New Timesheet")
        ttk.Label(form, textvariable=self.form_title_var, style="SurfaceTitle.TLabel").grid(row=0, column=0, columnspan=4, sticky="w", pady=(0, 16))

        self._field(form, "Employee", ttk.Label(form, textvariable=self.employee_var, style="TimerMeta.TLabel"), 1, 0)
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
        self.delete_button = ttk.Button(actions, text="Delete", style="Danger.TButton", command=self._delete_current_timesheet)
        self.delete_button.pack(side="right")
        ttk.Button(actions, text="Save and Submit Final", style="Accent.TButton", command=lambda: self._save_timesheet(submit_final=True)).pack(side="right", padx=(0, 8))
        ttk.Button(actions, text="Save Draft", style="Ghost.TButton", command=lambda: self._save_timesheet(submit_final=False)).pack(side="right", padx=(0, 8))
        self._set_delete_button_state()

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
        project = self.project_lookup.get(project_id, {}) if project_id else {}
        task_rows = project.get("tasks", [])
        self.task_map = {f"{self.project_var.get()} / {row['title']}": row["id"] for row in task_rows}
        self.task_combo["values"] = list(self.task_map)
        self.task_var.set(next(iter(self.task_map), ""))

    def _start_timer(self) -> None:
        if self.timer_running:
            return
        running_row = self._other_running_timesheet()
        if running_row:
            messagebox.showerror(
                "Run Timer",
                "Another timesheet is already running. Stop it before starting a new one.",
            )
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
        self._persist_timer_status("running")
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
        self._persist_timer_status("draft")

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

    def _persist_timer_status(self, status: str) -> None:
        try:
            hours = self._hours_for_payload()
            description = self.description.get("1.0", "end").strip()
            desktop_uuid = self.current_desktop_uuid or str(uuid.uuid4())
            saved_timesheet = self.api.create_timesheet(
                {
                    "id": self.current_timesheet_id,
                    "desktop_uuid": desktop_uuid,
                    "project_id": self.project_map[self.project_var.get()],
                    "project_task_id": self.task_map[self.task_var.get()],
                    "date": self._parse_date().isoformat(),
                    "start_time": None if self.start_time_var.get().startswith("--") else self.start_time_var.get(),
                    "end_time": None if self.end_time_var.get().startswith("--") else self.end_time_var.get(),
                    "hours_spent": float(hours),
                    "timer_elapsed_seconds": self.total_elapsed_seconds + (self.current_elapsed_seconds if self.timer_running else 0),
                    "timer_logs": self._timer_logs_payload(),
                    "description": description,
                    "is_billable": self.billable_var.get() == "Yes",
                    "submit_final": False,
                    "status": status,
                }
            )
        except (KeyError, ValueError, InvalidOperation, ApiError) as exc:
            messagebox.showerror("Timer Sync", str(exc))
            return

        self.current_timesheet_id = saved_timesheet.get("id") or self.current_timesheet_id
        self.current_desktop_uuid = saved_timesheet.get("desktop_uuid") or desktop_uuid
        self.form_title_var.set("Resume Running Timesheet" if status == "running" else "Resume Draft Timesheet")
        self._set_delete_button_state()
        self._refresh_remote_timesheets()

    def _hours_for_payload(self) -> Decimal:
        total = self.total_elapsed_seconds + (self.current_elapsed_seconds if self.timer_running else 0)
        if total > 0:
            return (Decimal(total) / Decimal(3600)).quantize(Decimal("0.0001"), rounding=ROUND_HALF_UP)
        if self.timer_running:
            return Decimal("0.0001")
        try:
            entered_hours = Decimal(self.hours_var.get())
            if entered_hours > 0:
                return entered_hours
        except InvalidOperation:
            pass
        return Decimal("0.0001")

    def _save_timesheet(self, *, submit_final: bool) -> None:
        try:
            hours = self._hours_for_payload()
            description = self.description.get("1.0", "end").strip()
            desktop_uuid = self.current_desktop_uuid or str(uuid.uuid4())
            saved_timesheet = self.api.create_timesheet(
                {
                    "id": self.current_timesheet_id,
                    "desktop_uuid": desktop_uuid,
                    "project_id": self.project_map[self.project_var.get()],
                    "project_task_id": self.task_map[self.task_var.get()],
                    "date": self._parse_date().isoformat(),
                    "start_time": None if self.start_time_var.get().startswith("--") else self.start_time_var.get(),
                    "end_time": None if self.end_time_var.get().startswith("--") else self.end_time_var.get(),
                    "hours_spent": float(hours),
                    "timer_elapsed_seconds": self.total_elapsed_seconds,
                    "timer_logs": self._timer_logs_payload(save_action="Submitted Final" if submit_final else "Saved Draft"),
                    "description": description,
                    "is_billable": self.billable_var.get() == "Yes",
                    "submit_final": submit_final,
                    "status": "draft",
                }
            )
        except (KeyError, ValueError, InvalidOperation, ApiError) as exc:
            messagebox.showerror("Save Timesheet", str(exc))
            return
        self._refresh_remote_timesheets()
        self._add_action("Submitted Final" if submit_final else "Saved Draft")
        messagebox.showinfo("Save Timesheet", "Timesheet submitted as final." if submit_final else "Timesheet saved as draft.")
        if submit_final:
            self._clear_form()
            self.notebook.select(self.all_frame)
            return

        self.current_timesheet_id = saved_timesheet.get("id") or self.current_timesheet_id
        self.current_desktop_uuid = saved_timesheet.get("desktop_uuid") or desktop_uuid
        self.form_title_var.set("Resume Draft Timesheet")
        self._set_delete_button_state()
        self._refresh_timer_labels()

    def _delete_current_timesheet(self) -> None:
        if not self.current_timesheet_id:
            messagebox.showinfo("Delete Timesheet", "Load a draft timesheet before deleting.")
            return

        confirmed = messagebox.askyesno(
            "Delete Timesheet",
            "This will permanently delete this draft timesheet. This action cannot be undone.\n\nDo you want to continue?",
            icon="warning",
        )
        if not confirmed:
            return

        if self.timer_running:
            self._stop_timer_by_button()

        try:
            self.api.delete_timesheet(int(self.current_timesheet_id))
        except ApiError as exc:
            messagebox.showerror("Delete Timesheet", str(exc))
            return

        messagebox.showinfo("Delete Timesheet", "Timesheet deleted.")
        self._refresh_remote_timesheets()
        self._clear_form()
        self.notebook.select(self.all_frame)

    def _timer_logs_payload(self, *, save_action: str | None = None) -> list[dict]:
        logs = []
        for item in self.action_tree.get_children():
            action, logged_at, elapsed, total, note = self.action_tree.item(item, "values")
            logs.append({"action": action, "time": logged_at, "elapsed": elapsed, "total": total, "note": note})
        if save_action:
            logs.append(
                {
                    "action": save_action,
                    "time": datetime.now().strftime("%d/%m/%Y %I:%M:%S %p"),
                    "elapsed": "00:00:00",
                    "total": self._format_hms(self.total_elapsed_seconds),
                    "note": self.note_var.get().strip(),
                }
            )
        return logs

    def _refresh_remote_timesheets(self) -> None:
        if hasattr(self, "refresh_button"):
            self.refresh_button.configure(text="↻ Syncing...", state="disabled")
            self.root.update_idletasks()
        try:
            self.remote_timesheets = self.api.timesheets()
        except ApiError as exc:
            if hasattr(self, "refresh_button"):
                self.refresh_button.configure(text="⟳ Refresh", state="normal")
            messagebox.showerror("Refresh failed", str(exc))
            return
        self._build_all_timesheets()

    def _open_selected_draft(self, tree: ttk.Treeview, *, notify: bool) -> None:
        selected = tree.selection()
        if not selected:
            return
        desktop_uuid = selected[0]
        row = next((item for item in self.remote_timesheets if str(item.get("desktop_uuid") or item.get("id")) == desktop_uuid), None)
        if not row:
            return
        if row.get("status") in {"submitted", "approved"}:
            self._show_readonly_timesheet(row)
            return
        if row.get("status") not in {"draft", "running"}:
            if notify:
                messagebox.showinfo("Timesheet", "Only draft or running timesheets can be resumed.")
            return
        if self.timer_running and self._is_current_timesheet(row):
            self.notebook.select(self.create_frame)
            return
        self._load_draft_into_form(row)

    def _load_draft_into_form(self, row: dict) -> None:
        if self.timer_running:
            self._stop_timer_by_button()
        self.current_timesheet_id = row.get("id")
        self.current_desktop_uuid = row.get("desktop_uuid")
        is_running = row.get("status") == "running"
        self.form_title_var.set("Resume Running Timesheet" if is_running else "Resume Draft Timesheet")
        self._set_delete_button_state()
        self.notebook.select(self.create_frame)
        self.date_var.set(self._display_date(row.get("date")))
        project_name = self._row_project_name(row)
        if project_name in self.project_map:
            self.project_var.set(project_name)
            self._load_tasks()
        task_title = self._row_task_title(row)
        task_key = next((key for key in self.task_map if key.endswith(f" / {task_title}")), "")
        if task_key:
            self.task_var.set(task_key)
        self.start_time_var.set(self._display_time(row.get("start_time")) or "--:-- --")
        self.end_time_var.set(self._display_time(row.get("end_time")) or "--:-- --")
        self.hours_var.set(str(row.get("hours_spent") or "1.00"))
        self.billable_var.set("Yes" if row.get("is_billable") else "No")
        self.description.delete("1.0", "end")
        self.description.insert("1.0", row.get("description") or "")
        self.total_elapsed_seconds = int(row.get("timer_elapsed_seconds") or 0)
        self.current_elapsed_seconds = 0
        self.timer_running = False
        self.timer_started_at = None
        self._clear_action_logs()
        for log in row.get("timer_logs") or []:
            tag = "even" if len(self.action_tree.get_children()) % 2 == 0 else "odd"
            self.action_tree.insert(
                "",
                "end",
                values=(
                    log.get("action", ""),
                    log.get("time", ""),
                    log.get("elapsed", ""),
                    log.get("total", ""),
                    log.get("note", ""),
                ),
                tags=(tag,),
            )
        self.action_tree.tag_configure("even", background=SURFACE_COLOR)
        self.action_tree.tag_configure("odd", background="#F8FAFC")
        if is_running:
            self._resume_running_timer(row)
        else:
            self.timer_status_var.set("Stopped")
            self._set_timer_buttons(running=False)
            self._refresh_timer_labels()

    def _is_current_timesheet(self, row: dict) -> bool:
        row_id = row.get("id")
        row_uuid = row.get("desktop_uuid")
        return (row_id is not None and row_id == self.current_timesheet_id) or (
            row_uuid is not None and row_uuid == self.current_desktop_uuid
        )

    def _resume_running_timer(self, row: dict) -> None:
        elapsed_since_start = self._running_elapsed_seconds(row.get("timer_logs") or [])
        self.current_elapsed_seconds = elapsed_since_start
        self.timer_started_at = time.monotonic() - elapsed_since_start
        self.timer_running = True
        self.timer_status_var.set("Running")
        self.end_time_var.set("--:-- --")
        self._set_timer_buttons(running=True)
        self._mark_active()
        self._refresh_timer_labels()
        self._sync_hours_from_timer()
        self._tick_timer()

    def _running_elapsed_seconds(self, logs: list[dict]) -> int:
        latest_started_at: datetime | None = None
        for log in logs:
            if log.get("action") != "Started":
                continue
            logged_at = log.get("time")
            if not logged_at:
                continue
            try:
                parsed = datetime.strptime(logged_at, "%d/%m/%Y %I:%M:%S %p")
            except ValueError:
                continue
            if latest_started_at is None or parsed > latest_started_at:
                latest_started_at = parsed

        if latest_started_at is None:
            return 0

        return max(0, int((datetime.now() - latest_started_at).total_seconds()))

    def _other_running_timesheet(self) -> dict | None:
        for row in self.remote_timesheets:
            if row.get("status") != "running":
                continue
            if not self._is_current_timesheet(row):
                return row
        return None

    def _show_readonly_timesheet(self, row: dict) -> None:
        window = tk.Toplevel(self.root)
        window.title("Timesheet Details")
        window.geometry("760x560")
        window.minsize(680, 480)
        window.configure(bg=BG_COLOR)
        window.transient(self.root)

        wrapper = ttk.Frame(window, padding=18, style="App.TFrame")
        wrapper.pack(fill="both", expand=True)

        header = ttk.Frame(wrapper, padding=16, style="Surface.TFrame")
        header.pack(fill="x", pady=(0, 12))
        ttk.Label(header, text="Timesheet Details", style="SurfaceTitle.TLabel").pack(side="left")
        status = self._status_key(row)
        status_label, status_color, status_background = self._status_style(status)
        tk.Label(
            header,
            text=status_label,
            bg=status_background,
            fg=status_color,
            padx=12,
            pady=5,
            font=("Segoe UI", 9, "bold"),
        ).pack(side="right")

        details = ttk.Frame(wrapper, padding=16, style="Surface.TFrame")
        details.pack(fill="x", pady=(0, 12))
        for column in range(4):
            details.columnconfigure(column, weight=1)

        seconds = int(row.get("timer_elapsed_seconds") or 0)
        total_time = f"{int(seconds // 3600)} hr {int((seconds % 3600) // 60)} min {int(seconds % 60)} sec"
        fields = [
            ("Employee", self._row_employee_name(row)),
            ("Date", self._display_date(row.get("date"))),
            ("Project", self._row_project_name(row)),
            ("Task", self._row_task_title(row)),
            ("Start", self._display_time(row.get("start_time")) or "-"),
            ("End", self._resolved_end_time(row)),
            ("Hours", f'{float(row.get("hours_spent") or 0):g}'),
            ("Total Time", total_time),
            ("Billable", "Yes" if row.get("is_billable") else "No"),
        ]
        for index, (label, value) in enumerate(fields):
            self._readonly_field(details, label, value, index // 2, (index % 2) * 2)

        note_frame = ttk.Frame(wrapper, padding=16, style="Surface.TFrame")
        note_frame.pack(fill="x", pady=(0, 12))
        ttk.Label(note_frame, text="Description", style="Section.TLabel").pack(anchor="w")
        tk.Label(
            note_frame,
            text=row.get("description") or "-",
            bg=SURFACE_COLOR,
            fg=TEXT_COLOR,
            anchor="w",
            justify="left",
            wraplength=690,
            font=("Segoe UI", 10),
        ).pack(fill="x", pady=(6, 0))

        logs_frame = ttk.Frame(wrapper, padding=16, style="Surface.TFrame")
        logs_frame.pack(fill="both", expand=True)
        ttk.Label(logs_frame, text="Work Timer Log", style="Section.TLabel").pack(anchor="w", pady=(0, 8))
        log_tree = ttk.Treeview(logs_frame, columns=("action", "time", "elapsed", "total", "note"), show="headings", height=7)
        for column, width in {"action": 130, "time": 170, "elapsed": 100, "total": 100, "note": 220}.items():
            log_tree.heading(column, text=column.title(), anchor="center")
            log_tree.column(column, width=width, anchor="center")
        log_tree.pack(fill="both", expand=True)
        for log in row.get("timer_logs") or []:
            log_tree.insert(
                "",
                "end",
                values=(
                    log.get("action", ""),
                    log.get("time", ""),
                    log.get("elapsed", ""),
                    log.get("total", ""),
                    log.get("note", ""),
                ),
            )

        ttk.Button(wrapper, text="Close", style="Ghost.TButton", command=window.destroy).pack(anchor="e", pady=(12, 0))

    def _readonly_field(self, parent: ttk.Frame, label: str, value: str, row: int, column: int) -> None:
        ttk.Label(parent, text=label, style="Section.TLabel").grid(row=row * 2, column=column, sticky="w", padx=(0, 12), pady=(0, 3))
        tk.Label(
            parent,
            text=value or "-",
            bg=SURFACE_COLOR,
            fg=TEXT_COLOR,
            anchor="w",
            font=("Segoe UI", 10),
        ).grid(row=row * 2 + 1, column=column, columnspan=2, sticky="ew", padx=(0, 18), pady=(0, 10))

    def _display_date(self, value: str | None) -> str:
        if not value:
            return date.today().strftime("%d/%m/%Y")
        try:
            return datetime.strptime(value, "%Y-%m-%d").strftime("%d/%m/%Y")
        except ValueError:
            return value

    def _display_time(self, value: str | None) -> str:
        if not value:
            return ""
        for fmt in ("%H:%M:%S", "%H:%M"):
            try:
                return datetime.strptime(value, fmt).strftime("%I:%M %p")
            except ValueError:
                pass
        return value

    def _resolved_end_time(self, row: dict) -> str:
        existing = self._display_time(row.get("end_time"))
        if existing:
            return existing

        latest_log_time = self._latest_timer_log_time(row.get("timer_logs") or [])
        if latest_log_time:
            return latest_log_time.strftime("%I:%M %p")

        start_time = row.get("start_time")
        seconds = int(row.get("timer_elapsed_seconds") or 0)
        if not start_time or seconds <= 0:
            return "-"

        for fmt in ("%H:%M:%S", "%H:%M", "%I:%M %p"):
            try:
                started_at = datetime.strptime(start_time, fmt)
                ended_at = started_at + timedelta(seconds=seconds)
                return ended_at.strftime("%I:%M %p")
            except ValueError:
                pass

        return "-"

    def _latest_timer_log_time(self, logs: list[dict]) -> datetime | None:
        latest: datetime | None = None
        for log in logs:
            logged_at = log.get("time")
            if not logged_at:
                continue
            try:
                parsed = datetime.strptime(logged_at, "%d/%m/%Y %I:%M:%S %p")
            except ValueError:
                continue
            if latest is None or parsed > latest:
                latest = parsed
        return latest

    def _row_employee_name(self, row: dict) -> str:
        return self.remote_user.get("employee", {}).get("name", "") if self.remote_user else ""

    def _row_project_name(self, row: dict) -> str:
        project = row.get("project")
        if isinstance(project, dict):
            return project.get("name") or ""
        return str(project or "")

    def _row_task_title(self, row: dict) -> str:
        task = row.get("task")
        if isinstance(task, dict):
            return task.get("title") or ""
        return str(task or "")

    def _status_key(self, row: dict) -> str:
        return str(row.get("status") or "unknown").strip().lower()

    def _status_style(self, status: str) -> tuple[str, str, str]:
        if status in STATUS_STYLES:
            return STATUS_STYLES[status]
        return (status.replace("_", " ").title() if status else "Unknown", MUTED_COLOR, "#F2F4F7")

    def _ensure_status_badge_images(self) -> None:
        if self.status_badge_images:
            return
        for status in [*STATUS_STYLES, "unknown"]:
            _label, color, background = self._status_style(status)
            image = tk.PhotoImage(width=22, height=14)
            image.put(background, to=(0, 0, 22, 14))
            image.put(color, to=(2, 2, 20, 12))
            image.put(background, to=(0, 0, 2, 2))
            image.put(background, to=(20, 0, 22, 2))
            image.put(background, to=(0, 12, 2, 14))
            image.put(background, to=(20, 12, 22, 14))
            self.status_badge_images[status] = image

    def _set_delete_button_state(self) -> None:
        if hasattr(self, "delete_button"):
            self.delete_button.configure(state="normal" if self.current_timesheet_id else "disabled")

    def _clear_form(self) -> None:
        if self.timer_running:
            self._stop_timer_by_button()
        self.date_var.set(date.today().strftime("%d/%m/%Y"))
        self.current_timesheet_id = None
        self.current_desktop_uuid = None
        self.form_title_var.set("Create New Timesheet")
        self._set_delete_button_state()
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
        self._clear_action_logs()

    def _clear_action_logs(self) -> None:
        for item in self.action_tree.get_children():
            self.action_tree.delete(item)
