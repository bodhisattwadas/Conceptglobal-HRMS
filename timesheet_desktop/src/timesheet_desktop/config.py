from pathlib import Path
import os


APP_NAME = "Timesheet Desktop"
APP_VENDOR = "ConceptGlobal"


def project_root() -> Path:
    return Path(__file__).resolve().parents[2]


def data_dir() -> Path:
    configured = os.environ.get("TIMESHEET_DESKTOP_DATA_DIR")
    if configured:
        path = Path(configured)
    else:
        path = project_root() / "data"
    path.mkdir(parents=True, exist_ok=True)
    return path


def database_path() -> Path:
    configured = os.environ.get("TIMESHEET_DB_PATH")
    if configured:
        path = Path(configured)
        path.parent.mkdir(parents=True, exist_ok=True)
        return path
    return data_dir() / "timesheet.db"
