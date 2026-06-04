# Timesheet Desktop

Standalone Python desktop application for project/task-based timesheets.

This first build uses only the Python standard library:

- Tkinter for the desktop UI
- SQLite for local storage
- `unittest` for tests

It is intentionally dependency-light so the app can run immediately inside this repository. A later UI pass can move the same service/database layer to PySide6.

## Run

```bash
cd timesheet_desktop
python run.py
```

The app starts with a Laravel login screen and syncs timesheets through:

```text
http://127.0.0.1:8000/api/desktop
```

Run the Laravel app and make sure migrations are applied before logging in:

```bash
php artisan migrate
php artisan db:seed
php artisan serve
```

Test login credentials:

```text
Laravel URL: http://127.0.0.1:8000
Email: admin@horilla.test
Password: password
```

Timesheet workflow:

- `Save Draft` saves the entry to Laravel as `draft`.
- Clicking a draft row in `All Timesheets` reopens it in `Create New` so work can resume.
- `Save and Submit Final` saves the entry to Laravel as `submitted`.

## Run Tests

```bash
cd timesheet_desktop
python -B -m unittest discover -s tests
```

## Build EXE Later

```bash
pip install pyinstaller
pyinstaller packaging/pyinstaller.spec --clean --noconfirm
```
