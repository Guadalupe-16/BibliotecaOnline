# Research: Monitoreo de la aplicación

**Spec**: [spec.md](spec.md) | **Issue**: #164 (planeación) — implementación en Issue separado

## 1. Herramienta de monitoreo: alternativas evaluadas

La Actividad 3.1 propone comparar Nagios, Zabbix, Prometheus + Grafana y Datadog.

| Herramienta | Modelo | Costo | Encaje con BibliotecaOnline |
|---|---|---|---|
| **Nagios** | Chequeos activos (plugins que se ejecutan periódicamente), pensado para disponibilidad de host/servicio | Núcleo libre, plugins y UI moderna de pago | Bueno para "¿está vivo el servidor?", pobre para series de tiempo (latencia, p95, tasas de error) sin plugins adicionales; no hay stack de dashboards nativo comparable a Grafana |
| **Zabbix** | Agente + servidor, series de tiempo propias, alertas | Libre (self-hosted) | Requiere agente o checks SNMP/HTTP; su modelo de métricas de aplicación (vía Zabbix Sender/API) es más pesado de integrar en un endpoint HTTP que un simple `/metrics` |
| **Prometheus + Grafana** | *Pull*: Prometheus scrapea un endpoint `/metrics` en texto plano; Grafana consulta Prometheus para dashboards y alertas | Libre (self-hosted), sin licencia | Encaja con una app Laravel: expone `/metrics` con un contador/histograma por librería PHP; no exige agente en el host; ya hay precedente en el repo (`docs/cicd/pipeline-liberacion-despliegue.md` §6 ya lo menciona como PLANEADO) |
| **Datadog** | SaaS con agente propietario | Pago (por host/métrica), requiere cuenta y clave de API externa | Introduce un secreto/API key de un tercero y costo recurrente; el proyecto es un ejercicio académico sin presupuesto ni servidor permanente (ver `docs/planeacion/estrategia-despliegue.md`) |

### Decisión

**Prometheus + Grafana**, por:

1. No requiere cuenta externa ni credenciales de un tercero (Restricción de la constitución: "no se guardan secretos... en el repositorio").
2. Modelo *pull* sobre un endpoint HTTP: se implementa como una ruta más de Laravel, sin agente adicional en el contenedor `app` (el mismo patrón que ya usa `HEALTHCHECK` sobre `/up` en el `Dockerfile`).
3. Ya está mencionado como la opción PLANEADO en `docs/cicd/pipeline-liberacion-despliegue.md` §6; esta spec la formaliza en vez de introducir una tercera alternativa.
4. Grafana permite construir el dashboard de métricas sin escribir una vista de Laravel (principio de simplicidad: no duplicar UI que Grafana ya resuelve).

Nagios y Zabbix se descartan por no encajar con un modelo de métricas de aplicación (series de tiempo con p95) sin trabajo adicional equivalente. Datadog se descarta por costo y por depender de un servicio externo con credenciales.

## 2. Cómo se expondrían las métricas (propuesto, no implementado)

Laravel no trae un exportador de Prometheus. La opción evaluada es una librería madura de la comunidad
(p. ej. `promphp/prometheus_client_php`, usada por paquetes como `spatie/laravel-prometheus-exporter` o
equivalentes mantenidos para Laravel 12) que:

- Registra contadores/histogramas en memoria de la petición y los persiste entre peticiones con un
  *storage adapter* (APCu o Redis; **no** archivo en disco, que no es seguro entre workers de Apache).
- Expone una ruta `GET /metrics` que Prometheus scrapea cada N segundos.

**No se agrega ninguna dependencia en este Issue.** La elección final de librería y su justificación
detallada (con benchmark de overhead) se registra en el `research.md` de la implementación, según la
restricción de la constitución ("una tecnología nueva se justifica en el research.md de su spec").

## 3. Qué métricas son razonables hoy

Basado en lo que la app ya expone o puede exponer sin rediseño:

| Métrica propuesta | Tipo Prometheus | De dónde sale |
|---|---|---|
| `http_requests_total{method,route,status}` | Counter | Middleware nuevo (o el mismo que registre trazabilidad, spec 005, si se decide compartir instrumentación) |
| `http_request_duration_seconds{route}` | Histogram | Igual que arriba; permite calcular p95 en Grafana con `histogram_quantile` |
| `http_requests_errors_total{status}` | Counter (derivado o con label `status` filtrable) | Mismo middleware, separando 4xx/5xx |
| `up` (self, vía `HEALTHCHECK`/`/up`) | El propio *scrape* de Prometheus ya produce `up{job="bibliotecaonline"}` | Prometheus, no requiere código nuevo |
| `queue_jobs_processed_total` / `queue_jobs_failed_total` | Counter | Tablas `jobs` / `failed_jobs` (driver `database`, ya en uso) vía un comando `artisan` que las lea, o eventos `JobProcessed`/`JobFailed` de Laravel |

Se descarta prometer métricas de negocio (préstamos, usuarios activos) en este Issue: no hay tablas ni
eventos hoy que las produzcan sin diseño adicional; quedarían para una spec futura si se solicitan.

## 4. Seguridad del endpoint `/metrics`

- No debe requerir sesión de Laravel (Prometheus no maneja cookies), pero tampoco debe ser público sin
  control: se protege por red (el `docker-compose` de liberación no publica el puerto de `/metrics` al
  host, o se usa un `ipWhitelist`/`role` a nivel de proxy) — decisión final en el plan de implementación.
- Las etiquetas (`labels`) de las métricas **no** deben incluir IDs de usuario, IPs ni rutas con
  parámetros crudos (alta cardinalidad y datos personales); se usa el nombre de ruta (`route()->getName()`),
  no la URL completa.
- Nunca se exponen variables de entorno, tokens ni valores de `.env` en `/metrics`.

## 5. Umbrales de alerta

Se reutiliza el nivel de servicio **NS-1** ya acordado por el equipo en
`docs/cicd/pipeline-liberacion-despliegue.md` §5 (**p95 de `http_req_duration` < 5000 ms**) como base de
la alerta de latencia, para no definir un segundo umbral arbitrario que contradiga al que ya valida k6.
El resto de los umbrales (tasa de 5xx, caída de `/up`) son decisiones de equipo, declaradas como tales en
`spec.md`.
