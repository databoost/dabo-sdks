---
title: "tumbler-dabo-sdks overview"
status: completed
workspace: dabo-sdks
transcript: n/a (cloud agent bc-870d0eb2-8e93-4355-8afe-9bc119542f1b)
related: []
topics: [fleet, tumbler, archive]
---

## Brief

My Machines agent on `tumbler-dabo-sdks` confirmed the checkout and README purpose (thin SRO SDKs). Explained the second sidebar “dabo-sdks” entry as control-plane **Remote dabo-sdks** on `tumbler-control` / `dabo-fleet`, not a second repo agent. Commit-worthy found outside handoffs only; no code changes in this chat. Safe to archive.

## When to open

- tumbler-dabo-sdks idle / overview archive
- why two Tumbler agents show for dabo-sdks (control vs repo worker)
- fleet worker on dabo-sdks with no code changes from this chat (2026-08-10)

## Handoff

Goal: Confirm Tumbler worker state for `dabo-sdks` and wait for instructions.
Current state: Worker ready; this chat made no repo edits. Working tree may still have outside dirty handoffs and sit on `cursor/typescript-sro-client-de6a` (not from this chat).
Decisions made: Only one self-hosted agent is bound to `github.com/databoost/dabo-sdks` (`bc-870d0eb2…`); **Remote dabo-sdks** (`bc-019fe834…`) is fleet/control on `dabo-fleet`. Left-nav rename of another agent is not available from this session / Agents API.
Files changed/created: This chat-index entry only (from prep archive).
Commands/tests run: `git status` / branch checks; `dabo-fleet agents list|show`; cursor-cloud `run-info` / `list-cloud-agents`.
Known issues / risks: Outside dirty docs under `docs/handoffs/` remain uncommitted unless chosen in a later commit-worthy. Branch tip may differ from `main` due to other work.
Do not do: Treat **Remote dabo-sdks** as a second `dabo-sdks` checkout agent; invent rename API for other agents.
Next steps: Archive this chat; optionally commit outside handoffs from their owning chat; archive or rename control agent from that chat’s UI.
Useful references:
- [`README.md`](../../README.md)
- [`docs/handoffs/rename-to-dabo-sdks-and-drop-sro-prefix.md`](../../docs/handoffs/rename-to-dabo-sdks-and-drop-sro-prefix.md)
- [`docs/handoffs/index-legacy-sro-clients-agents.md`](../../docs/handoffs/index-legacy-sro-clients-agents.md)
- Agent: https://cursor.com/agents/bc-870d0eb2-8e93-4355-8afe-9bc119542f1b

## Source of truth

Prefer these repo paths over the transcript:
- [`README.md`](../../README.md)
- [`.cursor/chat-index/tumbler-dabo-sdks-overview.md`](tumbler-dabo-sdks-overview.md)
- [`docs/handoffs/`](../../docs/handoffs/)
