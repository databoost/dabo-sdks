# DataBoost SRO — TypeScript client

Thin client for the SRO HTTP API. Same contract as the PHP / Ruby / Python packages; no ranking logic in-process.

Uses **native `fetch`** (Node 18+ / browsers). All methods are **async**. OpenAPI: [`../openapi/sro-v1.yaml`](../openapi/sro-v1.yaml).

## Install

```bash
# sibling / path (recommended until npm publish exists)
npm install ../dabo-sdks/typescript
```

```json
{
  "dependencies": {
    "databoost-sro": "file:../dabo-sdks/typescript"
  }
}
```

pnpm/bun can install a git subdirectory; plain npm path/file install is the supported story for now (same constraint as Composer in this monorepo).

## Usage

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
// => [{ id: "a", sequence: 1 }, ...]

await client.jump("bindery", "b", 1);
await client.reorder("bindery", "a", "b"); // place a after b
await client.remove("bindery", "a");
await client.resetSticky("bindery", "b"); // clear one sticky, re-rank naturals
await client.resetStickies("bindery"); // clear all stickies, re-rank naturals
```

Method names match PHP / OpenAPI (`syncNatural`, `resetSticky`). Ruby/Python use snake_case for the same operations.

## Develop

```bash
cd typescript
npm install
npm test
npm run build
```
