# © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.
"""Thin HTTP client. Method names mirror the Ruby client / OpenAPI (snake_case)."""

from __future__ import annotations

import json
import urllib.error
import urllib.parse
import urllib.request
from dataclasses import dataclass
from typing import Any, Mapping, Sequence

from databoost.sro._errors import Error


@dataclass(frozen=True, slots=True)
class SequenceRow:
    id: str
    sequence: int
    sticky: bool = False


class Client:
    """Stateful Sticky Relative Order client — dense 1…n sequences plus sticky flag."""

    def __init__(self, *, base_url: str, api_token: str, tenant_id: str) -> None:
        self._base_url = base_url.rstrip("/")
        self._api_token = api_token
        self._tenant_id = tenant_id

    def health(self) -> dict[str, str]:
        data = self._json("GET", "/health", None, auth=False)
        return {"status": str(data.get("status") or "")}

    def sync_natural(
        self,
        list_id: str,
        items: Sequence[Mapping[str, Any]],
        expected_version: int | None = None,
    ) -> list[SequenceRow]:
        payload: dict[str, Any] = {
            "items": [self._normalize_sync_item(item) for item in items]
        }
        if expected_version is not None:
            payload["expected_version"] = expected_version
        return self._request("POST", self._list_path(list_id, "syncNatural"), payload)

    def list(self, list_id: str) -> list[SequenceRow]:
        return self._request("GET", self._list_path(list_id), None)

    def jump(
        self,
        list_id: str,
        item_id: str,
        to_sequence: int,
        expected_version: int | None = None,
    ) -> list[SequenceRow]:
        payload: dict[str, Any] = {
            "item_id": item_id,
            "to_sequence": to_sequence,
        }
        if expected_version is not None:
            payload["expected_version"] = expected_version
        return self._request("POST", self._list_path(list_id, "jump"), payload)

    def reorder(
        self,
        list_id: str,
        item_id: str,
        after_item_id: str | None,
        before_item_id: str | None = None,
        expected_version: int | None = None,
    ) -> list[SequenceRow]:
        payload: dict[str, Any] = {"item_id": item_id}
        if before_item_id is not None:
            payload["before_item_id"] = before_item_id
        else:
            payload["after_item_id"] = after_item_id
        if expected_version is not None:
            payload["expected_version"] = expected_version
        return self._request("POST", self._list_path(list_id, "reorder"), payload)

    def remove(
        self,
        list_id: str,
        item_id: str,
        expected_version: int | None = None,
    ) -> list[SequenceRow]:
        payload: dict[str, Any] = {"item_id": item_id}
        if expected_version is not None:
            payload["expected_version"] = expected_version
        return self._request("POST", self._list_path(list_id, "remove"), payload)

    def reset_sticky(
        self,
        list_id: str,
        item_id: str,
        expected_version: int | None = None,
    ) -> list[SequenceRow]:
        payload: dict[str, Any] = {"item_id": item_id}
        if expected_version is not None:
            payload["expected_version"] = expected_version
        return self._request("POST", self._list_path(list_id, "resetSticky"), payload)

    def reset_stickies(
        self,
        list_id: str,
        expected_version: int | None = None,
    ) -> list[SequenceRow]:
        payload = None if expected_version is None else {"expected_version": expected_version}
        return self._request("POST", self._list_path(list_id, "resetStickies"), payload)

    def _normalize_sync_item(self, item: Mapping[str, Any]) -> dict[str, Any]:
        if "id" not in item:
            raise Error("sync_natural items must include id")
        return {
            "id": str(item["id"]),
            "sort_key": item.get("sort_key"),
            "sort_data_type": item.get("sort_data_type"),
        }

    def _list_path(self, list_id: str, action: str | None = None) -> str:
        tenant = urllib.parse.quote(self._tenant_id, safe="")
        list_enc = urllib.parse.quote(list_id, safe="")
        path = f"/v1/tenants/{tenant}/lists/{list_enc}"
        return f"{path}/{action}" if action else path

    def _request(
        self,
        method: str,
        path: str,
        body: Mapping[str, Any] | None,
    ) -> list[SequenceRow]:
        data_obj = self._json(method, path, body, auth=True)
        items = data_obj.get("items")
        if not isinstance(items, list):
            raise Error("SRO HTTP response missing items")

        rows: list[SequenceRow] = []
        for row in items:
            if not isinstance(row, dict) or "id" not in row or "sequence" not in row:
                continue
            rows.append(
                SequenceRow(
                    id=str(row["id"]),
                    sequence=int(row["sequence"]),
                    sticky=row.get("sticky") is True,
                )
            )
        return rows

    def _json(
        self,
        method: str,
        path: str,
        body: Mapping[str, Any] | None,
        *,
        auth: bool,
    ) -> dict[str, Any]:
        url = f"{self._base_url}{path}"
        headers = {"Accept": "application/json"}
        if auth:
            headers["Authorization"] = f"Bearer {self._api_token}"
            headers["X-Tenant-Id"] = self._tenant_id
        data: bytes | None = None
        if body is not None:
            data = json.dumps(body).encode("utf-8")
            headers["Content-Type"] = "application/json"

        req = urllib.request.Request(url, data=data, headers=headers, method=method)
        try:
            with urllib.request.urlopen(req, timeout=30) as res:
                raw = res.read().decode("utf-8")
                status = getattr(res, "status", 200)
        except urllib.error.HTTPError as exc:
            raw = exc.read().decode("utf-8", errors="replace")
            status = exc.code
            data_obj = self._parse_json(raw)
            message = (
                data_obj.get("error", {}).get("message")
                if isinstance(data_obj.get("error"), dict)
                else None
            )
            raise Error(message or f"SRO HTTP {status}") from exc
        except urllib.error.URLError as exc:
            raise Error("SRO HTTP request failed") from exc

        if status >= 400:
            data_obj = self._parse_json(raw)
            message = (
                data_obj.get("error", {}).get("message")
                if isinstance(data_obj.get("error"), dict)
                else None
            )
            raise Error(message or f"SRO HTTP {status}")

        data_obj = self._parse_json(raw)
        if "error" in data_obj and isinstance(data_obj["error"], dict):
            raise Error(str(data_obj["error"].get("message") or "SRO HTTP error"))
        return data_obj

    @staticmethod
    def _parse_json(raw: str) -> dict[str, Any]:
        try:
            parsed = json.loads(raw)
        except json.JSONDecodeError as exc:
            raise Error("SRO HTTP response was not JSON") from exc
        if not isinstance(parsed, dict):
            raise Error("SRO HTTP response was not a JSON object")
        return parsed
