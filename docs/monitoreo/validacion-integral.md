# Validación integral: monitoreo, trazabilidad y auditoría

> Issue: #168 · Fecha: 2026-09-28 · Ejecutada por: Javier Antonio Romo Bernal
> Código validado: `develop` @ `30eca26` (incluye #165 monitoreo, #166 trazabilidad y #167 auditoría)
> más los cambios de este issue (rama `test/168-validacion-integral`).

**Resultado: los tres módulos funcionan juntos y el CI permanece en verde.** Se encontraron 2 huecos
de integración (uno corregido aquí) y 3 observaciones documentadas; ninguno es crítico.

---

## 1. Cómo se integran los tres módulos

| | Monitoreo (#165) | Trazabilidad (#166) | Auditoría (#167) |
|---|---|---|---|
| Pregunta que responde | ¿Cómo está el sistema? | ¿Qué pasó en esta solicitud? | ¿Quién hizo qué? |
| Dónde se registra | Middleware **global** → APCu | Middleware del grupo **`web`** → cola → `request_traces` | `ActivityLog::registrar()` → cola → `activity_logs` |
| Dónde se consulta | Grafana `:3000` / Prometheus `:9090` | `/admin/trazas` | `/admin/logs` |
| Identificador | Patrón de ruta (sin IDs) | `trace_id` (UUID, cabecera `X-Trace-Id`) | Acción + autor |

Una misma solicitud puede aparecer en los tres, sin duplicar datos: las métricas no guardan IP ni
usuario, la auditoría no guarda datos técnicos y la trazabilidad no guarda acciones de negocio.

## 2. Pruebas automatizadas — VALIDADO

| Suite | Comando | Resultado |
|---|---|---|
| PHPUnit (completa) | `php artisan test` | **177 pasadas** (354 aserciones) |
| PHPUnit (módulos de observabilidad) | `--filter='Metricas\|Trazabilidad\|Trazas\|ActivityLog\|Auditoria\|Integracion'` | 59 pasadas |
| **Integración entre módulos (nueva)** | `tests/Feature/IntegracionObservabilidadTest.php` | **6 pasadas** |
| Vitest | `npm test` | 18 pasadas |
| ESLint / Prettier | `npm run lint` / `npm run format:check` | Sin errores |
| Playwright E2E (contra el entorno desplegado) | `RUN_E2E=true scripts/test-release.sh` | 12 pasadas |

`IntegracionObservabilidadTest` verifica en una sola solicitud:

1. Una acción administrativa queda en **métricas** (contador con el patrón de ruta), en **trazas**
   (con el `X-Trace-Id` de la respuesta, el usuario y el estado 302) y en **auditoría** (con su autor).
2. Cada módulo guarda solo lo suyo: la auditoría sin datos técnicos y las métricas sin IP, usuario ni IDs.
3. Un 404 de recurso inexistente (`/libros/999999`) se traza y se cuenta, pero no se audita.
4. Una URL sin ruta no se traza (limitación documentada en la spec 005), **pero el monitoreo la cubre**
   (`route="sin_ruta"`).
5. El scraping de `/metrics` no genera trazas ni auditoría.
6. Los dos visores exigen sesión y rol (`admin`/`superadmin`), y `/metrics` exige token.

## 3. Validación contra el entorno desplegado — VALIDADO

Entorno de liberación del #156 (`scripts/deploy.sh integ1`) con el monitoreo arriba
(`scripts/monitoring.sh up`). Script nuevo y repetible: **`scripts/validar-integracion.sh`**.
Parte de una sola visita a `/catalogo`, que debe aparecer en los tres módulos:

| # | Verificación | Resultado |
|---|---|---|
| 1 | `/up` responde | OK (200) |
| 2 | La respuesta trae `X-Trace-Id` | OK |
| 3 | Métrica: contador de `GET /catalogo` sube exactamente 1 | OK (1 → 2) |
| 4 | La traza con ese `trace_id` existe en `request_traces` | OK (`GET catalogo → 200, 13 ms`) |
| 5 | Auditoría: acción `catalogo` registrada | OK |
| 6 | La auditoría no contiene el `trace_id` | OK |
| 7 | `/metrics` sin `trace_id`, IP ni usuario | OK (46 series) |
| 8 | `/metrics` sin token → 403 | OK |
| 9 | `/admin/logs` y `/admin/trazas` exigen sesión | OK (302 → `/login`) |
| 10 | Scheduler con `trazas:podar` programado | OK |
| 11 | Prometheus: `up` y `probe_success` en 1 | OK |
| 12 | Ninguna alerta en *firing* con el sistema sano | OK |

**Resultado: 13/13, INTEGRACIÓN VALIDADA.** Además:

| Verificación | Resultado |
|---|---|
| `scripts/test-release.sh` (humo) | 8/8 |
| `scripts/monitoring.sh verify` | 7/7 |
| k6 contra el entorno (NS-1: p95 < 5000 ms) | `jarbprueba` p95 = 107.96 ms · `gaqprueba` p95 = 82.57 ms · 0 % fallidas |

## 4. Incidente provocado: base de datos caída — VALIDADO

MySQL detenido durante 90 s mientras se enviaban 420 solicitudes (`/catalogo`, `/libros/1`, `/login`):

| Módulo | Qué registró de las 420 respuestas 500 |
|---|---|
| **Monitoreo** | **420/420** errores 5xx contados; alerta `BibliotecaOnlineBaseDeDatosCaida` en **firing** |
| **Trazabilidad** | **0/420** trazas. El middleware registró 420 avisos `No se pudo registrar la traza` (con su `trace_id`) en el log de la app |
| **Auditoría** | 0 registros (su cola también vive en MySQL) |

Al restaurar MySQL, la app volvió a responder 200 y la alerta se resolvió sola.

## 5. Hallazgos

| # | Hallazgo | Severidad | Estado |
|---|---|---|---|
| H1 | **La trazabilidad pierde los errores de un incidente de BD.** Las trazas se encolan en la tabla `jobs` de MySQL: si MySQL cae, el `dispatch` falla y el middleware (*best effort*, a propósito para no romper la respuesta) solo deja un aviso en el log. Justo las solicitudes más interesantes no quedan en `/admin/trazas` | Media | **Documentado.** El monitoreo sí los cubre (§4). Recomendación: usar Redis como cola, o escribir la traza en el log estructurado (`stderr`) cuando falle el `dispatch`, para no perder el `trace_id` con su contexto |
| H2 | **No había scheduler en el entorno de liberación:** `trazas:podar` (diario) nunca se ejecutaba y `request_traces` crecería sin límite | Media | **Corregido en este issue:** servicio `scheduler` (`php artisan schedule:work`) en `docker-compose.release.yml`, verificado por `validar-integracion.sh` (#10) |
| H3 | Las URLs sin ruta no se trazan (limitación ya documentada en la spec 005) | Baja | Cubierto por el monitoreo (`route="sin_ruta"`); probado en `IntegracionObservabilidadTest` |
| H4 | **Costo de la observabilidad:** con trazabilidad activa, cada solicitud agrega una escritura en `jobs`. En la misma máquina, el p95 de `jarbprueba` pasó de 39 ms (#165) a 108 ms | Baja | Documentado. Sigue 46 veces por debajo de NS-1. Comparación de corridas individuales, no un benchmark controlado |
| H5 | En local, trazas y auditoría solo aparecen si corre un worker (`composer dev` o `php artisan queue:work`) | Informativo | Documentado en `docs/monitoreo/auditoria.md` §3 |

## 6. CI/CD — VALIDADO

| Workflow | Ejecución | Resultado |
|---|---|---|
| CI — BibliotecaOnline | Push a `develop` del merge #175 (`30eca26`) | ✅ |
| Seguridad — Snyk | Push a `develop` del merge #175 | ✅ |
| Liberación y despliegue | PR #175 (primera imagen con APCu) | ✅ (6 min 50 s) |
| PHPUnit / Vitest / Playwright | Locales y dentro del pipeline | ✅ (ver §2) |

Captura: `evidencias/validacion-github-actions.png`. Este PR vuelve a correr los 3 workflows con la
prueba de integración y el servicio `scheduler`.

## 7. Evidencia (`evidencias/`)

| Captura | Qué muestra |
|---|---|
| `validacion-grafana.png` | Dashboard con tráfico, incidente de BD (5xx) y recuperación |
| `validacion-prometheus-alertas.png` | `ALERTS{alertstate="firing"}`: intervalo en que `BaseDeDatosCaida` estuvo disparada |
| `validacion-trazabilidad-listado.png` | Visor `/admin/trazas` con trazas reales |
| `validacion-trazabilidad-detalle.png` | Traza de `superadmin.toggleEstado` buscada por su `trace_id`, con el detalle desplegado |
| `validacion-auditoria.png` | La misma acción en `/admin/logs`: "El usuario Superadmin Sistema activó la cuenta de Usuario Prueba" |
| `validacion-github-actions.png` | Workflows en verde |

La evidencia individual de cada módulo está en `monitoreo.md` (#165), `trazabilidad.md` (#166) y
`auditoria.md` (#167).

## 8. Cómo repetir la validación

```bash
scripts/prepare-release.sh v-local && scripts/deploy.sh v-local
scripts/monitoring.sh up
scripts/validar-integracion.sh                  # 13 verificaciones → dist/validacion/reporte.md
RUN_E2E=true RUN_K6=true scripts/test-release.sh
php artisan test --filter=IntegracionObservabilidadTest
```

## 9. Criterios del issue

| Criterio | Evidencia |
|---|---|
| Los tres módulos funcionan | §2, §3 (13/13), §7 |
| Las pruebas automatizadas pasan | 177 PHPUnit, 18 Vitest, 12 E2E |
| CI permanece en verde | §6 |
| Las evidencias están conservadas | `docs/monitoreo/evidencias/validacion-*.png` |
| No existen errores críticos conocidos | §5: ningún hallazgo crítico; H2 corregido, H1 documentado con recomendación |
