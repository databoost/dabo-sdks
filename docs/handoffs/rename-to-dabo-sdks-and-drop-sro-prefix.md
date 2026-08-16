# Handoff: Rename clients repo to `dabo-sdks`, drop `Sro` from PHP class names, target AWS API

**Repo:** `databoost/dabo-sdks` (renamed from `sro-clients`)
**Path:** `/Users/brad/misc/github/databoost/dabo-sdks`
**Date:** 2026-08-08 (executed 2026-08-09)
**Audience:** New agent — do not assume prior chat access except this doc + linked files.

> **Plan — complete.** Steps 1–7 done. This repo is on `main` @ `839151b` (pushed). Sibling
> cutovers landed in `lpp-console` and `dabo-sro`.

## Preflight — where are we?

Do not trust any step to be undone. Detect state, then skip completed steps.

```bash
basename "$PWD"; git remote -v | head -1; git status -sb; ls src php 2>/dev/null
```

| Observed | Meaning | Resume at |
|---|---|---|
| dir `sro-clients`, remote `…/sro-clients.git` | nothing done | step 1 |
| dir `dabo-sdks` | repo rename done | step 3 |
| `php/sro/composer.json` or `php/Sro/` exists | layout done | step 4 |
| `src/Client.php` (and no `SroClient.php`) | class rename done | step 5 |
| `README.md` shows `sro.databoost.com` | endpoint done | step 6 |

Never re-run a rename whose end state already holds. Tick the boxes under **Next steps** as you go.

## Goal

1. Rename the repo to a **product-neutral** name, **`dabo-sdks`**, so future SDKs live here without
   creating another public repo. (User said "dabo-clients"; the actual repo is **`sro-clients`** —
   same intent.)
2. Restructure so **multiple** PHP SDKs can coexist (see the Shape A / Shape B decision).
3. Remove the redundant **`Sro`** from PHP class names — the namespace `Databoost\Sro\` already
   says it.
4. Point the PHP client's **default base URL** at the live AWS API `https://sro.databoost.com`
   instead of `http://127.0.0.1:8080`.

Sibling rename happening at the same time: the service repo `dabo-scheduler` becomes **`dabo-sro`**.

## Current state (after 2026-08-09)

Repo renamed on GitHub + locally. Shape B + class rename **committed and pushed** (`839151b`).
Sibling cutovers complete (`lpp-console` on `databoost/sro`; service repo is `dabo-sro`).

| Thing | Value |
|---|---|
| Remote | `git@github.com:databoost/dabo-sdks.git` |
| Tip | `839151b` on `main` = `origin/main` |
| PHP package | `php/sro/` → `databoost/sro`, PSR-4 `Databoost\Sro\` → `src/` |
| PHP classes | `Client`, `Engine`, `HttpEngine`, `SequenceRow` |
| Ruby | unchanged module; homepage URLs → `dabo-sdks` |
| Contract | `openapi/sro-v1.yaml` primary server `https://sro.databoost.com` |
| Install | path `../dabo-sdks/php/*` (no VCS until split CI) |

The clients contain **no ranking logic**; the engine lives in the service repo
(`dabo-scheduler/service/src/Engine/`, becoming `dabo-sro/service/src/Engine/`).

**Live API** (from `dabo-scheduler/docs/DEPLOY-AWS.md`): `https://sro.databoost.com`, verified
end to end — `/health` returns `{"status":"ok"}`, bad token → `401`, `SRO_ENV=production` so
responses carry no `major_minor`. Auth is `Authorization: Bearer <token>` + `X-Tenant-Id`.

## Decisions made

- **Shape B** (package per SDK under `php/sro/`; defer subtree-split CI). Path install for
  consumers: `../dabo-sdks/php/*`.
- **Base URL required** — no localhost or production default in client code. Document
  `https://sro.databoost.com` in README / OpenAPI `servers` only.
