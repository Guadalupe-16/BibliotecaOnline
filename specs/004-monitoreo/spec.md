# Feature Specification: Monitoreo de la aplicación

**Feature Branch**: `feat/165-monitoreo-prometheus-grafana` (implementación, Issue #165)

**Created**: 2026-09-27

**Status**: Implementado (Issue #165) — planeada en el Issue #164

**Input**: Actividad 3.1: diseñar con SDD el módulo de monitoreo de BibliotecaOnline (métricas,
dashboard y alertas), eligiendo herramienta entre Nagios, Zabbix, Prometheus + Grafana y Datadog. Este
Issue **no** instala ni implementa nada; solo produce spec, plan y tasks.

**Issue**: #164 (planeación). La implementación se abrirá en un Issue nuevo que enlace este spec.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Ver el estado general de la aplicación (Priority: P1)

Como integrante del equipo responsable de operar BibliotecaOnline, quiero un dashboard que muestre si
la aplicación está disponible y cómo responde, para detectar un problema sin tener que revisar logs a
mano.

**Why this priority**: Es el requisito mínimo de la actividad ("monitorear la aplicación"); sin esto no
hay módulo de monitoreo.

**Independent Test**: Con el stack de monitoreo desplegado (spec futura de implementación), abrir el
dashboard de Grafana y ver paneles con datos reales de una petición reciente.

**Acceptance Scenarios**:

1. **Given** la aplicación recibe tráfico, **When** se abre el dashboard, **Then** se ven solicitudes
   por minuto, latencia (promedio y p95), tasa de errores y el estado de `/up` de las últimas horas.
2. **Given** la aplicación deja de responder, **When** Prometheus intenta scrapear `/metrics` o `/up`,
   **Then** el dashboard refleja la caída (`up == 0`) dentro del intervalo de scrape configurado.

---

### User Story 2 - Recibir una alerta ante un problema real (Priority: P2)

Como responsable de operar la aplicación, quiero recibir una alerta cuando la aplicación no responde o
la tasa de errores sube, para reaccionar antes de que un usuario lo reporte.

**Why this priority**: Un dashboard que nadie mira no evita incidentes; las alertas son lo que la
actividad pide como parte de "monitoreo activo". Depende de que existan las métricas (historia 1).

**Independent Test**: Provocar (en un entorno de prueba) que `/up` deje de responder o generar tráfico
con errores 5xx y verificar que la regla de alerta pasa a estado `firing`.

**Acceptance Scenarios**:

1. **Given** `/up` no responde durante el tiempo configurado en la alerta, **When** se evalúa la regla,
   **Then** la alerta cambia a estado de disparo.
2. **Given** la tasa de respuestas 5xx supera el umbral acordado por el equipo, **When** se evalúa la
   regla, **Then** la alerta se dispara y queda visible en el dashboard/alertmanager.
3. **Given** ninguna condición de alerta se cumple, **When** se evalúa cualquier regla, **Then**
   ninguna alerta está en estado de disparo (no hay falsos positivos permanentes).

---

### User Story 3 - No exponer información sensible por monitoreo (Priority: P1)

Como responsable de seguridad del proyecto, quiero que el endpoint de métricas no filtre datos de
usuarios ni secretos, para que agregar monitoreo no cree una nueva superficie de fuga de datos.

**Why this priority**: Principio VI de la constitución (seguridad por rol, sin secretos en el
repositorio ni expuestos); es una condición de aceptación, no una mejora opcional.

**Independent Test**: Revisar la salida de `/metrics` (o su diseño, en esta fase de planeación) y
confirmar que ninguna métrica usa como *label* un ID de usuario, una IP, una URL con parámetros o un
valor de configuración.

**Acceptance Scenarios**:

1. **Given** la definición de métricas de esta spec, **When** se audita cada `label`, **Then** ninguno
   contiene datos personales ni secretos (solo nombre de ruta, método HTTP y código de estado).
2. **Given** el endpoint `/metrics` propuesto, **When** se documenta su acceso, **Then** queda explícito
   que no requiere ni expone sesión de usuario de la aplicación.

### Edge Cases

- **Alta cardinalidad**: si las métricas usaran la URL completa (con IDs de libro, por ejemplo) en vez
  del nombre de ruta, Prometheus acumularía series sin límite; se define con nombre de ruta.
- **Aplicación caída completa**: si el contenedor no responde, Prometheus no puede ni scrapear
  `/metrics`; la alerta de disponibilidad se basa en el resultado del scrape (`up`), no solo en el
  contenido de la métrica.
- **Entorno sin stack de monitoreo desplegado** (caso actual): el dashboard y las alertas no existen
  todavía; esta spec se valida por revisión de diseño, no por evidencia de ejecución (ver Assumptions).

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: La aplicación MUST poder exponer un endpoint de métricas en formato Prometheus (texto
  plano) sin requerir sesión de usuario autenticado.
- **FR-002**: El diseño MUST incluir, como mínimo, contador de solicitudes HTTP por ruta/método/estado,
  histograma de duración de solicitudes, contador de errores 4xx y 5xx, disponibilidad vía `/up`, y
  contadores de trabajos de cola procesados y fallidos.
- **FR-003**: Ninguna métrica MUST usar como *label* datos personales (IP, user-agent completo, ID de
  usuario) ni URLs con parámetros; se usa el nombre de ruta.
- **FR-004**: El dashboard propuesto MUST mostrar, como mínimo: solicitudes por minuto, errores,
  latencia promedio, p95 y disponibilidad.
- **FR-005**: El diseño MUST definir al menos una alerta verificable con su condición, ventana de
  evaluación y umbral, y MUST declarar si el umbral viene de un requisito de la actividad (como NS-1) o
  es una decisión del equipo.
- **FR-006**: El dashboard operativo MUST vivir en Grafana, no como una vista de Laravel; el spec MUST
  documentar dónde se consulta (URL/puerto del stack de monitoreo cuando exista).
- **FR-007**: La spec MUST justificar la elección de Prometheus + Grafana frente a Nagios, Zabbix y
  Datadog (ver `research.md`).
- **FR-008**: Esta spec MUST NOT resultar en instalación de Prometheus, Grafana, ni cambios de código;
  es solo diseño (Issue #164).

### Key Entities

- **Métrica**: nombre, tipo (counter/histogram/gauge), labels permitidos, fuente de datos.
- **Dashboard**: conjunto de paneles de Grafana que consultan las métricas anteriores.
- **Alerta**: nombre, expresión (PromQL propuesto), umbral, ventana, severidad, acción esperada.
- **Endpoint `/metrics`**: ruta HTTP que expone las métricas en formato Prometheus.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: El spec, el plan y las tasks de este módulo existen en `specs/004-monitoreo/` y no
  contienen instrucciones de implementación ya ejecutadas (todas las tareas de `tasks.md` están
  pendientes).
- **SC-002**: La elección de herramienta está justificada por escrito contra las 3 alternativas
  restantes de la actividad (`research.md`).
- **SC-003**: Cada métrica propuesta tiene una fuente de datos identificada en el repositorio actual
  (código, tabla o mecanismo existente) o está marcada como dependiente de una librería a evaluar en la
  implementación.
- **SC-004**: 0 labels de métricas propuestas contienen datos personales o secretos (auditable por
  lectura del documento).
- **SC-005**: Al menos 1 alerta queda completamente especificada (expresión, umbral, ventana).

## Assumptions

- No existe hoy ningún endpoint de métricas, agente de monitoreo ni stack de Prometheus/Grafana en el
  repositorio; todo lo descrito aquí es PLANEADO.
- El entorno de despliegue actual (`docker-compose.release.yml`) es efímero (vive lo que dura el job de
  CI); el stack de monitoreo permanente queda fuera de alcance de este Issue y se implementará cuando
  exista un entorno de larga duración (ver `docs/cicd/pipeline-liberacion-despliegue.md` §13).
- Se reutiliza el umbral NS-1 (`p95 < 5000 ms`) ya acordado por el equipo como base de la alerta de
  latencia, en vez de definir un segundo umbral que pueda contradecirlo.
- La librería concreta para exponer métricas desde Laravel/PHP se decide y justifica en el
  `research.md` de la spec de implementación, no en esta planeación.
- Los roles que pueden ver el dashboard son un tema de acceso a Grafana (usuario/contraseña de Grafana
  o SSO), no del sistema de roles de BibliotecaOnline (`usuario`/`admin`/`superadmin`); se documenta
  como decisión pendiente del equipo en el plan.
