#!/usr/bin/env bash
# =============================================================================
# Regresa a la liberacion anterior de BibliotecaOnline (Issue #156)
#   Vuelve a desplegar la version registrada en .release/previous con scripts/deploy.sh.
#   No revierte migraciones: si la version fallida cambio el esquema, restaurar la BD
#   (ver docs/cicd/pipeline-liberacion-despliegue.md, seccion Rollback).
#
# Uso: scripts/rollback.sh [version]     (default: .release/previous)
# =============================================================================
set -euo pipefail

cd "$(dirname "$0")/.."

OBJETIVO="${1:-$(cat .release/previous 2>/dev/null || true)}"
[ -n "$OBJETIVO" ] || { echo "ERROR: no hay version anterior registrada en .release/previous" >&2; exit 1; }

echo "==> Rollback: $(cat .release/current 2>/dev/null || echo '?') -> ${OBJETIVO}"
scripts/deploy.sh "$OBJETIVO"
