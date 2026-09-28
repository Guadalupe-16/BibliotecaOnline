#!/usr/bin/env bash
# =============================================================================
# Validacion integral de monitoreo (#165), trazabilidad (#166) y auditoria (#167) — Issue #168
#   Contra el entorno de liberacion desplegado (scripts/deploy.sh) con el monitoreo arriba
#   (scripts/monitoring.sh up). Una visita a /catalogo debe aparecer en los tres modulos:
#     metricas (contador de Prometheus), traza (request_traces con su X-Trace-Id) y
#     auditoria (activity_logs, accion "catalogo").
#   Deja el reporte en dist/validacion/reporte.md y sale con el numero de fallos.
#
# Uso: scripts/validar-integracion.sh
# Guia: docs/monitoreo/validacion-integral.md
# =============================================================================
set -uo pipefail

cd "$(dirname "$0")/.."

export RELEASE_VERSION="${RELEASE_VERSION:-$(cat .release/current 2>/dev/null || true)}"
APP="http://127.0.0.1:${RELEASE_PORT:-8090}"
PROMETHEUS="http://127.0.0.1:${PROMETHEUS_PORT:-9090}"
COMPOSE=(docker compose --env-file .env.release -f docker-compose.release.yml -f docker-compose.monitoring.yml)
SALIDA="dist/validacion"
REPORTE="$SALIDA/reporte.md"
mkdir -p "$SALIDA"

valor() { grep -E "^$1=" .env.release | head -1 | cut -d= -f2-; }
TOKEN="$(valor METRICS_TOKEN)"

# sql "consulta" -> resultado sin encabezados, ejecutado dentro del contenedor de MySQL
sql() {
    "${COMPOSE[@]}" exec -T db sh -c 'mysql -N -u "$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" -e "$0"' "$1" 2>/dev/null
}
contador_catalogo() {
    curl -fsS -H "Authorization: Bearer ${TOKEN}" "${APP}/metrics" \
        | awk '/^bibliotecaonline_http_requests_total\{method="GET",route="\/catalogo",status="200"\}/ {print $2}'
}
esperar() {  # esperar segundos "comando que debe tener exito"
    local limite=$1; shift
    for _ in $(seq 1 "$limite"); do eval "$*" >/dev/null 2>&1 && return 0; sleep 1; done
    return 1
}

fallos=0
{
    echo "# Validación integral — liberación ${RELEASE_VERSION}"
    echo
    echo "- Fecha: $(date -u +%Y-%m-%dT%H:%M:%SZ)"
    echo "- Entorno: ${APP}"
    echo
    echo "| # | Verificación | Resultado | Detalle |"
    echo "|---|---|---|---|"
} > "$REPORTE"

registrar() {  # registrar n "verificacion" ok|falla "detalle"
    local marca="OK"
    [ "$3" = "ok" ] || { marca="FALLA"; fallos=$((fallos + 1)); }
    printf '  %-6s %s — %s\n' "$marca" "$2" "$4"
    echo "| $1 | $2 | $marca | $4 |" >> "$REPORTE"
}

echo "==> Validacion integral contra ${APP} (liberacion ${RELEASE_VERSION})"

# 1. Salud basica
estado=$(curl -s -o /dev/null -w '%{http_code}' "${APP}/up")
[ "$estado" = "200" ] && r=ok || r=falla
registrar 1 "Aplicación disponible (/up)" "$r" "HTTP ${estado}"

# 2. Una sola visita a /catalogo
antes_metrica=$(contador_catalogo); antes_metrica=${antes_metrica:-0}
antes_auditoria=$(sql "SELECT COUNT(*) FROM activity_logs WHERE accion='catalogo'")
trace_id=$(curl -s -D - -o /dev/null "${APP}/catalogo" | awk 'tolower($1)=="x-trace-id:" {print $2}' | tr -d '\r')
[ -n "$trace_id" ] && r=ok || r=falla
registrar 2 "La respuesta trae X-Trace-Id" "$r" "${trace_id:-sin cabecera}"

# 3. Monitoreo: el contador subio exactamente en 1
despues_metrica=$(contador_catalogo)
[ "${despues_metrica%.*}" = "$(( ${antes_metrica%.*} + 1 ))" ] && r=ok || r=falla
registrar 3 "Métrica: contador de GET /catalogo +1" "$r" "${antes_metrica} → ${despues_metrica}"

