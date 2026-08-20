# © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.
"""SRO admin HTTP client (SRO_ADMIN_TOKEN). No X-Tenant-Id."""

from __future__ import annotations

import json
import urllib.error
import urllib.parse
import urllib.request
from typing import Any, Mapping, Sequence

from databoost.sro._errors import Error


class AdminClient:
    """Console/service-to-service admin surface. Never log issued token plaintext."""

    def __init__(self, *, base_url: str, admin_token: str) -> None:
        self._base_url = base_url.rstrip("/")
        self._admin_token = admin_token

    def health(self) -> dict[str, str]:
        data = self._json("GET", "/health", None, auth=False)
        return {"status": str(data.get("status") or "")}

    def list_tenants(self) -> dict[str, Any]:
        return self._json("GET", "/admin/v1/tenants", None, auth=True)

    def reconcile_tenants(
        self,
        tenant_ids: Sequence[str],
        *,
        dry_run: bool = False,
        allow_empty: bool = False,
    ) -> dict[str, Any]:
        return self._json(
            "POST",
            "/admin/v1/tenants/reconcile",
            {
                "tenant_ids": list(tenant_ids),
                "dry_run": dry_run,
                "allow_empty": allow_empty,
            },
            auth=True,
        )

    def provision_tenant(self, tenant_id: str, body: Mapping[str, Any]) -> dict[str, Any]:
        return self._json(
            "PUT",
            f"/admin/v1/tenants/{urllib.parse.quote(tenant_id, safe='')}",
            dict(body),
            auth=True,
        )

    def update_tenant(self, tenant_id: str, body: Mapping[str, Any]) -> dict[str, Any]:
        return self._json(
            "PATCH",
            f"/admin/v1/tenants/{urllib.parse.quote(tenant_id, safe='')}",
            dict(body),
            auth=True,
        )

    def delete_tenant(self, tenant_id: str) -> dict[str, Any]:
        return self._json(
            "DELETE",
            f"/admin/v1/tenants/{urllib.parse.quote(tenant_id, safe='')}",
            None,
            auth=True,
        )

    def regenerate_token(
        self,
        tenant_id: str,
        token_label: str | None = None,
    ) -> dict[str, Any]:
        body = None if token_label is None else {"token_label": token_label}
        return self._json(
            "POST",
            f"/admin/v1/tenants/{urllib.parse.quote(tenant_id, safe='')}/token",
            body,
            auth=True,
        )

    def revoke_token(self, tenant_id: str) -> dict[str, Any]:
        return self._json(
            "DELETE",
            f"/admin/v1/tenants/{urllib.parse.quote(tenant_id, safe='')}/token",
            None,
            auth=True,
        )

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
            headers["Authorization"] = f"Bearer {self._admin_token}"
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
            parsed = json.loads(raw) if raw else {}
            message = None
            if isinstance(parsed, dict) and isinstance(parsed.get("error"), dict):
                message = parsed["error"].get("message")
            raise Error(message or f"SRO HTTP {exc.code}") from exc
        except urllib.error.URLError as exc:
            raise Error("SRO HTTP request failed") from exc
        if status >= 400:
            raise Error(f"SRO HTTP {status}")
        parsed = json.loads(raw) if raw else {}
        if not isinstance(parsed, dict):
            raise Error("SRO HTTP response was not a JSON object")
        return parsed
