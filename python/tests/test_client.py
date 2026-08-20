# © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.
"""Unit tests for the SRO HTTP client (mocked urllib)."""

from __future__ import annotations

import json
import urllib.error
from io import BytesIO
from unittest.mock import patch

import pytest

from databoost.sro import Client, Error, SequenceRow


class _FakeResponse:
    def __init__(self, body: dict, status: int = 200) -> None:
        self._raw = json.dumps(body).encode("utf-8")
        self.status = status

    def read(self) -> bytes:
        return self._raw

    def __enter__(self) -> _FakeResponse:
        return self

    def __exit__(self, *args: object) -> None:
        return None


def _client() -> Client:
    return Client(
        base_url="https://sro.example.test",
        api_token="token",
        tenant_id="demo",
    )


def test_list_parses_sequence_rows() -> None:
    client = _client()
    fake = _FakeResponse(
        {
            "items": [
                {"id": "a", "sequence": 1, "sticky": False},
                {"id": "b", "sequence": 2, "sticky": True},
            ],
            "version": 3,
        }
    )
    with patch("urllib.request.urlopen", return_value=fake) as urlopen:
        rows = client.list("bindery")

    assert rows == [
        SequenceRow(id="a", sequence=1, sticky=False),
        SequenceRow(id="b", sequence=2, sticky=True),
    ]
    req = urlopen.call_args.args[0]
    assert req.full_url == "https://sro.example.test/v1/tenants/demo/lists/bindery"
    assert req.get_method() == "GET"
    assert req.get_header("Authorization") == "Bearer token"
    assert req.get_header("X-tenant-id") == "demo"


def test_sync_natural_posts_items() -> None:
    client = _client()
    fake = _FakeResponse({"items": [{"id": "a", "sequence": 1, "sticky": False}], "version": 1})
    with patch("urllib.request.urlopen", return_value=fake) as urlopen:
        rows = client.sync_natural(
            "bindery",
            [{"id": "a", "sort_key": "2026-08-01", "sort_data_type": "date"}],
        )

    assert rows == [SequenceRow(id="a", sequence=1, sticky=False)]
    req = urlopen.call_args.args[0]
    assert req.get_method() == "POST"
    assert req.full_url.endswith("/syncNatural")
    assert json.loads(req.data.decode("utf-8")) == {
        "items": [
            {"id": "a", "sort_key": "2026-08-01", "sort_data_type": "date"},
        ]
    }


def test_reset_sticky_and_reset_stickies() -> None:
    client = _client()
    fake = _FakeResponse({"items": [], "version": 1})
    with patch("urllib.request.urlopen", return_value=fake) as urlopen:
        client.reset_sticky("bindery", "job-1")
        client.reset_stickies("bindery")

    first, second = urlopen.call_args_list
    assert first.args[0].full_url.endswith("/resetSticky")
    assert json.loads(first.args[0].data.decode("utf-8")) == {"item_id": "job-1"}
    assert second.args[0].full_url.endswith("/resetStickies")
    assert second.args[0].data is None


def test_missing_sticky_defaults_false_and_ignores_major_minor() -> None:
    client = _client()
    fake = _FakeResponse(
        {"items": [{"id": "a", "sequence": 1, "major_minor": "5.1"}], "version": 1}
    )
    with patch("urllib.request.urlopen", return_value=fake):
        rows = client.list("bindery")

    assert rows == [SequenceRow(id="a", sequence=1, sticky=False)]
    assert not hasattr(rows[0], "major_minor")


def test_http_error_raises_message() -> None:
    client = _client()
    body = json.dumps({"error": {"code": "not_found", "message": "List missing"}}).encode()
    err = urllib.error.HTTPError(
        url="https://sro.example.test/x",
        code=404,
        msg="Not Found",
        hdrs=None,
        fp=BytesIO(body),
    )
    with patch("urllib.request.urlopen", side_effect=err):
        with pytest.raises(Error, match="List missing"):
            client.remove("bindery", "a")
