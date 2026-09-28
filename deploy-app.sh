#!/usr/bin/env bash
# © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")" && pwd)"
cd "$ROOT"
git pull --ff-only
