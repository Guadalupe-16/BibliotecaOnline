#!/usr/bin/env bash
# =============================================================================
# Verifica una liberacion ya desplegada de BibliotecaOnline (Issue #156)
#   1. Pruebas de humo HTTP (siempre): estado, contenido y tiempo de respuesta
#   2. E2E Playwright contra el entorno desplegado (RUN_E2E=true)
#   3. Carga corta con k6 contra el entorno desplegado (RUN_K6=true); falla si p95 >= 5000 ms
#   Deja reporte y metricas en dist/test-release/
#
# Uso: [RUN_E2E=true] [RUN_K6=true] scripts/test-release.sh
# Guia: docs/cicd/pipeline-liberacion-despliegue.md
# =============================================================================
set -uo pipefail

cd "$(dirname "$0")/.."

RELEASE_PORT="${RELEASE_PORT:-8090}"
BASE_URL="${BASE_URL:-http://127.0.0.1:${RELEASE_PORT}}"
SALIDA="dist/test-release"
REPORTE="$SALIDA/reporte.md"
mkdir -p "$SALIDA"

VERSION="$(cat .release/current 2>/dev/null || echo desconocida)"
fallos=0

{
    echo "# Verificacion de la liberacion ${VERSION}"
    echo
    echo "- Entorno: ${BASE_URL}"
    echo "- Fecha: $(date -u +%Y-%m-%dT%H:%M:%SZ)"
    echo
    echo "## Pruebas de humo"
    echo
    echo "| Prueba | Esperado | Obtenido | Tiempo | Resultado |"
    echo "|---|---|---|---|---|"
} > "$REPORTE"

# humo "nombre" ruta estado_esperado [texto_esperado]
humo() {
    local nombre="$1" ruta="$2" esperado="$3" texto="${4:-}"
    local cuerpo="$SALIDA/cuerpo.html" resultado="OK" estado tiempo
    read -r estado tiempo < <(curl -s -o "$cuerpo" -w '%{http_code} %{time_total}' "${BASE_URL}${ruta}")
    if [ "$estado" != "$esperado" ]; then
        resultado="FALLA"
    elif [ -n "$texto" ] && ! grep -q "$texto" "$cuerpo"; then
        resultado="FALLA (no contiene \"$texto\")"
    fi
    [ "$resultado" = "OK" ] || fallos=$((fallos + 1))
    local ms
    ms=$(awk -v t="$tiempo" 'BEGIN { printf "%.0f", t * 1000 }')
    printf '  %-45s %s -> %s  %5s ms  %s\n' "$nombre" "$esperado" "$estado" "$ms" "$resultado"
    echo "| ${nombre} | ${esperado} | ${estado} | ${ms} ms | ${resultado} |" >> "$REPORTE"
}

echo "==> Pruebas de humo contra ${BASE_URL} (liberacion ${VERSION})"
humo "Health check /up"                         "/up"             200
humo "Catalogo publico"                         "/catalogo"       200 "Libros"
humo "Detalle de libro 1 (seed)"                "/libros/1"       200 "Cien Anos de Soledad"
humo "Buscador"                                 "/buscar"         200 "Buscar por"
humo "Login"                                    "/login"          200 "password"
humo "Favoritos sin sesion redirige a login"    "/favoritos"      302
humo "Panel superadmin sin sesion redirige"     "/superadmin"     302
humo "Ruta inexistente responde 404"            "/no-existe-$$"   404
rm -f "$SALIDA/cuerpo.html"

if [ "${RUN_E2E:-false}" = "true" ]; then
    echo "==> E2E Playwright contra el entorno desplegado"
    if PLAYWRIGHT_BASE_URL="$BASE_URL" npx playwright test --reporter=list,html > "$SALIDA/e2e.log" 2>&1; then
        e2e="OK"
    else
        e2e="FALLA"; fallos=$((fallos + 1))
    fi
    tail -3 "$SALIDA/e2e.log"
    { echo; echo "## E2E Playwright"; echo; echo "Resultado: **${e2e}** — $(grep -E '[0-9]+ (passed|failed)' "$SALIDA/e2e.log" | tr '\n' ' ')"; } >> "$REPORTE"
fi

if [ "${RUN_K6:-false}" = "true" ]; then
    echo "==> Carga corta con k6 (perfil reducido del plan: 10 VUs, 40 s)"
    { echo; echo "## Carga k6 (p95 < 5000 ms)"; echo; echo "| Script | p95 | Fallidas | Threshold |"; echo "|---|---|---|---|"; } >> "$REPORTE"
    for script in jarbprueba gaqprueba; do
        if k6 run --quiet --stage 10s:10,20s:10,10s:0 -e BASE_URL="$BASE_URL" \
              --summary-export="$SALIDA/k6-${script}.json" "tests/load/${script}.js" > "$SALIDA/k6-${script}.log" 2>&1; then
            umbral="OK"
        else
            umbral="FALLA"; fallos=$((fallos + 1))
        fi
        read -r p95 fallidas < <(python3 -c "
import json; m=json.load(open('$SALIDA/k6-${script}.json'))['metrics']
print(round(m['http_req_duration']['p(95)'],2), round(m['http_req_failed']['value']*100,2))")
        printf '  %-12s p95=%s ms  fallidas=%s %%  %s\n' "$script" "$p95" "$fallidas" "$umbral"
        echo "| ${script}.js | ${p95} ms | ${fallidas} % | ${umbral} |" >> "$REPORTE"
    done
fi

{ echo; echo "**Resultado global: $([ "$fallos" -eq 0 ] && echo 'LIBERACION VERIFICADA' || echo "${fallos} VERIFICACION(ES) FALLIDA(S)")**"; } >> "$REPORTE"
echo "==> Reporte: ${REPORTE}"
[ "$fallos" -eq 0 ] && echo "==> LIBERACION VERIFICADA" || echo "==> ${fallos} verificacion(es) fallida(s)" >&2
exit "$fallos"
