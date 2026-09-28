#!/usr/bin/env bash
# =============================================================================
# Prepara una liberacion de BibliotecaOnline (Issue #156)
#   1. Genera .env.release (solo la primera vez; nunca se versiona)
#   2. Construye la imagen Docker bibliotecaonline:<version>
#   3. Opcional (EXPORT=true): exporta la imagen a dist/ para adjuntarla a la liberacion
#   SKIP_BUILD=true: solo genera .env.release (la imagen ya existe, p. ej. en el pipeline)
#
# Uso: scripts/prepare-release.sh [version]      (default: git describe)
# Guia: docs/cicd/pipeline-liberacion-despliegue.md
# =============================================================================
set -euo pipefail

cd "$(dirname "$0")/.."

VERSION="${1:-$(git describe --tags --always --dirty 2>/dev/null || echo dev)}"
RELEASE_PORT="${RELEASE_PORT:-8090}"
ENV_FILE=".env.release"

command -v docker >/dev/null || { echo "ERROR: Docker no esta instalado." >&2; exit 1; }
docker compose version >/dev/null || { echo "ERROR: falta Docker Compose." >&2; exit 1; }

aleatorio() { openssl rand -hex "${1:-16}"; }

if [ ! -f "$ENV_FILE" ]; then
    echo "==> Generando $ENV_FILE (credenciales aleatorias, solo para este entorno)"
    cp .env.example "$ENV_FILE"
    fijar() {  # fijar CLAVE valor  -> reemplaza o agrega la linea
        if grep -qE "^#? ?$1=" "$ENV_FILE"; then
            sed -i.bak -E "s|^#? ?$1=.*|$1=$2|" "$ENV_FILE"
        else
            echo "$1=$2" >> "$ENV_FILE"
        fi
    }
    fijar APP_NAME '"Biblioteca Digital"'
    fijar APP_ENV production
    fijar APP_DEBUG false
    fijar APP_URL "http://127.0.0.1:${RELEASE_PORT}"
    fijar APP_KEY "base64:$(openssl rand -base64 32)"
    fijar LOG_CHANNEL stderr
    fijar LOG_LEVEL info
    fijar DB_CONNECTION mysql
    fijar DB_HOST db
    fijar DB_PORT 3306
    fijar DB_DATABASE biblioteca
    fijar DB_USERNAME biblioteca
    fijar DB_PASSWORD "$(aleatorio)"
    fijar MYSQL_ROOT_PASSWORD "$(aleatorio)"
    fijar SESSION_DRIVER database
    fijar QUEUE_CONNECTION database
    fijar CACHE_STORE database
    fijar MAIL_MAILER log
    rm -f "$ENV_FILE.bak"
    chmod 600 "$ENV_FILE"
else
    echo "==> $ENV_FILE ya existe; se conserva (misma APP_KEY y credenciales entre liberaciones)"
fi

if [ "${SKIP_BUILD:-false}" = "true" ]; then
    # En el pipeline la imagen ya viene construida del job anterior (docker load)
    echo "==> SKIP_BUILD=true: solo se prepara el entorno; se usa la imagen existente"
    exit 0
fi

echo "==> Construyendo imagen bibliotecaonline:${VERSION}"
docker build \
    --build-arg APP_VERSION="$VERSION" \
    -t "bibliotecaonline:${VERSION}" \
    .

if [ "${EXPORT:-false}" = "true" ]; then
    mkdir -p dist
    ARCHIVO="dist/bibliotecaonline-${VERSION}.tar.gz"
    echo "==> Exportando imagen a ${ARCHIVO}"
    docker save "bibliotecaonline:${VERSION}" | gzip > "$ARCHIVO"
    du -h "$ARCHIVO"
fi

echo "==> Liberacion preparada: bibliotecaonline:${VERSION}"
echo "    Siguiente paso: scripts/deploy.sh ${VERSION}"
