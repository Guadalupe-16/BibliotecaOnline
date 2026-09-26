# Feature Specification: Infraestructura CI/CD — pruebas E2E en CI y entorno reproducible

**Feature Branch**: `ci/138-e2e-devcontainer-spec-003`

**Created**: 2026-09-25

**Status**: Draft

**Input**: User description: "Infraestructura CI/CD de BibliotecaOnline (Issue #138). Formalizar un
entorno de desarrollo reproducible y ampliar el CI para ejecutar las pruebas E2E de Playwright sin
romper los jobs actuales (lint-format y php-tests). Alcance: job e2e-playwright, devcontainer para
Codespaces y propuesta de Terraform solo como diseño. Cada elemento se etiqueta IMPLEMENTADO,
VALIDADO o PLANEADO."

**Issue**: #138

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Las pruebas de navegador se ejecutan en cada Pull Request (Priority: P1)

Como integrante del equipo que abre un Pull Request hacia `develop` o `main`, quiero que las pruebas
de extremo a extremo (flujos reales en un navegador) se ejecuten automáticamente junto con las
verificaciones existentes, para saber antes del merge si mi cambio rompió algún flujo visible para
el usuario (login, catálogo, búsqueda, navegación).

**Why this priority**: Hoy las pruebas E2E (14: 12 de la aplicación y 2 de la plantilla de Playwright) solo corren en la máquina de quien las ejecute; un
cambio puede romper un flujo y llegar a `develop` sin que nadie lo note. Es el objetivo principal
del Issue #138 y el que exige evidencia de un run real.

**Independent Test**: Abrir un PR hacia `develop`; en la pestaña de checks aparece un tercer check
de pruebas E2E que termina en verde y ofrece el reporte descargable.

**Acceptance Scenarios**:

1. **Given** un PR hacia `develop` sin errores, **When** se ejecuta el CI, **Then** el check de
   pruebas E2E termina en verde y los checks de lint/formato y de PHPUnit también.
2. **Given** un cambio que rompe un flujo cubierto por una prueba E2E, **When** se ejecuta el CI,
   **Then** el check E2E falla y el reporte identifica la prueba y el paso que fallaron.
3. **Given** una ejecución terminada (verde o roja), **When** un revisor abre el run, **Then** puede
   descargar el reporte HTML de las pruebas como artefacto.
4. **Given** que la aplicación tarda en arrancar, **When** el CI espera el servidor, **Then** la
   espera termina en cuanto la aplicación responde sano (no en un tiempo fijo) y falla con un
   mensaje claro si no responde dentro del límite.

---

### User Story 2 - Entorno de desarrollo listo en un clic (Priority: P2)

Como integrante nuevo o que cambia de equipo, quiero abrir el repositorio en un entorno
preconfigurado (GitHub Codespaces o contenedor local) que ya tenga todas las herramientas
necesarias, para poder ejecutar la aplicación y todas las suites de prueba sin instalar nada a mano.

