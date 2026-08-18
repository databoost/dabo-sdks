---
title: "SmoothCurve PHP SDK"
status: completed
workspace: dabo-sdks
transcript: ~/.cursor/projects/Users-brad-misc-github-databoost-dabo-sdks/agent-transcripts/8df25a84-d482-440f-87a4-4ea6c92046f3/8df25a84-d482-440f-87a4-4ea6c92046f3.jsonl
related: []
topics: [smoothcurve, php-sdk, dabo-sdks, campaign]
---

## Brief

Campaign member 4: thin PHP SmoothCurve client (`databoost/smooth-curve`, `curve`/`map` only). Gated SMOCUR-SDK completed and stopped. Path-install ready for Carunch. Safe to archive; commit/PR of README Time mix and Time SDK WIP were left outside.

## When to open

- SmoothCurve PHP SDK / `php/smooth-curve` / `databoost/smooth-curve`
- SMOCUR-SDK gated plan
- campaign smoothcurve member 4
- Carunch path-install of the SmoothCurve client

## Handoff

Goal: Thin PHP client for SmoothCurve HTTP (`curve` and `map`), same Shape B layout as `php/sro` / `php/time`.
Current state: Package lives under `php/smooth-curve/`. Branch `cursor/smoothcurve-php-sdk` (from TypeScript SRO HEAD, not `main`). PHPUnit OK (2 tests, 7 assertions). Gated **stopped**. Campaign Next was SmoothCurve tools CLI.
Decisions made: Composer `databoost/smooth-curve`; namespace `Databoost\SmoothCurve`; Bearer + `X-Tenant-Id`; no easing math; no admin API; README env `SMOCUR_*`.
Files changed/created: `php/smooth-curve/`, `openapi/smoothcurve-v1.yaml`, `docs/handoffs/smoothcurve-php-sdk.md`, `.cursor/plans/smoothcurve-php-sdk.md`; README SmoothCurve section remains mixed with Time WIP (uncommitted).
Commands/tests run: `php -l`; `composer install` via temp phar; `./vendor/bin/phpunit` OK (2 tests, 7 assertions); `composer validate` OK.
Known issues / risks: Branch not from `main`; Time SDK / other handoffs / mixed README still dirty; `composer` not on PATH.
Do not do: Implement `get_diminished_value` in the SDK; mix Time files into SmoothCurve commits; print tokens.
Next steps: Archive this chat. CLI is campaign member 5. Commit README SmoothCurve hunks after splitting Time. Optional PR once branch base is clean.
Useful references: `php/smooth-curve/`; `openapi/smoothcurve-v1.yaml`; `docs/handoffs/smoothcurve-php-sdk.md`; `$DABO_TOOLS/docs/campaigns/smoothcurve.md`

## Source of truth

Prefer these repo paths over the transcript:
- [`php/smooth-curve/`](../../php/smooth-curve/)
- [`openapi/smoothcurve-v1.yaml`](../../openapi/smoothcurve-v1.yaml)
- [`docs/handoffs/smoothcurve-php-sdk.md`](../../docs/handoffs/smoothcurve-php-sdk.md)
- [`.cursor/plans/smoothcurve-php-sdk.md`](../plans/smoothcurve-php-sdk.md)
