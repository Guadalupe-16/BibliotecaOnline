#!/usr/bin/env bash
# =============================================================================
# Despliega una liberacion de BibliotecaOnline en el entorno de contenedores (Issue #156)
#   - Levanta db + app + worker con la imagen bibliotecaonline:<version>
#   - La app migra la BD al arrancar (y siembra datos si la BD es nueva)
#   - Espera el health check /up (nivel de servicio: < 120 s)
#   - Registra la version actual y la anterior en .release/ para scripts/rollback.sh
#
# Uso: scripts/deploy.sh <version>
# Guia: docs/cicd/pipeline-liberacion-despliegue.md
# =============================================================================
set -euo pipefail

cd "$(dirname "$0")/.."

VERSION="${1:?Uso: scripts/deploy.sh <version>}"
export RELEASE_VERSION="$VERSION"
export RELEASE_PORT="${RELEASE_PORT:-8090}"
HEALTH_TIMEOUT="${HEALTH_TIMEOUT:-120}"
COMPOSE=(docker compose --env-file .env.release -f docker-compose.release.yml)

[ -f .env.release ] || { echo "ERROR: falta .env.release; ejecuta scripts/prepare-release.sh" >&2; exit 1; }
docker image inspect "bibliotecaonline:${VERSION}" >/dev/null 2>&1 \
    || { echo "ERROR: no existe la imagen bibliotecaonline:${VERSION}" >&2; exit 1; }

mkdir -p .release
ANTERIOR="$(cat .release/current 2>/dev/null || true)"

echo "==> Desplegando bibliotecaonline:${VERSION} (anterior: ${ANTERIOR:-ninguna})"
inicio=$(date +%s)
"${COMPOSE[@]}" up -d --remove-orphans

echo "==> Esperando health check http://127.0.0.1:${RELEASE_PORT}/up (maximo ${HEALTH_TIMEOUT} s)"
until curl -fsS "http://127.0.0.1:${RELEASE_PORT}/up" >/dev/null 2>&1; do
    if [ $(( $(date +%s) - inicio )) -ge "$HEALTH_TIMEOUT" ]; then
        echo "ERROR: la aplicacion no respondio en /up en ${HEALTH_TIMEOUT} s" >&2
        "${COMPOSE[@]}" ps
        "${COMPOSE[@]}" logs --tail 80 app
        exit 1
    fi
    sleep 2
done
segundos=$(( $(date +%s) - inicio ))

if [ -n "$ANTERIOR" ] && [ "$ANTERIOR" != "$VERSION" ]; then
    echo "$ANTERIOR" > .release/previous
fi
echo "$VERSION" > .release/current
echo "$(date -u +%Y-%m-%dT%H:%M:%SZ) ${VERSION} ${segundos}s" >> .release/history

"${COMPOSE[@]}" ps
echo "==> Desplegado bibliotecaonline:${VERSION} en ${segundos} s -> http://127.0.0.1:${RELEASE_PORT}"
echo "    Siguiente paso: scripts/test-release.sh"
