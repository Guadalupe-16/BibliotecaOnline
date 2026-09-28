#!/usr/bin/env bash
# =============================================================================
# Stack de monitoreo de BibliotecaOnline: Prometheus + blackbox_exporter + Grafana (Issue #165)
#   Requiere el entorno de liberacion desplegado (scripts/prepare-release.sh + scripts/deploy.sh).
#
# Uso: scripts/monitoring.sh up|down|status|verify
#   up      levanta el stack junto al entorno de liberacion
#   down    apaga solo el stack de monitoreo
#   status  estado de los contenedores
#   verify  comprueba targets, reglas de alerta y dashboard (sale con error si algo falla)
#
# Guia: docs/monitoreo/monitoreo.md
# =============================================================================
set -euo pipefail

cd "$(dirname "$0")/.."

ACCION="${1:-status}"
export RELEASE_VERSION="${RELEASE_VERSION:-$(cat .release/current 2>/dev/null || true)}"
PROMETHEUS="http://127.0.0.1:${PROMETHEUS_PORT:-9090}"
GRAFANA="http://127.0.0.1:${GRAFANA_PORT:-3000}"
COMPOSE=(docker compose --env-file .env.release -f docker-compose.release.yml -f docker-compose.monitoring.yml)
SERVICIOS=(prometheus blackbox grafana)

[ -f .env.release ] || { echo "ERROR: falta .env.release; ejecuta scripts/prepare-release.sh" >&2; exit 1; }
[ -n "$RELEASE_VERSION" ] || { echo "ERROR: no hay liberacion desplegada; ejecuta scripts/deploy.sh" >&2; exit 1; }

valor() { grep -E "^$1=" .env.release | head -1 | cut -d= -f2-; }

case "$ACCION" in
up)
    TOKEN="$(valor METRICS_TOKEN)"
    [ -n "$TOKEN" ] || { echo "ERROR: falta METRICS_TOKEN; vuelve a ejecutar scripts/prepare-release.sh" >&2; exit 1; }
    mkdir -p monitoring/.secretos
    printf '%s' "$TOKEN" > monitoring/.secretos/metrics_token
    chmod 644 monitoring/.secretos/metrics_token   # Prometheus corre como otro usuario dentro del contenedor

    echo "==> Levantando Prometheus, blackbox_exporter y Grafana (liberacion ${RELEASE_VERSION})"
    "${COMPOSE[@]}" up -d "${SERVICIOS[@]}"
    echo "    Prometheus: ${PROMETHEUS}"
    echo "    Grafana:    ${GRAFANA}  (usuario admin; contrasena en GRAFANA_ADMIN_PASSWORD de .env.release)"
    ;;
down)
    "${COMPOSE[@]}" stop "${SERVICIOS[@]}"
    "${COMPOSE[@]}" rm -f "${SERVICIOS[@]}"
    ;;
status)
    "${COMPOSE[@]}" ps
    ;;
verify)
    fallos=0
    revisar() {  # revisar "descripcion" comando...
        if "${@:2}" >/dev/null 2>&1; then echo "  OK     $1"; else echo "  FALLA  $1"; fallos=$((fallos + 1)); fi
    }
    echo "==> Verificando el stack de monitoreo"
    revisar "Prometheus responde"               curl -fsS "${PROMETHEUS}/-/ready"
    revisar "Grafana responde"                  curl -fsS "${GRAFANA}/api/health"
    for job in bibliotecaonline disponibilidad; do
        revisar "Target '${job}' en estado up" sh -c \
            "curl -fsS '${PROMETHEUS}/api/v1/targets?state=active' | python3 -c \"import json,sys; t=[x for x in json.load(sys.stdin)['data']['activeTargets'] if x['labels']['job']=='${job}']; sys.exit(0 if t and all(x['health']=='up' for x in t) else 1)\""
    done
    revisar "Reglas de alerta cargadas (6)" sh -c \
        "curl -fsS '${PROMETHEUS}/api/v1/rules?type=alert' | python3 -c \"import json,sys; g=json.load(sys.stdin)['data']['groups']; sys.exit(0 if sum(len(x['rules']) for x in g)==6 else 1)\""
    revisar "Metricas de la app en Prometheus" sh -c \
        "curl -fsS '${PROMETHEUS}/api/v1/query?query=bibliotecaonline_app_info' | python3 -c \"import json,sys; sys.exit(0 if json.load(sys.stdin)['data']['result'] else 1)\""
    revisar "Dashboard provisionado en Grafana" sh -c \
        "curl -fsS -u admin:'$(valor GRAFANA_ADMIN_PASSWORD)' '${GRAFANA}/api/dashboards/uid/bibliotecaonline' | grep -q 'BibliotecaOnline'"
    [ "$fallos" -eq 0 ] && echo "==> MONITOREO VERIFICADO" || echo "==> ${fallos} verificacion(es) fallida(s)" >&2
    exit "$fallos"
    ;;
*)
    echo "Uso: scripts/monitoring.sh up|down|status|verify" >&2
    exit 2
    ;;
esac
