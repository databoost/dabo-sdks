# DataBoost SDKs (`dabo-sdks`)

Thin language SDKs for DataBoost HTTP APIs. Today: **Sticky Relative Order (SRO)**.

These packages **only call the API**. They do **not** embed ranking logic (natural spine, sticky overrides, reheal) — that lives in the SRO service.

| Path | Package | Language |
|------|---------|----------|
| [`php/sro/`](php/sro/) | `databoost/sro` | PHP 8.2+ |
| [`ruby/`](ruby/) | `databoost-sro` | Ruby 3.1+ |
| [`typescript/`](typescript/) | `databoost-sro` | TypeScript (Node 18+) |
| [`openapi/sro-v1.yaml`](openapi/sro-v1.yaml) | API contract | — |

Live SRO API: <https://sro.databoost.com>

## PHP (`databoost/sro`)

Package lives under [`php/sro/`](php/sro/) (Shape B — one Composer package per SDK). There is **no** root `composer.json`.

**Sibling / path install** (recommended until package-split CI exists):

```json
{
  "repositories": [
    { "type": "path", "url": "../dabo-sdks/php/*" }
  ],
  "require": { "databoost/sro": "@dev" }
}
```

VCS/`composer require databoost/sro` from this monorepo is **not** supported yet (Composer only reads a root manifest).

```php
use Databoost\Sro\Client;

// base URL is required — no localhost or production default
$client = Client::http(
    getenv('SRO_BASE_URL') ?: throw new RuntimeException('SRO_BASE_URL is required'),
    getenv('SRO_API_TOKEN') ?: '',
    getenv('SRO_TENANT_ID') ?: 'demo',
);
// production example: Client::http('https://sro.databoost.com', $token, 'lpp');

// Push natural sort signals — opaque ids only, no business payloads
$client->syncNatural('bindery', [
    ['id' => '101', 'sort_key' => '2026-08-01', 'sort_data_type' => 'date'],
    ['id' => '102', 'sort_key' => '2026-08-10', 'sort_data_type' => 'date'],
]);

// Dense production sequence 1…n plus sticky flag
foreach ($client->list('bindery') as $row) {
    // $row->id, $row->sequence, $row->sticky
}

$client->jump('bindery', '102', 1);
$client->reorder('bindery', '101', afterItemId: null); // move to first
$client->remove('bindery', '101');
$client->resetSticky('bindery', '102');   // clear one sticky overlay
$client->resetStickies('bindery');        // clear all overlays on this list
```

Namespace `Databoost\Sro`. `Client::http(...)` returns `Client`. Tests may inject a fake via `new Client($engine)` (`Engine` interface).

## Ruby

```ruby
# Gemfile
gem 'databoost-sro', git: 'https://github.com/databoost/dabo-sdks.git', glob: 'ruby/*.gemspec'
```

```ruby
require 'databoost/sro'

client = Databoost::Sro::Client.new(
  base_url: ENV.fetch('SRO_BASE_URL'), # e.g. https://sro.databoost.com
  api_token: ENV.fetch('SRO_API_TOKEN'),
  tenant_id: 'demo'
)

client.sync_natural('bindery', [
  { id: 'a', sort_key: '2026-08-01', sort_data_type: 'date' },
  { id: 'b', sort_key: '2026-08-10', sort_data_type: 'date' }
])

rows = client.list('bindery')
client.jump('bindery', 'b', 1)
client.reset_sticky('bindery', 'b')
client.reset_stickies('bindery')
```

See [`ruby/README.md`](ruby/README.md).

## TypeScript

```json
{
  "dependencies": {
    "databoost-sro": "file:../dabo-sdks/typescript"
  }
}
```

```ts
import { Client } from "databoost-sro";

const client = new Client({
  baseUrl: process.env.SRO_BASE_URL!, // e.g. https://sro.databoost.com
  apiToken: process.env.SRO_API_TOKEN!,
  tenantId: "demo",
});

await client.syncNatural("bindery", [
  { id: "a", sort_key: "2026-08-01", sort_data_type: "date" },
  { id: "b", sort_key: "2026-08-10", sort_data_type: "date" },
]);

const rows = await client.list("bindery");
await client.jump("bindery", "b", 1);
await client.resetSticky("bindery", "b");
await client.resetStickies("bindery");
```

Methods are **async** (native `fetch`). Names match PHP / OpenAPI camelCase.

See [`typescript/README.md`](typescript/README.md).

## API surface

| Method | Purpose |
|--------|---------|
| `syncNatural(listId, items)` | Push `{id, sort_key, sort_data_type}`; service assigns the natural spine |
| `list(listId)` | Current order as `{id, sequence, sticky}` (sequence 1…n) |
| `jump(listId, itemId, toSequence)` | Move an item to a display slot |
| `reorder(listId, itemId, afterItemId)` | Place after a neighbor (`null` = first) |
| `remove(listId, itemId)` | Drop an item; service compacts |
| `resetSticky(listId, itemId)` | Clear one item’s sticky overlay, then densify naturals |
| `resetStickies(listId)` | Clear every sticky overlay on that list, then densify naturals |

Auth: `Authorization: Bearer <token>` + `X-Tenant-Id: <tenant>` (must match the path `/v1/tenants/{tenant}/…`).

Responses contain `{id, sequence, sticky}` — never internal ranking keys. `sticky` is always present (`false` on naturals).

## Adding a language / another PHP SDK

New clients (Python, Go…) and additional PHP packages (`php/<product>/`) belong here. Match the method names above and return `{id, sequence, sticky}`; keep all ranking on the service.

## License

Proprietary — see [NOTICE](NOTICE).