# 4. Trazabilidad: la traza existe (la escribe el worker desde la cola)
if esperar 30 "[ \"\$(sql \"SELECT COUNT(*) FROM request_traces WHERE trace_id='${trace_id}'\")\" = 1 ]"; then
    detalle=$(sql "SELECT CONCAT(metodo,' ',ruta,' → ',status_http,', ',duracion_ms,' ms') FROM request_traces WHERE trace_id='${trace_id}'")
    registrar 4 "Traza guardada con ese trace_id" ok "$detalle"
else
    registrar 4 "Traza guardada con ese trace_id" falla "no apareció en 30 s"
fi

# 5. Auditoria: la visita quedo registrada como accion de negocio
if esperar 30 "[ \"\$(sql \"SELECT COUNT(*) FROM activity_logs WHERE accion='catalogo'\")\" -gt ${antes_auditoria:-0} ]"; then
    registrar 5 "Auditoría: acción 'catalogo' registrada" ok "$(sql "SELECT COUNT(*) FROM activity_logs WHERE accion='catalogo'") registros (antes ${antes_auditoria})"
else
    registrar 5 "Auditoría: acción 'catalogo' registrada" falla "sin registro nuevo en 30 s"
fi

# 6. Separacion de responsabilidades: la auditoria no guarda el trace_id ni datos tecnicos
fuga=$(sql "SELECT COUNT(*) FROM activity_logs WHERE descripcion LIKE '%${trace_id}%'")
[ "$fuga" = "0" ] && r=ok || r=falla
registrar 6 "Auditoría sin datos técnicos de la traza" "$r" "${fuga} coincidencias"

# 7. Las metricas no exponen el trace_id ni IPs
curl -fsS -H "Authorization: Bearer ${TOKEN}" "${APP}/metrics" > "$SALIDA/metrics.txt"
if grep -q "$trace_id" "$SALIDA/metrics.txt" || grep -Eq '\b(ip|user_id)="' "$SALIDA/metrics.txt"; then r=falla; else r=ok; fi
registrar 7 "/metrics sin trace_id, IP ni usuario" "$r" "$(grep -c '^bibliotecaonline_' "$SALIDA/metrics.txt") series"

# 8. /metrics protegido
estado=$(curl -s -o /dev/null -w '%{http_code}' "${APP}/metrics")
[ "$estado" = "403" ] && r=ok || r=falla
registrar 8 "/metrics sin token responde 403" "$r" "HTTP ${estado}"

# 9. Visores protegidos por rol (sin sesion -> login)
for visor in /admin/logs /admin/trazas; do
    destino=$(curl -s -o /dev/null -w '%{http_code} %{redirect_url}' "${APP}${visor}")
    case "$destino" in 302*login*) r=ok ;; *) r=falla ;; esac
    registrar 9 "Visor ${visor} exige sesión" "$r" "$destino"
done

# 10. Scheduler: la poda de trazas esta programada (el servicio arranca despues de que app esta sana)
programado() { "${COMPOSE[@]}" exec -T scheduler php artisan schedule:list 2>/dev/null | grep -q "trazas:podar"; }
if esperar 60 programado; then
    registrar 10 "Scheduler con trazas:podar programado" ok "servicio scheduler activo"
else
    registrar 10 "Scheduler con trazas:podar programado" falla "no aparece en schedule:list"
fi

# 11. Prometheus ve la app y la disponibilidad
if curl -fsS "${PROMETHEUS}/api/v1/query?query=up%7Bjob%3D%22bibliotecaonline%22%7D" | grep -q '"1"' \
   && curl -fsS "${PROMETHEUS}/api/v1/query?query=probe_success" | grep -q '"1"'; then r=ok; else r=falla; fi
registrar 11 "Prometheus: targets app y /up en 1" "$r" "up y probe_success"

# 12. Sin alertas disparadas con el sistema sano
disparadas=$(curl -fsS "${PROMETHEUS}/api/v1/alerts" | python3 -c "import json,sys; print(sum(a['state']=='firing' for a in json.load(sys.stdin)['data']['alerts']))")
[ "$disparadas" = "0" ] && r=ok || r=falla
registrar 12 "Sin alertas en firing con el sistema sano" "$r" "${disparadas} en firing"

{ echo; echo "**Resultado: $([ "$fallos" -eq 0 ] && echo 'INTEGRACIÓN VALIDADA' || echo "${fallos} verificación(es) fallida(s)")**"; } >> "$REPORTE"
echo "==> Reporte: ${REPORTE}"
[ "$fallos" -eq 0 ] && echo "==> INTEGRACION VALIDADA" || echo "==> ${fallos} verificacion(es) fallida(s)" >&2
exit "$fallos"
