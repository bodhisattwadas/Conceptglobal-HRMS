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

The app opens directly as a single-user timesheet tracker. There is no admin/login interface in this build.

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
