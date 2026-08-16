---
title: "dabo-sdks rename complete"
status: completed
workspace: dabo-sdks
transcript: ~/.cursor/projects/Users-brad-misc-github-databoost-dabo-sdks/agent-transcripts/9aef5977-b1bd-46b3-940e-166a60d7dfb2/9aef5977-b1bd-46b3-940e-166a60d7dfb2.jsonl
related: []
topics: [rename, dabo-sdks, handoff, shape-b]
---

## Brief

Continued the rename handoff after the checkout became `dabo-sdks`: Shape B PHP layout, class renames, required base URL, pushed `839151b`, verified `lpp-console` / `dabo-sro` cutovers, and closed handoff steps 1–7. Safe to archive.

## When to open

- `sro-clients` → `dabo-sdks` rename status
- Shape B `php/sro/` layout / `Client` `Engine` `HttpEngine`
- required `SRO_BASE_URL` (no client default)
- sibling cutover with `lpp-console` and `dabo-sro`

## Handoff

Goal: Rename repo to `dabo-sdks`, Shape B layout, drop `Sro` class prefix, document AWS URL with required base URL.
Current state: Complete on `main` @ `839151b` (pushed). Handoff steps 1–7 checked. Working tree may still have unrelated dirty files (Time SDK, other handoffs) on feature branches.
Decisions made: Shape B (defer split CI); required base URL (document AWS only); keep package `databoost/sro` / namespace `Databoost\Sro\`.
Files changed/created: `php/sro/` layout; class renames; README/OpenAPI; `docs/handoffs/rename-to-dabo-sdks-and-drop-sro-prefix.md` marked complete.
Commands/tests run: PHPUnit in `php/sro` OK; push of `839151b`.
Known issues / risks: Unrelated dirty Time SDK / indexing handoff may remain uncommitted on other branches.
Do not do: Put ranking logic in clients; rename Ruby gem; commit/push without asking.
Next steps: None for this handoff — archive.
Useful references: `docs/handoffs/rename-to-dabo-sdks-and-drop-sro-prefix.md`; `php/sro/`; `lpp-console/docs/handoffs/sro-package-cutover-databoost-sro.md`; `dabo-sro/docs/handoffs/rename-repo-to-dabo-sro.md`

## Source of truth

Prefer these repo paths over the transcript:
- [`docs/handoffs/rename-to-dabo-sdks-and-drop-sro-prefix.md`](../../docs/handoffs/rename-to-dabo-sdks-and-drop-sro-prefix.md)
- [`php/sro/`](../../php/sro/)
- [`README.md`](../../README.md)
- [`openapi/sro-v1.yaml`](../../openapi/sro-v1.yaml)
