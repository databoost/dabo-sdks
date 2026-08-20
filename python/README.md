# DataBoost SRO — Python client

Thin client for the SRO HTTP API. Same contract as the PHP `databoost/sro` and Ruby `databoost-sro` packages; no ranking logic in-process.

Uses **stdlib only** (`urllib`). OpenAPI: [`../openapi/sro-v1.yaml`](../openapi/sro-v1.yaml).

## Install

```toml
# pyproject.toml / requirements — path
databoost-sro = { path = "../dabo-sdks/python", editable = true }
```

```toml
# git
databoost-sro = { git = "https://github.com/databoost/dabo-sdks.git", subdirectory = "python" }
```

Local editable:

```bash
pip install -e "./python[dev]"
```

## Usage

```python
import os
from databoost.sro import Client

client = Client(
    base_url=os.environ["SRO_BASE_URL"],  # e.g. https://sro.databoost.com
    api_token=os.environ["SRO_API_TOKEN"],
    tenant_id="demo",
)

client.sync_natural("bindery", [
    {"id": "a", "sort_key": "2026-08-01", "sort_data_type": "date"},
    {"id": "b", "sort_key": "2026-08-10", "sort_data_type": "date"},
])

rows = client.list("bindery")
# => [SequenceRow(id='a', sequence=1, sticky=False), ...]

client.jump("bindery", "b", 1)
client.reorder("bindery", "a", "b")  # place a after b
client.remove("bindery", "a")
client.reset_sticky("bindery", "b")  # clear one sticky overlay
client.reset_stickies("bindery")     # clear all overlays on this list
```

Method names match the Ruby façade (`sync_natural`, `reset_sticky`, …). PHP uses camelCase for the same operations.

## Tests

```bash
cd python && pip install -e ".[dev]" && pytest
```
