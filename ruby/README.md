# DataBoost SRO — Ruby client

Thin client for the SRO HTTP API. Same contract as the PHP `databoost/sro` package; no ranking logic in-process.

OpenAPI: [`../openapi/sro-v1.yaml`](../openapi/sro-v1.yaml).

## Install

```ruby
# Gemfile
gem 'databoost-sro', git: 'https://github.com/databoost/dabo-sdks.git', glob: 'ruby/*.gemspec'
```

Local checkout:

```ruby
gem 'databoost-sro', path: 'ruby'
```

## Usage

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
# => [#<struct id="a", sequence=1, sticky=false>, ...]

client.jump('bindery', 'b', 1)
client.reorder('bindery', 'a', 'b')  # place a after b
client.remove('bindery', 'a')
client.reset_sticky('bindery', 'b')
client.reset_stickies('bindery')
```

Method names mirror the PHP façade (`syncNatural` → `sync_natural`).