**Why this priority**: Reduce el tiempo de arranque y las diferencias entre máquinas ("en la mía sí
funciona"), pero no bloquea la detección de errores, que es lo que aporta la historia 1.

**Independent Test**: Crear un Codespace (o abrir el contenedor en VS Code) desde la rama; al
terminar la preparación automática, ejecutar la aplicación y las pruebas sin pasos manuales
adicionales salvo los documentados.

**Acceptance Scenarios**:

1. **Given** un entorno recién creado, **When** termina la preparación automática, **Then** las
   dependencias están instaladas, existe una configuración local válida y la base de datos de
   desarrollo está migrada.
2. **Given** el entorno preparado, **When** el integrante arranca la aplicación, **Then** puede
   abrirla en el navegador mediante un puerto reenviado y documentado.
3. **Given** el entorno preparado, **When** ejecuta las suites de PHPUnit, Vitest y Playwright,
   **Then** las tres pueden ejecutarse sin instalar herramientas adicionales.
4. **Given** que el entorno no se pudo probar en Codespaces, **When** alguien lee la documentación,
   **Then** encuentra esa limitación declarada explícitamente.

---

### User Story 3 - Propuesta de infraestructura como código (Priority: P3)

Como equipo, queremos un diseño documentado de cómo se provisionaría la infraestructura de Staging
y Producción con Terraform, para tener una base cuando exista hosting, sin crear recursos reales ni
gastar créditos hoy.

**Why this priority**: No existe hosting (ver `docs/planeacion/estrategia-despliegue.md`); la
propuesta es solo diseño y no entrega valor ejecutable inmediato.

**Independent Test**: Leer la propuesta y verificar que describe recursos, variables y salidas, que
todo está etiquetado como PLANEADO y que el repositorio no contiene credenciales ni estado de
Terraform.

**Acceptance Scenarios**:

1. **Given** la propuesta, **When** se revisa, **Then** cada elemento está etiquetado como
   PLANEADO y no hay afirmaciones de recursos existentes.
2. **Given** el repositorio, **When** se busca configuración o estado de Terraform, **Then** no hay
   credenciales, archivos de estado ni recursos aplicados.

---

### Edge Cases

- **Dependencia de internet externa**: `tests/e2e/example.spec.js` es la plantilla de Playwright y
  navega a un sitio externo (playwright.dev); no prueba la aplicación y puede fallar por causas
  ajenas al proyecto.
- **Base de datos sin datos**: la prueba de detalle de libro pasa sin verificar nada si el catálogo
  está vacío; el CI debe cargar datos de ejemplo para que la prueba sea significativa.
- **Recursos de interfaz sin compilar**: según el Issue #138, sin compilar los assets fallaron 3 de
  14 pruebas en local; el CI debe compilarlos antes de ejecutar las pruebas.
- **Servidor que no arranca**: el CI debe fallar con un mensaje claro tras un tiempo límite en vez
  de quedarse esperando o ejecutar pruebas contra un servidor caído.
- **Base de datos en memoria**: el servidor de la aplicación y el proceso de migraciones son
  procesos distintos; una base en memoria no se comparte entre ellos.
- **Checks existentes**: agregar el nuevo check no debe cambiar el comportamiento ni el resultado
  de lint/formato ni de PHPUnit.
- **Sin secretos**: ni el CI ni el entorno de desarrollo deben requerir credenciales reales; la
  prueba de un servicio externo (p. ej. correo) no debe enviar mensajes reales.

## Requirements *(mandatory)*

### Functional Requirements

**Integración continua (historia 1)**

- **FR-001**: El CI MUST ejecutar la suite E2E en cada push y cada Pull Request hacia `main` y
  `develop`, igual que los checks existentes.
- **FR-002**: La suite E2E MUST ejecutarse como un check independiente, de modo que un fallo E2E no
  oculte ni altere el resultado de lint/formato ni de PHPUnit.
- **FR-003**: Antes de ejecutar las pruebas, el CI MUST preparar la aplicación completa: dependencias,
  configuración local sin secretos, clave de aplicación, base de datos migrada con datos de ejemplo
  y recursos de interfaz compilados.
- **FR-004**: El CI MUST esperar a que la aplicación responda en su endpoint de salud antes de
  ejecutar las pruebas, con un tiempo límite, y MUST fallar con un mensaje claro si se excede.
- **FR-005**: El CI MUST publicar el reporte de las pruebas E2E como artefacto descargable en cada
  ejecución, pase o falle.
- **FR-006**: La suite ejecutada en CI MUST contener solo pruebas de la aplicación; las pruebas que
  dependen de sitios externos se excluyen o se eliminan, con la decisión justificada en
  `research.md`.
- **FR-007**: Las pruebas E2E MUST ejecutarse de forma que una prueba marcada como exclusiva por
  error (`only`) haga fallar el CI (comportamiento ya configurado).

**Entorno de desarrollo (historia 2)**

- **FR-008**: El repositorio MUST incluir una definición de entorno de desarrollo que provea las
  versiones de PHP, Composer, Node y npm requeridas por el proyecto, SQLite y un navegador Chromium
  utilizable por Playwright.
- **FR-009**: La preparación automática del entorno MUST instalar dependencias, crear la
  configuración local a partir de `.env.example`, generar la clave y migrar la base de datos de
  desarrollo.
- **FR-010**: La definición MUST declarar los puertos de la aplicación y del servidor de recursos de
  interfaz, y documentar los pasos para arrancar la aplicación y ejecutar cada suite.
- **FR-011**: La definición del entorno MUST NOT contener secretos ni credenciales.

**Infraestructura como código (historia 3)**

- **FR-012**: El proyecto MUST incluir una propuesta de Terraform, solo como documento de diseño,
  etiquetada como PLANEADO.
- **FR-013**: El repositorio MUST NOT contener archivos de estado de Terraform, credenciales ni
  configuración que cree recursos al ejecutarse.

**Transversal**

- **FR-014**: Toda la documentación de esta spec MUST etiquetar cada elemento como IMPLEMENTADO,
  VALIDADO o PLANEADO, según la constitución (principio VIII).
- **FR-015**: Lo que no se pueda verificar (p. ej. el entorno en Codespaces) MUST declararse como no
  verificado.

### Key Entities

- **Pipeline de CI**: conjunto de checks que se ejecutan por push/PR; hoy `lint-format` y
  `php-tests`, se agrega `e2e-playwright`.
- **Reporte E2E**: resultado de una ejecución de Playwright (pruebas, estado, pasos fallidos,
  trazas), publicado como artefacto.
- **Entorno de desarrollo**: definición versionada de herramientas, puertos, variables y pasos de
  preparación.
- **Propuesta de infraestructura**: documento de diseño con ambientes, recursos, variables y salidas
  previstos.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Existe al menos un run real en GitHub Actions, sobre el PR de esta spec, en el que los
  tres checks (lint/formato, PHPUnit y E2E) terminan en verde; su enlace queda registrado en la
  documentación.
- **SC-002**: El 100 % de las pruebas E2E de la aplicación incluidas en la suite se ejecutan en CI
  (ninguna se omite en silencio).
- **SC-003**: Los checks de lint/formato y PHPUnit mantienen el mismo resultado que antes del cambio
  (siguen en verde).
- **SC-004**: El check E2E completo termina en menos de 10 minutos.
- **SC-005**: Un integrante puede pasar de "abrir el entorno" a "aplicación corriendo" sin instalar
  herramientas manualmente, siguiendo solo los pasos documentados.
- **SC-006**: El repositorio contiene 0 secretos, credenciales o archivos de estado de
  infraestructura introducidos por este cambio.

## Assumptions

- El CI usa GitHub Actions con los runners estándar de Ubuntu, igual que los jobs existentes.
- La base de datos en CI y en el entorno de desarrollo es SQLite en archivo (`.env.example` ya usa
  `DB_CONNECTION=sqlite`); MySQL queda fuera de alcance para el E2E.
- Los seeders existentes (`DatabaseSeeder`, `RolesSeeder`, `BibliotecaSeeder`) bastan como datos de
  ejemplo y no requieren servicios externos.
- La afirmación del Issue #138 "sin compilar los assets fallaron 3 de 14 pruebas" se verificó en una
  simulación local (research.md §1).
- El endpoint de salud de Laravel `/up` ya existe (`bootstrap/app.php`) y se reutiliza.
- Solo se prueba con Chromium; otros navegadores quedan fuera de alcance.
- Ampliar o corregir las pruebas E2E existentes (favoritos, roles, pruebas que no verifican lo que
  su nombre indica) es alcance de la spec 001 (Issue #140), no de esta.
- Es posible que el entorno no se pueda probar en Codespaces por falta de acceso o cuota; en ese
  caso se documenta según FR-015.
- La ejecución de Vitest en CI (exigida como puerta por la constitución) no está en el alcance del
  Issue #138; se registra como brecha en `research.md`.
