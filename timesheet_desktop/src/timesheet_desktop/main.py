from .database import initialize_database
from .ui.app import TimesheetDesktopApp


def main() -> None:
    initialize_database()
    app = TimesheetDesktopApp()
    app.run()

