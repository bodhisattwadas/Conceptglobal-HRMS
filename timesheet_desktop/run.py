from pathlib import Path
import sys


ROOT = Path(__file__).resolve().parent
SRC = ROOT / "src"
sys.path.insert(0, str(SRC))

from timesheet_desktop.main import main


if __name__ == "__main__":
    main()

