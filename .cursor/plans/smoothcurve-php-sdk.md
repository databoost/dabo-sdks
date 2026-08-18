<!-- © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved. -->
# Plan: SMOCUR-SDK

**Goal:** Thin PHP client for SmoothCurve HTTP (`curve` / `map`) under `php/smooth-curve`, same Shape B layout as `php/sro` and `php/time`. Path-install ready for Carunch. No easing math in the SDK.

**Handoff:** [docs/handoffs/smoothcurve-php-sdk.md](../../docs/handoffs/smoothcurve-php-sdk.md)
**Campaign:** `/Users/brad/misc/github/databoost/dabo-tools/docs/campaigns/smoothcurve.md` (member 4)

## Execution protocol

Gated (step-plan skill). One step per **confirm**. Verbs: **confirm** | **pause** | **resume plan** | **status** | **ask** | **revert** | **stop**.

## Status

```text
Done: S5 — composer install + PHPUnit OK (2 tests, 7 assertions)
Run: SMOCUR-SDK | Target: dabo-sdks | Plan complete: yes | Gated: stopped
```

## Locked decisions

1. Folder `php/smooth-curve`; Composer `databoost/smooth-curve`; namespace `Databoost\SmoothCurve`.
2. Methods **curve** and **map** only. Body arrays match OpenAPI (`a,b,c` + optional `ease_mode`, `ease_exponent`, `clamp`; map also `g,h`). Returns decoded JSON (`t` / `value`).
3. Auth: `Authorization: Bearer` + `X-Tenant-Id` matching path `/v1/tenants/{tenant_id}/…`. `Client::http($baseUrl, $apiToken, $tenantId)` — no default URL.
4. README env: `SMOCUR_BASE_URL` / `SMOCUR_API_TOKEN` / `SMOCUR_TENANT_ID` (console short prefix). Do not print values.
5. Copy sidecar spec from `/Users/brad/misc/github/databoost/dabo-smooth-curve/openapi/smoothcurve-v1.yaml` into this repo’s `openapi/`.
6. Stamp new first-party PHP/YAML/plan files with the canonical © line. Do **not** bulk-stamp the rest of the repo (other campaign).
7. Do not mix commits with uncommitted `php/time/` or other-handoff WIP.

## Out of scope

- `get_diminished_value` / power easing in the client.
- Admin provision HTTP (console `SmocurAdminClient`).
- Health/root helpers.
- dabo-tools CLI (member 5) and Carunch migrate (member 6).
- Live sidecar smoke (fake `Engine` is the test gate). Public DNS not required.
- Commit/PR unless the user asks after the plan.

## Steps

| Id | Title | Status | Revert |
|----|-------|--------|--------|
| S1 | Branch `cursor/smoothcurve-php-sdk` from `main`. Copy sidecar OpenAPI → [`openapi/smoothcurve-v1.yaml`](../../openapi/smoothcurve-v1.yaml). Leave Time/handoff WIP unstaged; do not revert it. | done | delete branch / restore OpenAPI file |
| S2 | Scaffold `php/smooth-curve`: `composer.json`, `phpunit.xml`, `Engine`, `HttpEngine`, `Client` (`curve`/`map`, Time-shaped HTTP). | done | delete `php/smooth-curve/` |
| S3 | PHPUnit: `Client::http` factory + fake Engine delegation for `curve`/`map`. | done | delete tests |
| S4 | Root [`README.md`](../../README.md): table rows + PHP (`databoost/smooth-curve`) section. Keep existing Time WIP hunks; do not revert them. | done | restore README |
| S5 | `composer install` + `./vendor/bin/phpunit` in `php/smooth-curve`. Path glob `../dabo-sdks/php/*` already covers the new package. | done | remove `php/smooth-curve/vendor/` (gitignored) |

## Notes

- S1: `git switch -c … main` failed (dirty `README.md`). Branched from `cursor/typescript-sro-client-de6a` HEAD (`a48e05e`). Time/`php/time`/other-handoff WIP still unstaged.
- Template: [`php/time/src/Client.php`](../../php/time/src/Client.php), [`php/time/src/HttpEngine.php`](../../php/time/src/HttpEngine.php), [`php/time/tests/ClientHttpFactoryTest.php`](../../php/time/tests/ClientHttpFactoryTest.php).
- Contract: [dabo-smooth-curve `openapi/smoothcurve-v1.yaml`](file:///Users/brad/misc/github/databoost/dabo-smooth-curve/openapi/smoothcurve-v1.yaml) (`POST …/curve`, `POST …/map`).
