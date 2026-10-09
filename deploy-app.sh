#!/usr/bin/env bash
# © 2026 Bradley Giesbrecht, © 2026 DataBoost™, LLC, © 2026 DataBoost™ Inc. All Rights Reserved.
# No argument: git pull --ff-only. A commit: fetch and fast-forward to that commit.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")" && pwd)"
cd "$ROOT"

deploy_ff() {
  local sha="${1:-}"
  local remote_ref head
  if [[ -z "$sha" ]]; then
    echo "deploy-app: git pull --ff-only"
    git pull --ff-only
    return
  fi
  echo "deploy-app: fetch and fast-forward to $sha"
  git fetch origin
  git rev-parse --verify --quiet "${sha}^{commit}" >/dev/null
  sha=$(git rev-parse "${sha}^{commit}")
  remote_ref=$(git symbolic-ref --short refs/remotes/origin/HEAD 2>/dev/null || true)
  if [[ -z "$remote_ref" ]]; then
    if git rev-parse --verify --quiet refs/remotes/origin/main >/dev/null; then
      remote_ref=origin/main
    else
      remote_ref=origin/master
    fi
  fi
  if ! git merge-base --is-ancestor "$sha" "$remote_ref"; then
    echo "deploy-app: $sha is not on $remote_ref" >&2
    exit 1
  fi
  head=$(git rev-parse HEAD)
  if [[ "$head" == "$sha" ]]; then
    echo "deploy-app: already at $sha"
    return
  fi
  if git merge-base --is-ancestor "$head" "$sha"; then
    git merge --ff-only "$sha"
    return
  fi
  if git merge-base --is-ancestor "$sha" "$head"; then
    echo "deploy-app: checkout is already past $sha" >&2
    exit 1
  fi
  echo "deploy-app: histories split from $sha" >&2
  exit 1
}

deploy_ff "${1:-}"
