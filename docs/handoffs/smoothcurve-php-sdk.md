# Handoff: SmoothCurve PHP SDK

**Repo:** `databoost/dabo-sdks` (open this folder as the Cursor workspace root)
**Path:** `/Users/brad/misc/github/databoost/dabo-sdks`
**Date:** 2026-08-17
**Chat title:** SmoothCurve PHP SDK
**Campaign:** `/Users/brad/misc/github/databoost/dabo-tools/docs/campaigns/smoothcurve.md`
**Audience:** New agent — do not assume prior chat access except this doc + linked files.

## Goal

Add a **thin PHP client** for SmoothCurve HTTP (`curve` and `map`), same layout as [`php/sro`](../../php/sro) / [`php/time`](../../php/time). Package name likely `databoost/smooth-curve` (confirm with existing naming). Carunch (member 6) must use this SDK, not raw curl.

This is **campaign member 4**. SDK packagist-path install works (`databoost/smooth-curve`). Gated plan **stopped**.

## Current state

- **SMOCUR-SDK gated stopped** (plan complete). Package [`php/smooth-curve/`](../../php/smooth-curve/) — Composer `databoost/smooth-curve`, namespace `Databoost\SmoothCurve`. `curve`/`map` only. PHPUnit **OK (2 tests, 7 assertions)**.
- Branch `cursor/smoothcurve-php-sdk`. Spec: [`openapi/smoothcurve-v1.yaml`](../../openapi/smoothcurve-v1.yaml). Uncommitted (Time SDK / other-handoff WIP still unstaged — do not mix).
- Unblocks member 6. Campaign Next: **SmoothCurve tools CLI** (member 5).

## Decisions made

Do not reopen without the user:

1. Thin client only. Easing math lives in the sidecar, not a second copy in the SDK (SDK may send params; do not reimplement `power` easing unless tests need a fake).
2. Methods: **curve** and **map** matching OpenAPI from member 1.
3. Auth: Bearer + `X-Tenant-Id` like SRO/Time unless OpenAPI says otherwise.
4. Carunch consumes this package (path repo / Composer).

## Files changed/created

- This handoff (continue-from).
- [`.cursor/plans/smoothcurve-php-sdk.md`](../../.cursor/plans/smoothcurve-php-sdk.md) (gated plan).
- S1: [`openapi/smoothcurve-v1.yaml`](../../openapi/smoothcurve-v1.yaml).
- S2: [`php/smooth-curve/composer.json`](../../php/smooth-curve/composer.json), [`src/Client.php`](../../php/smooth-curve/src/Client.php), [`src/Engine.php`](../../php/smooth-curve/src/Engine.php), [`src/HttpEngine.php`](../../php/smooth-curve/src/HttpEngine.php), [`phpunit.xml`](../../php/smooth-curve/phpunit.xml).
- S3: [`php/smooth-curve/tests/ClientHttpFactoryTest.php`](../../php/smooth-curve/tests/ClientHttpFactoryTest.php).
- S4: root [`README.md`](../../README.md) SmoothCurve table rows + PHP section (Time hunks kept).

## Commands/tests run

- `php -l` on package src + test — no syntax errors.
- S5: `php /tmp/composer install` in `php/smooth-curve`; `./vendor/bin/phpunit` — OK (2 tests, 7 assertions); `composer validate` — valid.

## Known issues / risks

- Branch is from TypeScript-SRO HEAD (`a48e05e`), not `main` (dirty README blocked checkout). Time/`php/time` WIP still on the tree — do not stage it on an SDK-only commit.
- `composer` not on PATH; S5 used a temp phar. `vendor/` gitignored.

## Do not do

- Implement `get_diminished_value` in the SDK.
- dabo-tools CLI (member 5).
- Print tokens.

## Next steps

1. Member 4 complete. Do not hop. Paste into **SmoothCurve tools CLI**.
2. Commit/PR not in this plan unless asked.

## Useful references

- [`php/sro/composer.json`](../../php/sro/composer.json)
- [`php/sro/src/Client.php`](../../php/sro/src/Client.php)
- [`php/time/src/Client.php`](../../php/time/src/Client.php)
- Member 1 OpenAPI: [`dabo-smooth-curve/openapi/smoothcurve-v1.yaml`](file:///Users/brad/misc/github/databoost/dabo-smooth-curve/openapi/smoothcurve-v1.yaml)
- Gated plan: [`.cursor/plans/smoothcurve-php-sdk.md`](../../.cursor/plans/smoothcurve-php-sdk.md)