- Repo name: **`dabo-sdks`** — not `dabo-libs`, `dabo-public`, `openDABO`, or a bare `sdks`.
  - `openDABO` was rejected: both `composer.json` files declare `"license": "proprietary"` and
    `dabo-scheduler/docs/IP.md` documents a trade-secret posture — "open" would misrepresent it.
  - `dabo-public` names a GitHub visibility toggle, not contents.
  - **Keep the `dabo-` prefix.** It is DataBoost branding, it sorts all commercial repos together,
    and it separates our work from forked/third-party checkouts (`reportico`, `reportico-pr-*`,
    `iRedAdmin-Pro-SQL`, …) sitting in the same tree. The `databoost/dabo-sdks` "stutter" is
    cosmetic and appears only in the URL — the prefix never shows up in what developers type,
    and `git clone` lands a branded `dabo-sdks/` directory in any tree. Consistent with
    `dabo-tools`, `dabo-fleet`, `dabo-console`, `dabo-sro`.
- "SDK" = Software Development Kit; accepted as the umbrella word even though these are thin
  HTTP clients, matching common vendor usage (Stripe/AWS "PHP SDK").
- Package name stays **`databoost/sro`**; namespace stays **`Databoost\Sro\`**. Both are declared
  in the manifest and are independent of the repo name.
- Ruby client needs **no** renaming — `Databoost::Sro::Client` is already correct.

## Files changed/created

Done in this repo (uncommitted unless user asked):

- Removed root `composer.json` / `src/` / `tests/` / `phpunit.xml`
- [`php/sro/composer.json`](../../php/sro/composer.json) — package `databoost/sro`, homepage → `dabo-sdks`
- [`php/sro/src/{Client,Engine,HttpEngine,SequenceRow}.php`](../../php/sro/src/)
- [`php/sro/tests/ClientHttpFactoryTest.php`](../../php/sro/tests/ClientHttpFactoryTest.php)
- [`README.md`](../../README.md) — path install, required `SRO_BASE_URL`, class names
- [`openapi/sro-v1.yaml`](../../openapi/sro-v1.yaml) — primary server `https://sro.databoost.com`
- Ruby homepage / README install URL → `dabo-sdks`
- Sibling (untracked): `dabo-scheduler/clients/README.md`

### Class rename map (applied)

| Was | Now |
|---|---|
| `SroClient` + `StatefulSroClient` | `Client` (`::http` + façade) |
| `StatefulSroEngine` | `Engine` |
| `HttpSroEngine` | `HttpEngine` |
| `SequenceRow` | `SequenceRow` |

Result: `Databoost\Sro\Client::http($baseUrl, $token, $tenantId)` returns `Client`.
`new Client($engine)` keeps the test seam for `lpp-console`.

## Commands/tests run

```bash
# php/sro — PHPUnit 11.5.56 / PHP 8.5.4
composer test   # OK (1 test, 1 assertion)
```

Previously verified (live API):

```bash
curl -s https://sro.databoost.com/health          # {"status":"ok"}
```

## Known issues / risks

1. **Renaming the repo forces a restructure — it is not cosmetic.** The root `composer.json`
   currently *is* `databoost/sro`. A repo named `dabo-sdks` whose root package is the SRO client is
   incoherent, and more practically the root manifest is the **only** one a Composer VCS
   repository can see, so a second PHP SDK could never be installed. Pick one:

   - **Shape A — umbrella package.** Root becomes `databoost/sdk` with
     `"autoload": {"psr-4": {"Databoost\\": "php/"}}`; SRO moves to `php/Sro/`. One tag ships
     everything. Simple, no infrastructure. Cost: version coupling (an SRO bump churns every
     consumer) and shared dependencies.
   - **Shape B — package per SDK.** No root manifest; `php/sro/{composer.json,src,tests}`.
     Per-package versioning and requires. Cost: **network install needs CI** that
     `git subtree split`s each `php/<product>/` into a read-only mirror repo (`splitsh/lite` or
     `symplify/monorepo-builder`), or paid Private Packagist, which reads subdirectory manifests
     directly. Satis does **not** help — same one-package-per-repo limit.

   **Recommendation: Shape B, defer the splitting CI.** The only consumer today is a sibling
   checkout, and Composer path repositories accept wildcards, so `lpp-console` can use
   `{"type": "path", "url": "../dabo-sdks/php/*"}` and get per-package requires immediately. Add
   splitting when an off-machine consumer appears.

