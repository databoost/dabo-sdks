# DataBoost SDKs (`dabo-sdks`)

Thin language SDKs for DataBoost HTTP APIs. Today: **Sticky Relative Order (SRO)**.

These packages **only call the API**. They do **not** embed ranking logic (natural spine, sticky overrides, reheal) — that lives in the SRO service.

| Path | Package | Language |
|------|---------|----------|
| [`php/sro/`](php/sro/) | `databoost/sro` | PHP 8.2+ |
| [`ruby/`](ruby/) | `databoost-sro` | Ruby 3.1+ |
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

// Dense production sequence 1…n
foreach ($client->list('bindery') as $row) {
    // $row->id, $row->sequence
}

$client->jump('bindery', '102', 1);
$client->reorder('bindery', '101', afterItemId: null); // move to first
$client->remove('bindery', '101');
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
```

See [`ruby/README.md`](ruby/README.md).

## API surface

| Method | Purpose |
|--------|---------|
| `syncNatural(listId, items)` | Push `{id, sort_key, sort_data_type}`; service assigns the natural spine |
| `list(listId)` | Current order as `{id, sequence}` (1…n) |
| `jump(listId, itemId, toSequence)` | Move an item to a display slot |
| `reorder(listId, itemId, afterItemId)` | Place after a neighbor (`null` = first) |
| `remove(listId, itemId)` | Drop an item; service compacts |

Auth: `Authorization: Bearer <token>` + `X-Tenant-Id: <tenant>` (must match the path `/v1/tenants/{tenant}/…`).

Responses contain dense sequences only — never internal ranking keys.

## Adding a language / another PHP SDK

New clients (Python, JS/TS, Go…) and additional PHP packages (`php/<product>/`) belong here. Match the method names above and return `{id, sequence}`; keep all ranking on the service.

## License

Proprietary — see [NOTICE](NOTICE).
