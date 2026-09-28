# Feature Specification: Visor de trazabilidad técnica de solicitudes

**Feature Branch**: Por definir (se asignará `tipo/NNN-descripcion` al crear el Issue de implementación)

**Created**: 2026-09-27

**Status**: Draft (planeación — Issue #164)

**Input**: Actividad 3.1: diseñar con SDD un sistema de trazabilidad técnica de solicitudes (qué
petición ocurrió, por qué ruta, cuánto tardó, qué respondió, si falló) y su visor administrativo,
diferenciado de la auditoría de negocio ya existente (`ActivityLog`, spec 006). Este Issue **no**
implementa middleware, tablas ni vistas; solo produce spec, plan y tasks.

**Issue**: #164 (planeación). La implementación se abrirá en un Issue nuevo que enlace este spec.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Diagnosticar una petición lenta o fallida (Priority: P1)

Como integrante del equipo que da soporte técnico, quiero poder buscar qué pasó con una petición
específica (por ruta, fecha o resultado), para diagnosticar un problema reportado sin depender de logs
de texto plano.

**Why this priority**: Es el valor central de la trazabilidad; sin un visor buscable, los datos
capturados no son útiles operativamente.

**Independent Test**: Con el módulo implementado, generar una petición que falle (p. ej. una ruta
inexistente) y encontrarla en el visor filtrando por resultado "error".

**Acceptance Scenarios**:

1. **Given** varias peticiones registradas, **When** se filtra el visor por rango de fechas, **Then**
   solo se muestran las trazas de ese rango, paginadas.
2. **Given** una petición que respondió con error 5xx, **When** se busca en el visor, **Then** aparece
   con su `trace_id`, ruta, duración, código de estado y la referencia de error.
3. **Given** una traza en el listado, **When** se abre su detalle, **Then** se ven todos sus campos sin
   necesidad de consultar la base de datos a mano.

---

### User Story 2 - Acceso restringido al visor (Priority: P1)

Como responsable de seguridad, quiero que solo personal autorizado pueda ver las trazas (que incluyen
IPs y rutas visitadas por usuarios), para no exponer patrones de uso a cualquier usuario autenticado.

**Why this priority**: Principio VI de la constitución (seguridad por rol); condición de aceptación, no
mejora posterior.

**Independent Test**: Intentar acceder a la ruta del visor con una cuenta de rol `usuario` y confirmar
`403`; con `admin` o `superadmin`, confirmar acceso.

**Acceptance Scenarios**:

1. **Given** un usuario sin sesión, **When** intenta entrar al visor, **Then** se le redirige a login
   (igual que el resto de rutas protegidas por `auth`).
2. **Given** un usuario con rol `usuario`, **When** intenta entrar al visor, **Then** recibe `403`.
3. **Given** un usuario con rol `admin` o `superadmin`, **When** entra al visor, **Then** puede listar,
   filtrar y ver el detalle de las trazas.

---

### User Story 3 - La trazabilidad no degrada el sistema (Priority: P1)

Como responsable de operar la aplicación, quiero que capturar trazas no haga más lenta cada petición ni
llene la base de datos sin control, para que el módulo sea sostenible en producción.

**Why this priority**: Un sistema de trazabilidad que ralentiza la app o crece sin límite es peor que no
tenerlo; es una condición de aceptación técnica, no una optimización opcional.

**Independent Test**: Revisar el diseño de escritura asíncrona (cola) y la estrategia de retención antes
de aprobar el plan de implementación.

**Acceptance Scenarios**:

1. **Given** el diseño de esta spec, **When** se revisa cómo se persiste una traza, **Then** la
   escritura ocurre después de responder al cliente (asíncrona), no en el camino crítico de la petición.
2. **Given** el diseño, **When** se revisa el crecimiento de la tabla, **Then** existe una estrategia de
   retención/poda documentada con su justificación.
3. **Given** el diseño, **When** se revisa qué campos se guardan, **Then** ninguno es una contraseña,
   token, cookie o cuerpo de petición/respuesta.

### Edge Cases

- **Petición que nunca llega a `terminate()`** (p. ej. el proceso PHP muere a medio camino): esa traza
  se pierde; se documenta como limitación aceptada, no se agrega un mecanismo de journaling adicional.
- **URL que no coincide con ninguna ruta definida** (404 "ruta no encontrada"): nunca entra al pipeline
  de middleware, así que no genera traza — a diferencia de una ruta que sí existe mediante *route model
  binding* pero cuyo recurso no existe (p. ej. `/libros/999999`), que sí se traza con `status_http =
  404` (verificado en la implementación, Issue #166:
  `tests/Feature/TrazabilidadMiddlewareTest.php::test_status_http_se_registra_para_un_recurso_inexistente`).
- **Usuario anónimo**: `user_id` queda `null`; el visor debe poder filtrar igualmente por ese caso.
- **Traza sin `error_referencia`** (petición exitosa): el campo queda `null`; el visor no debe mostrarlo
  como si fuera un error.
- **Volumen alto de peticiones**: sin retención, la tabla y el visor se vuelven lentos; cubierto por
  FR-009 y `research.md` §5.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: El sistema MUST registrar, por cada petición HTTP a rutas `web` (excluyendo `/metrics` y
  `/up`), un identificador de traza, método, ruta, usuario (si existe sesión), IP, código de estado,
  duración en milisegundos y resultado (`ok`/`error`).
- **FR-002**: El sistema MUST registrar una referencia de error (clase + mensaje truncado) cuando el
  resultado sea `error`, sin incluir el stack trace completo ni datos sensibles del request.
- **FR-003**: El sistema MUST NOT registrar contraseñas, tokens, cookies ni el cuerpo de la petición o
  de la respuesta.
- **FR-004**: La escritura de cada traza MUST ocurrir de forma asíncrona (cola), después de responder
  al cliente, para no añadir latencia perceptible a la petición.
- **FR-005**: El sistema MUST definir una estrategia de retención (borrado de trazas más antiguas que un
  umbral configurable) para evitar crecimiento sin límite.
- **FR-006**: El visor MUST estar protegido para roles `admin` y `superadmin` únicamente, igual que el
  resto de vistas administrativas existentes (`/admin/logs`).
- **FR-007**: El visor MUST ofrecer listado paginado, búsqueda/filtros (por ruta, resultado, rango de
  fechas, y opcionalmente usuario) y una vista de detalle por traza.
- **FR-008**: El diseño MUST diferenciarse explícitamente de la auditoría de negocio (spec 006): no
  debe registrar `accion`/`descripcion` de negocio ni sustituir a `ActivityLog`.
- **FR-009**: Esta spec MUST NOT resultar en la creación de la tabla, el middleware ni el visor en este
  Issue; es solo diseño (Issue #164).

### Key Entities

- **RequestTrace**: ver `data-model.md`.
- **Middleware de trazabilidad**: componente que captura los datos de la petición y despacha el job de
  persistencia.
- **Visor de trazabilidad**: componente Livewire administrativo (listado, filtros, detalle), análogo en
  patrón a `ActivityLogger` pero sobre datos distintos.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: El spec, el plan y las tasks de este módulo existen en `specs/005-trazabilidad/` y todas
  las tareas de `tasks.md` están pendientes (nada implementado en este Issue).
- **SC-002**: El `data-model.md` no repite ningún campo de negocio de `ActivityLog` (`accion`,
  `descripcion`) y documenta explícitamente qué campos NO se guardan.
- **SC-003**: El diseño de persistencia asíncrona queda documentado con el mismo patrón (cola) que ya
  usa `LogActivityJob`, sin proponer escritura síncrona.
- **SC-004**: Existe una estrategia de retención con un umbral propuesto y su justificación.
- **SC-005**: El acceso al visor queda definido para los mismos roles (`admin`, `superadmin`) que
  protegen `/admin/logs` hoy, sin contradecir el principio VI de la constitución.

## Assumptions

- No existe hoy ningún middleware, tabla ni vista de trazabilidad técnica en el repositorio; todo lo
  descrito aquí es PLANEADO.
- Se reutiliza el mecanismo de cola ya configurado (`QUEUE_CONNECTION=database`) para la persistencia
  asíncrona, en vez de introducir un driver de cola nuevo.
- El identificador de traza es un UUID generado por la aplicación (no depende de un proxy externo que
  hoy no existe); ver `research.md` §2.
- El umbral de retención (propuesto 30 días) es una decisión de equipo a confirmar en la implementación,
  no un requisito de la actividad.
- El campo `user_agent` es opcional y su inclusión final se decide en la implementación según el
  volumen adicional que represente (`research.md` §6).
- Si la spec 004 (monitoreo) y esta spec comparten instrumentación de middleware, la decisión de
  fusionar o mantener separados ambos mecanismos se toma en la fase de implementación (ver plan.md
  Riesgos de la spec 004), sin que una bloquee a la otra.
