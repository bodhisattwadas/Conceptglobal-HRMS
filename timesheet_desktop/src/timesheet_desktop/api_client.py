from __future__ import annotations

from dataclasses import dataclass
import json
import socket
from typing import Any
import uuid
from urllib.error import HTTPError, URLError
from urllib.request import Request, urlopen


class ApiError(RuntimeError):
    pass


@dataclass
class ApiSession:
    token: str
    user: dict[str, Any]
    settings: dict[str, Any]


class LaravelTimesheetApi:
    def __init__(self, base_url: str) -> None:
        self.base_url = base_url.rstrip("/")
        self.session: ApiSession | None = None

    def login(self, email: str, password: str) -> ApiSession:
        response = self._request(
            "POST",
            "/api/desktop/login",
            {"email": email, "password": password, "machine": machine_identity()},
            authenticated=False,
        )
        self.session = ApiSession(token=response["token"], user=response["user"], settings=response.get("settings", {}))
        return self.session

    def bootstrap(self) -> dict[str, Any]:
        return self._request("GET", "/api/desktop/bootstrap")

    def timesheets(self) -> list[dict[str, Any]]:
        return self._request("GET", "/api/desktop/timesheets").get("timesheets", [])

    def create_timesheet(self, payload: dict[str, Any]) -> dict[str, Any]:
        payload = {**payload, "machine": machine_identity()}
        return self._request("POST", "/api/desktop/timesheets", payload).get("timesheet", {})

    def delete_timesheet(self, timesheet_id: int) -> None:
        self._request("DELETE", f"/api/desktop/timesheets/{timesheet_id}")

    def _request(
        self,
        method: str,
        path: str,
        payload: dict[str, Any] | None = None,
        *,
        authenticated: bool = True,
    ) -> dict[str, Any]:
        body = None
        headers = {"Accept": "application/json"}
        if payload is not None:
            body = json.dumps(payload).encode("utf-8")
            headers["Content-Type"] = "application/json"
        if authenticated:
            if not self.session:
                raise ApiError("Please login first.")
            headers["Authorization"] = f"Bearer {self.session.token}"

        request = Request(f"{self.base_url}{path}", data=body, headers=headers, method=method)

        try:
            with urlopen(request, timeout=20) as response:
                return json.loads(response.read().decode("utf-8"))
        except HTTPError as exc:
            message = exc.reason
            try:
                error_body = json.loads(exc.read().decode("utf-8"))
                message = error_body.get("message") or message
                if "errors" in error_body:
                    first_errors = next(iter(error_body["errors"].values()), [])
                    if first_errors:
                        message = first_errors[0]
            except Exception:
                pass
            raise ApiError(message) from exc
        except URLError as exc:
            raise ApiError(f"Could not connect to Laravel API: {exc.reason}") from exc


def machine_identity() -> dict[str, str | None]:
    return {
        "ip": local_ip_address(),
        "mac": local_mac_address(),
    }


def local_ip_address() -> str | None:
    sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    try:
        sock.connect(("8.8.8.8", 80))
        return sock.getsockname()[0]
    except OSError:
        try:
            return socket.gethostbyname(socket.gethostname())
        except OSError:
            return None
    finally:
        sock.close()


def local_mac_address() -> str | None:
    node = uuid.getnode()
    if (node >> 40) % 2:
        return None
    return ":".join(f"{(node >> shift) & 0xFF:02X}" for shift in range(40, -1, -8))