2. **Shape B breaks the currently documented VCS install** (`README.md` shows
   `composer require databoost/sro:^0.1` from a VCS repository). Acceptable only because there
   are no external consumers yet — but the README must stop advertising it until splitting exists.

3. **Class renames are a breaking API change** for `lpp-console`, which imports
   `StatefulSroClient` in `app/Providers/AppServiceProvider.php` and
   `app/Services/ProductionSchedule/JobSroReorderService.php`, and uses both `SroClient::http`
   and `new StatefulSroClient($engine)` in `tests/Unit/JobSroStatefulClientTest.php`. Coordinate
   with the `lpp-console` handoff — do not land one side alone.

4. **Resolved:** base URL is required (no code default). Document AWS in README/OpenAPI only.

5. Talking to a remote API over the internet (rather than a LAN sidecar) makes the client's
   fixed 30s timeout and total lack of retry/backoff more consequential. Out of scope here, but
   worth a follow-up.

6. `.gitignore` exists but there is no `docs/` tree yet — this file creates `docs/handoffs/`.

7. **Three repos rename together** (`sro-clients` → `dabo-sdks`, `dabo-scheduler` → `dabo-sro`,
   plus lpp-console's path repo pointing at both). Relative path repositories break the moment a
   sibling directory is renamed. Do all three in one sitting.

## Do not do

- Do **not** put ranking logic (natural spine, sticky keys, reheal) in these clients. The engine
  stays in the service repo.
- Do **not** rename the Ruby gem or its module — `databoost-sro` / `Databoost::Sro` are correct.
- Do **not** expose `major_minor` or sticky keys in any client surface.
- Do **not** rename `databoost/sro` to something else while renaming the repo; package identity
  and repo identity are deliberately separate.
- Do **not** drop the `dabo-` prefix — it is a deliberate branding/sorting decision.
- Do **not** commit or push without the user asking.

## Next steps

- [x] **1.** Shape B + required base URL (user accepted recommendation 2026-08-09).
- [x] **2.** Renamed GitHub `databoost/sro-clients` → `databoost/dabo-sdks`; local dir + remote URL.
- [x] **3.** Shape B layout: `php/sro/{composer.json,src,tests,phpunit.xml}`; no root manifest.
- [x] **4.** Class rename map applied (`Client` / `Engine` / `HttpEngine`); README + tests updated.
- [x] **5.** Documented `https://sro.databoost.com` (OpenAPI primary server + README); no code default.
- [x] **6.** Updated service `clients/README.md` (now under `dabo-sro`; points at `dabo-sdks`).
- [x] **7.** Coordinated siblings (verified 2026-08-09):
      - `lpp-console` cutover complete — path `../dabo-sdks/php/*`, `databoost/sro`,
        `Databoost\Sro\{Client,Engine,SequenceRow}`, `SRO_BASE_URL=https://sro.databoost.com`
        (see `lpp-console/docs/handoffs/sro-package-cutover-databoost-sro.md`, steps 1–8).
      - Service repo renamed `dabo-scheduler` → `dabo-sro` (GitHub + local);
        legacy root `databoost/scheduler` package removed.

## Useful references

- [`php/sro/composer.json`](../../php/sro/composer.json) · [`README.md`](../../README.md) · [`openapi/sro-v1.yaml`](../../openapi/sro-v1.yaml)
- PHP: [`php/sro/src/Client.php`](../../php/sro/src/Client.php) · [`Engine.php`](../../php/sro/src/Engine.php) · [`HttpEngine.php`](../../php/sro/src/HttpEngine.php) · [`SequenceRow.php`](../../php/sro/src/SequenceRow.php)
- Ruby client: [`ruby/README.md`](../../ruby/README.md)
- Service + AWS (`dabo-sro`): `/Users/brad/misc/github/databoost/dabo-sro/docs/DEPLOY-AWS.md` · `.../service/README.md`
- Sibling handoffs: `dabo-sro/docs/handoffs/rename-repo-to-dabo-sro.md` · `lpp-console/docs/handoffs/sro-package-cutover-databoost-sro.md`
- Live API: <https://sro.databoost.com>
