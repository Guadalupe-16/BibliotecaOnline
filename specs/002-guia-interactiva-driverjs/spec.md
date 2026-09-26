# Feature Specification: Guía interactiva para usuarios nuevos

**Feature Branch**: `docs/137-casos-prueba-spec-002` (rama del Issue #137; la spec vive en `specs/002-guia-interactiva-driverjs`)

**Created**: 2026-09-25

**Status**: Draft — estado del piloto: **PLANEADO** (solo especificación y planeación; no hay código)

**Input**: User description: "Como usuario nuevo de BibliotecaOnline quiero recibir un recorrido guiado que me enseñe las funciones principales del sistema."

## Clarifications

### Session 2026-09-25 (decisiones por defecto, pendientes de validación del equipo)

Estas respuestas las propuso quien redactó la spec a partir del contexto del proyecto; no las confirmó una persona del equipo. Si el equipo decide otra cosa, se actualizan aquí y en `plan.md`.

- Q: ¿Dónde se guarda que el tour ya se completó? → A: En el navegador de la persona, con una clave distinta para visitantes y para cada usuario con sesión; no se agrega ningún campo a la base de datos.
- Q: ¿El tour se muestra también a visitantes sin sesión? → A: Sí. Los pasos cuyo elemento no está disponible para ese visitante (por ejemplo, el perfil) se omiten.
- Q: ¿Cuántos pasos tiene el tour mínimo? → A: Cinco, en este orden: bienvenida, catálogo, buscador, favoritos y perfil.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Recorrido de bienvenida para quien llega por primera vez (Priority: P1)

Una persona que entra por primera vez a BibliotecaOnline ve un recorrido guiado que resalta, una por una, las funciones principales (catálogo, buscador, favoritos y perfil) con un texto breve en español para cada una, y puede avanzar, retroceder o cerrarlo en cualquier momento.

**Why this priority**: Es el valor central de la funcionalidad: reducir la curva de aprendizaje de quien no conoce el sistema. Sin esta historia no hay funcionalidad.

**Independent Test**: Abrir el sitio con un navegador sin datos previos y comprobar que aparece el paso de bienvenida y que se puede recorrer hasta el último paso.

**Acceptance Scenarios**:

1. **Given** una persona que nunca completó ni cerró el tour, **When** entra a una página con el menú principal, **Then** aparece el paso 1 (bienvenida) con un indicador de progreso "1 de N", donde N es el número de pasos disponibles (5 con sesión, 4 como visitante).
2. **Given** el tour está en el paso N, **When** la persona elige "Siguiente", **Then** se resalta el elemento del paso N+1 y el indicador de progreso se actualiza.
3. **Given** el tour está en el paso N mayor que 1, **When** elige "Anterior", **Then** vuelve al paso N-1.
4. **Given** el tour está en el último paso, **When** elige "Finalizar", **Then** el tour se cierra y se registra como completado.

---

### User Story 2 - No repetir el tour ya visto y poder relanzarlo (Priority: P2)

Quien ya completó o cerró el tour no lo vuelve a ver en cada visita, pero puede relanzarlo cuando quiera desde el menú.

**Why this priority**: Evita molestar a usuarios recurrentes y da una salida a quien quiere repasar. Depende de que exista la historia 1.

**Independent Test**: Completar el tour, recargar la página y verificar que no aparece; luego usar la opción "Ver guía" del menú y verificar que vuelve a empezar en el paso 1.

**Acceptance Scenarios**:

1. **Given** la persona completó el tour, **When** vuelve a visitar el sitio (misma persona, mismo navegador), **Then** el tour no se inicia solo.
2. **Given** la persona cerró el tour antes de terminarlo, **When** vuelve al sitio, **Then** el tour no se inicia solo.
3. **Given** cualquier estado previo, **When** elige "Ver guía" en el menú, **Then** el tour empieza desde el paso 1.
4. **Given** dos personas distintas usan el mismo navegador con sesiones distintas, **When** una completa el tour, **Then** el tour sigue disponible para la otra.

---

### User Story 3 - Recorrido que no se rompe si falta un elemento (Priority: P2)

El tour omite con elegancia los pasos cuyo elemento no existe o no es visible (por ejemplo, el perfil para un visitante sin sesión, o el menú en pantallas pequeñas), sin errores visibles ni bloqueos.

**Why this priority**: El menú cambia según la sesión y el tamaño de pantalla; un tour que falla por un elemento ausente estropea la primera impresión.

**Independent Test**: Iniciar el tour como visitante y verificar que recorre los pasos disponibles, omite el perfil y termina sin error.

**Acceptance Scenarios**:

1. **Given** un visitante sin sesión, **When** recorre el tour, **Then** el paso "Perfil" no aparece y el indicador de progreso refleja solo los pasos mostrados.
2. **Given** un elemento ancla de un paso no existe en la página, **When** el tour llega a ese paso, **Then** lo omite y continúa con el siguiente sin mostrar errores.
3. **Given** ningún elemento ancla existe, **When** se intenta iniciar el tour, **Then** no se muestra nada y la página funciona con normalidad.

---

### User Story 4 - Recorrido accesible (Priority: P3)

El tour se puede operar solo con el teclado y es interpretable por lectores de pantalla.

**Why this priority**: Garantiza que la guía no excluya a personas con discapacidad; es una mejora de calidad sobre las historias anteriores.

**Independent Test**: Recorrer el tour completo sin usar el ratón y verificar que el foco se mantiene dentro del cuadro del paso y que cada paso se anuncia.

**Acceptance Scenarios**:

1. **Given** el tour está abierto, **When** la persona pulsa Escape, **Then** el tour se cierra y el foco regresa a la página.
2. **Given** el tour está abierto, **When** usa Tab o las flechas, **Then** puede llegar a "Anterior", "Siguiente" y "Cerrar" en un orden lógico.
3. **Given** un lector de pantalla activo, **When** aparece un paso, **Then** su título y texto se anuncian junto con la posición ("paso 2 de 5").

---

### Edge Cases

- ¿Qué pasa si el navegador no permite guardar datos locales (modo privado o bloqueado)? El tour debe funcionar en la sesión actual sin errores y puede volver a mostrarse en la siguiente visita.
- ¿Qué pasa en pantallas pequeñas, donde el menú principal está oculto tras un botón? El tour usa los elementos visibles o abre el menú antes del paso; si no puede, omite el paso.
- ¿Qué pasa si la persona inicia sesión a mitad del tour? El tour de visitante se cierra y el estado de la persona con sesión se evalúa aparte.
- ¿Qué pasa si la página cambia (navegación) mientras el tour está abierto? El tour se cierra sin marcarse como completado.
- ¿Qué pasa si hay dos elementos coincidentes (versión de escritorio y versión móvil del menú)? Se elige el que está visible.
- ¿Qué pasa si el tour se lanza dos veces seguidas (doble clic en "Ver guía")? Solo hay un tour activo.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: El sistema MUST mostrar un recorrido guiado de cinco pasos, en este orden: bienvenida, catálogo, buscador, favoritos y perfil.
- **FR-002**: Cada paso MUST resaltar el elemento de la interfaz al que se refiere y mostrar un título y un texto breve en español.
- **FR-003**: El sistema MUST mostrar un indicador de progreso ("paso X de Y") que cuente solo los pasos realmente mostrados.
- **FR-004**: La persona MUST poder avanzar ("Siguiente"), retroceder ("Anterior") y cerrar el recorrido en cualquier paso; en el último paso el botón de avance MUST llamarse "Finalizar".
- **FR-005**: El recorrido MUST cerrarse al pulsar Escape.
- **FR-006**: El sistema MUST iniciar el recorrido automáticamente solo para quien no lo haya completado ni cerrado antes.
- **FR-007**: El sistema MUST recordar en el navegador que el recorrido ya se vio (completado o cerrado), con un registro separado para visitantes y para cada usuario con sesión.
- **FR-008**: El sistema MUST ofrecer una opción visible en el menú para relanzar el recorrido en cualquier momento, sin importar si ya se completó.
- **FR-009**: El sistema MUST omitir sin error los pasos cuyo elemento no exista o no sea visible, y MUST completar el recorrido con los pasos restantes.
- **FR-010**: Si no hay ningún paso disponible, el sistema MUST NOT mostrar el recorrido ni producir errores visibles.
- **FR-011**: El recorrido MUST poder operarse solo con teclado, mantener el foco dentro del cuadro del paso mientras está abierto y devolverlo a la página al cerrarse.
- **FR-012**: Cada paso MUST ser anunciado por lectores de pantalla con su título, su texto y su posición.
- **FR-013**: El recorrido MUST funcionar en pantallas de escritorio y pequeñas, considerando que el menú principal cambia de presentación.
- **FR-014**: Si el navegador no puede guardar el estado, el recorrido MUST seguir funcionando durante la sesión sin errores.
- **FR-015**: El recorrido MUST NOT alterar el funcionamiento de las demás funciones de la página, ni requerir cambios en la base de datos.
- **FR-016**: Los textos, botones y mensajes del recorrido MUST estar en español.

### Key Entities

- **Paso del recorrido**: una etapa con un título, un texto, el elemento de la interfaz que resalta, su posición y su condición de disponibilidad (por ejemplo, requiere sesión).
- **Estado del recorrido**: registro de que una persona ya vio el recorrido, asociado al navegador y a la identidad de quien lo vio (visitante o usuario con sesión).

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Una persona nueva puede recorrer los cinco pasos, de principio a fin, en menos de 60 segundos sin ayuda.
- **SC-002**: El 100 % de los seis comportamientos requeridos (inicia cuando corresponde, avanza, se puede cerrar, no se repite si se completó, se puede relanzar y no falla si falta un elemento) tiene una prueba automática que pasa.
- **SC-003**: El recorrido se puede completar usando únicamente el teclado, sin quedar atrapado ni perder el foco.
- **SC-004**: Ante la ausencia de cualquiera de los elementos resaltados, el recorrido termina sin producir errores visibles ni en la consola del navegador.
- **SC-005**: Ninguna página existente cambia su funcionamiento (las pruebas actuales de PHPUnit, Vitest y Playwright siguen pasando después de la implementación).

## Assumptions

- Las personas usan un navegador moderno con JavaScript habilitado.
- El menú principal (`components/navigation`) está presente en todas las páginas que usan el layout `layouts/app`.
- El recorrido se limita a lo que ya existe: catálogo, buscador, favoritos y perfil; no añade nuevas pantallas.
- "Usuario nuevo" se entiende como quien no ha visto el recorrido en ese navegador, sin depender de la fecha de registro.
- No se requieren cambios en la base de datos ni nuevas rutas del servidor (el estado vive en el navegador).
- El idioma es solo español; la internacionalización queda fuera de alcance.
- Estado de la implementación: PLANEADO. Este piloto solo entrega la especificación y su planeación; el código se abordará en un Issue posterior.
- Dependencia: los elementos del menú actual no tienen identificadores propios; habrá que marcarlos como puntos de anclaje en la fase de implementación (se detalla en el plan).
