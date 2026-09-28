# Planeación SDD — Actividad 3.1 (monitoreo, trazabilidad y auditoría)

> Issue: #164 · Fecha: 2026-09-27 · Autora: Guadalupe Amavizca Quinter

Estado de este documento: **PLANEACIÓN**. No describe código implementado; resume las tres specs
`specs/004-monitoreo/`, `specs/005-trazabilidad/` y `specs/006-auditoria/`, todas en estado *Draft*.

## 1. Objetivo

La Actividad 3.1 pide diseñar, para BibliotecaOnline, tres capacidades operativas:

1. **Monitoreo**: saber si la aplicación está disponible y cómo responde (latencia, errores, colas).
2. **Trazabilidad**: poder reconstruir qué pasó con una petición HTTP concreta (ruta, duración,
   resultado, error).
3. **Auditoría**: saber quién hizo qué acción de negocio, cuándo y desde dónde.

Este Issue (#164) es exclusivamente de planeación: produce spec, plan y tasks para los tres módulos,
sin instalar Prometheus/Grafana, sin crear tablas nuevas, sin tocar vistas ni lógica del visor de
auditoría existente. La implementación de cada módulo se abre en un Issue separado que enlace su spec.

## 2. Justificación de SDD

BibliotecaOnline ya usa [GitHub Spec Kit](https://github.com/github/spec-kit) para todo requerimiento
significativo (`specs/README.md`, `.specify/memory/constitution.md`). El flujo es:

```text
Issue → /speckit-specify → /speckit-clarify → /speckit-plan → /speckit-tasks
      → rama → implementación → pruebas → /speckit-analyze → PR → CI → revisión → merge
```

Se sigue el mismo flujo aquí por dos razones:

- **Consistencia**: las tres specs anteriores (001, 002, 003) ya viven en `specs/`; un cuarto, quinto y
  sexto módulo con una estructura distinta rompería la convención que el equipo ya sigue.
- **Trazabilidad del propio proceso** (principio II de la constitución): cada módulo futuro podrá
  enlazar Issue → spec → plan → tasks → código → pruebas → PR, igual que el resto del proyecto.

Necesidad → spec (qué y por qué) → plan (cómo, con qué se integra) → tasks (pasos verificables) →
implementación → pruebas → PR: ese orden evita que monitoreo, trazabilidad o auditoría se implementen
"a ojo" sin haber decidido antes qué se audita, qué se traza y qué se mide.

## 3. Estado actual del sistema (verificado, no supuesto)

| Pieza | Estado |
|---|---|
| Stack | Laravel 12, Livewire 4, Alpine.js, Tailwind 4, Vite — TALL stack (`README.md`) |
| CI | `.github/workflows/ci.yml`: `lint-format`, `php-tests`, `e2e-playwright` (Playwright) |
| Liberación y despliegue | `.github/workflows/release-deploy.yml` + `Dockerfile` + `docker-compose.release.yml` (Issue #156, **VALIDADO** local y en GitHub Actions, `docs/cicd/pipeline-liberacion-despliegue.md`) |
| Health check | `/up` — ruta nativa de Laravel 12 (`bootstrap/app.php`, `health: '/up'`); ya la usan el CI (espera antes de correr E2E) y el `HEALTHCHECK` del `Dockerfile` |
| Monitoreo | **No existe** ningún endpoint de métricas ni stack de Prometheus/Grafana. El propio documento del pipeline de liberación ya lo marca como PLANEADO (`docs/cicd/pipeline-liberacion-despliegue.md` §6) |
| Trazabilidad técnica | **No existe** ningún middleware ni tabla de trazas de petición |
| Auditoría | **Existe parcialmente**: `ActivityLog` + `LogActivityJob` + visor Livewire (`ActivityLogger`) en `/admin/logs`, protegido por `role:admin,superadmin`; cubre solo 4 acciones (`login`, `logout`, `catalogo`, `busqueda`) y no tiene ninguna prueba automatizada |

## 4. Módulo 004 — Monitoreo

- **Propósito**: dashboard y alertas sobre el estado de la aplicación.
- **Herramienta propuesta**: Prometheus + Grafana, justificada frente a Nagios, Zabbix y Datadog en
  [`specs/004-monitoreo/research.md`](../../specs/004-monitoreo/research.md) — principalmente porque no
  requiere agente adicional en el host ni credenciales de un tercero, y encaja con un simple endpoint
  `GET /metrics`.
- **Métricas**: solicitudes HTTP por ruta/método/estado, duración (para p95), errores 4xx/5xx,
  disponibilidad (`up` vía `/up`), trabajos de cola procesados/fallidos (`jobs`/`failed_jobs`, ya
  existentes con `QUEUE_CONNECTION=database`).
- **Alertas**: al menos disponibilidad caída y latencia p95 sobre el umbral **NS-1 ya acordado por el
  equipo** (`p95 < 5000 ms`, `docs/cicd/pipeline-liberacion-despliegue.md` §5) — se reutiliza en vez de
  inventar un segundo umbral.
- **Dependencia futura**: elegir y validar la librería cliente de Prometheus para PHP/Laravel; ninguna
  se agrega en este Issue.

## 5. Módulo 005 — Trazabilidad

- **Propósito**: reconstruir qué pasó con una petición HTTP concreta.
- **Diferencia con auditoría**: la trazabilidad responde por la petición en sí (ruta, duración, código
  de estado, error), sin importar si esa petición correspondía a una acción de negocio auditable; la
  auditoría responde por la acción de negocio (quién hizo qué), sin datos técnicos de la petición. No
  se fusionan ambas tablas (`specs/005-trazabilidad/research.md` §1).
- **Datos**: `trace_id` (UUID propio), método, ruta (nombre, no URL cruda), usuario si existe, IP,
  código de estado, duración en ms, resultado, referencia de error. Explícitamente **no** se guardan
  contraseñas, tokens, cookies ni cuerpos de petición/respuesta.
- **Visor**: listado paginado, filtros (ruta, resultado, fechas), detalle — protegido para
  `admin`/`superadmin`, mismo patrón de acceso que el visor de auditoría.
- **Seguridad y sostenibilidad**: persistencia asíncrona (mismo patrón de cola que ya usa
  `LogActivityJob`, para no añadir latencia a cada petición) y retención automática por antigüedad
  (evita crecimiento sin límite, a diferencia de `activity_logs`, que solo crece con acciones de
  negocio).

## 6. Módulo 006 — Auditoría

- **Funcionalidad existente**: `ActivityLog` + `LogActivityJob` + `ActivityLogger` (Livewire) +
  `/admin/logs`, ya protegida por rol; cubre login, logout, visita al catálogo y búsqueda en Open
  Library.
- **Qué se reutiliza**: todo — modelo, job, tabla, visor y ruta. No se propone una segunda tabla ni un
  segundo mecanismo (constitución, principio de simplicidad).
- **Qué se mejora**: se identifican 5 acciones administrativas hoy no auditadas —cambios de rol
  (`RbacController`, `SuperAdminController`), activar/desactivar usuario, editar/eliminar usuario
  (`UsuarioController`)— con el punto exacto de código donde agregar `ActivityLog::registrar()`
  (`specs/006-auditoria/plan.md`). También se cierra una brecha real: **hoy no existe ninguna prueba
  automatizada** del modelo, del job, del visor ni del control de acceso; esta spec la incluye como
  prioridad P1, antes que ampliar la cobertura de acciones.

## 7. Dependencias entre módulos

- Los tres módulos dependen de esta planeación (Issue #164) como punto de partida común.
- **Monitoreo, trazabilidad y auditoría pueden implementarse en paralelo**: no hay una relación de
  bloqueo entre sus Issues de implementación. La única coordinación sugerida es de diseño, no de
  código: si monitoreo (004) y trazabilidad (005) terminan compartiendo el mismo middleware de
  instrumentación para no medir dos veces cada petición, esa decisión se toma dentro de la
  implementación de cualquiera de las dos, documentada en su propio `plan.md` (`Riesgos`), sin que una
  espere a la otra para empezar.
- Ninguno de los tres depende del código sin mergear de otro; auditoría (006) en particular es
  independiente de los otros dos porque reutiliza infraestructura que ya existe en `develop`.

## 8. Estrategia de PR

```text
PR de planeación (este Issue, #164)
  → PR de implementación de monitoreo (004)
  → PR de implementación de trazabilidad (005)
  → PR de implementación de auditoría (006)
  → PR de validación integrada (confirma que los tres conviven: rutas, navegación, permisos,
     y que ninguno instrumenta dos veces la misma petición sin que el equipo lo haya decidido así)
```

Los tres PRs de implementación pueden abrirse y avanzar en paralelo (sección 7); el PR de validación
integrada se abre al final, cuando al menos dos de los tres estén mergeados a `develop`.

## 9. Criterios generales de aceptación

- Existen `specs/004-monitoreo/`, `specs/005-trazabilidad/` y `specs/006-auditoria/`, cada una con
  `spec.md`, `plan.md` y `tasks.md` (además de `research.md`/`data-model.md` donde aporta valor real).
- Ninguna tarea de ningún `tasks.md` aparece marcada como completada: este Issue no implementa código.
- No hay contradicciones entre los tres módulos: auditoría no duplica campos técnicos de trazabilidad
  (spec 006, FR-007); trazabilidad no duplica campos de negocio de auditoría (spec 005, FR-008);
  monitoreo no depende de que trazabilidad exista para funcionar (métricas agregadas vs. trazas
  individuales son mecanismos independientes, aunque puedan compartir middleware).
- `specs/README.md` lista los tres módulos nuevos con su estado ("Especificados y planeados").
- Ningún archivo de código de la aplicación (`app/`, `routes/`, `database/migrations/`,
  `resources/views/`), configuración de CI/CD, `composer.json` ni `package.json` fue modificado por
  este Issue.

## 10. Riesgos y decisiones

| Riesgo / decisión abierta | Dónde se resuelve |
|---|---|
| Elegir la librería cliente de Prometheus para PHP/Laravel | `specs/004-monitoreo/research.md` de la implementación (Issue futuro) |
| Compartir o no un único middleware entre monitoreo (métricas) y trazabilidad (trazas por petición) | `plan.md` de la implementación que se haga primero; no bloquea a la otra |
| Umbral de retención de `request_traces` (propuesto 30 días) | Confirmación de equipo en `specs/005-trazabilidad/tasks.md` T001 |
| Incluir `FavoritoController`/`PerfilController` en la auditoría ampliada | Confirmación de equipo en `specs/006-auditoria/tasks.md` T001 |
| Acceso a Grafana (usuario/SSO) vs. roles de la aplicación (`usuario`/`admin`/`superadmin`) | Decisión pendiente, documentada en `specs/004-monitoreo/spec.md` Assumptions |
| Stack de Prometheus/Grafana en un entorno permanente (hoy el entorno de liberación es efímero) | Fuera de alcance hasta que exista hosting permanente (`docs/cicd/pipeline-liberacion-despliegue.md` §13) |
