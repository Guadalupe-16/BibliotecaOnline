---
description: "Lista de tareas de la guía interactiva para usuarios nuevos"
---

# Tasks: Guía interactiva para usuarios nuevos

**Input**: Documentos de diseño en `/specs/002-guia-interactiva-driverjs/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/tour-ui-contract.md
**Estado**: PLANEADO. Ninguna tarea está hecha; este piloto solo entrega la planeación.
**Tests**: incluidos, porque la constitución (principio IV) los exige y la spec pide seis comportamientos verificables.
**Organization**: agrupadas por historia de usuario para implementarlas y probarlas de forma independiente.

## Format: `[ID] [P?] [Story] Descripción`

- **[P]**: se puede hacer en paralelo (archivos distintos, sin dependencias)
- **[Story]**: historia de usuario de la spec (US1 a US4)
- Cada tarea nombra el archivo exacto

## Phase 1: Setup

- [ ] T001 Instalar Driver.js con `npm install driver.js` y confirmar en `package.json` y `package-lock.json` la versión `^1.8.0`
- [ ] T002 [P] Crear la carpeta `resources/js/tour/` con `tour-logic.js` y `tour.js` vacíos exportando sus funciones
- [ ] T003 [P] Importar `driver.js/dist/driver.css` en `resources/css/app.css` y comprobar que `npm run build` compila

---

## Phase 2: Foundational (bloquea todas las historias)

**⚠️ CRÍTICO**: ninguna historia puede empezar hasta terminar esta fase.

- [ ] T004 Definir en `resources/js/tour/tour-logic.js` los cinco pasos (`bienvenida`, `catalogo`, `buscar`, `favoritos`, `perfil`) con títulos y textos en español y los textos de botones y progreso (FR-001, FR-002, FR-016)
- [ ] T005 [P] Implementar en `resources/js/tour/tour-logic.js` `claveEstado`, `debeIniciar`, `marcarVisto` y `limpiarEstado`, con lectura y escritura dentro de `try/catch` (FR-007, FR-014)
- [ ] T006 [P] Implementar en `resources/js/tour/tour-logic.js` `construirPasos(pasos, resolverAncla)` que descarta pasos sin ancla visible y devuelve vacío si no hay ninguno anclado (FR-003, FR-009, FR-010)
- [ ] T007 Agregar `data-tour` (`catalogo`, `buscar`, `favoritos`, `perfil`) en ambas copias del menú y `data-tour-usuario` en el `<nav>` de `resources/views/components/navigation.blade.php` (FR-002, FR-013)
- [ ] T008 Agregar en `resources/views/components/navigation.blade.php` los listeners Alpine `x-on:tour:abrir-menu.window` y `x-on:tour:cerrar-menu.window` que cambian `menuAbierto` (FR-013)

**Checkpoint**: lógica y anclajes listos; las historias pueden empezar.

---

## Phase 3: User Story 1 - Recorrido de bienvenida (Priority: P1) 🎯 MVP

**Goal**: quien llega por primera vez ve el recorrido y puede avanzar, retroceder y finalizarlo.

**Independent Test**: abrir el sitio en una ventana de incógnito, ver el paso 1 y recorrerlo hasta "Finalizar".

### Tests for User Story 1 (escribirlas primero; deben fallar antes de implementar)

- [ ] T009 [P] [US1] Vitest de los pasos, los textos en español y `construirPasos` en `resources/js/tests/tour.test.js`, importando `resources/js/tour/tour-logic.js`
- [ ] T010 [P] [US1] PHPUnit `tests/Feature/TourAnclajesTest.php`: la página del catálogo contiene `data-tour` de catálogo, buscar y favoritos; `perfil` solo con sesión; `data-tour-usuario` vale `guest` o el id
- [ ] T011 [P] [US1] Playwright `tests/e2e/tour.spec.js`: inicia en la primera visita con "1 de N", avanza, retrocede y finaliza

### Implementation for User Story 1

- [ ] T012 [US1] Integrar Driver.js en `resources/js/tour/tour.js` con `showProgress`, `progressText`, `nextBtnText`, `prevBtnText`, `doneBtnText`, `showButtons` y `skipMissingElement: true` (FR-001 a FR-004)
- [ ] T013 [US1] Llamar a `iniciarSiCorresponde()` al cargar la página desde `resources/js/app.js` (FR-006)
- [ ] T014 [US1] En `resources/js/tour/tour.js` guardar `completado` en `onDoneClick` y `cerrado` en `onDestroyed` (FR-004, FR-007)

**Checkpoint**: la historia 1 funciona sola y es demostrable.

---

## Phase 4: User Story 2 - No repetir y relanzar (Priority: P2)

**Goal**: el recorrido no se repite y puede relanzarse desde el menú.

**Independent Test**: completar el recorrido, recargar y comprobar que no aparece; pulsar "Ver guía" y verlo desde el paso 1.

### Tests for User Story 2

- [ ] T015 [P] [US2] Vitest en `resources/js/tests/tour.test.js`: no se repite si hay estado, claves distintas por visitante y por usuario, relanzar limpia el estado
- [ ] T016 [P] [US2] Playwright en `tests/e2e/tour.spec.js`: no se repite tras recargar; "Ver guía" relanza; el estado de una cuenta no afecta a otra en el mismo navegador

### Implementation for User Story 2

- [ ] T017 [US2] Agregar el enlace "Ver guía" con `data-tour-relanzar` en ambas copias del menú de `resources/views/components/navigation.blade.php` (FR-008)
- [ ] T018 [US2] Implementar `relanzar()` con bloqueo contra dos recorridos simultáneos en `resources/js/tour/tour.js` y conectarlo al enlace (FR-008)

---

## Phase 5: User Story 3 - No se rompe si falta un elemento (Priority: P2)

**Goal**: los pasos sin elemento visible se omiten sin errores.

**Independent Test**: iniciar como visitante y comprobar que se omite "Mi perfil" y no hay errores en consola.

### Tests for User Story 3

- [ ] T019 [P] [US3] Vitest en `resources/js/tests/tour.test.js`: pasos ausentes omitidos, lista vacía no inicia, `localStorage` que lanza excepción no rompe
- [ ] T020 [P] [US3] Playwright en `tests/e2e/tour.spec.js`: el visitante no ve "Mi perfil", eliminar un ancla del DOM no rompe el recorrido y no hay errores en la consola (FR-009, SC-004)

### Implementation for User Story 3

- [ ] T021 [US3] Resolver en `resources/js/tour/tour.js` el primer elemento visible de cada `data-tour` entre las copias de escritorio y móvil (FR-009, FR-013)
- [ ] T022 [US3] Abrir el drawer móvil antes del recorrido y cerrarlo al terminar, con el evento `tour:abrir-menu` (FR-013)
- [ ] T033 [P] [US3] Playwright en `tests/e2e/tour.spec.js` con `test.use({ viewport: { width: 390, height: 844 } })`: el drawer se abre durante el recorrido, se resaltan los enlaces visibles y se cierra al terminar (FR-013)
- [ ] T023 [US3] Cerrar el recorrido sin marcarlo como visto si la página cambia durante el recorrido (casos límite de la spec)

---

## Phase 6: User Story 4 - Accesibilidad (Priority: P3)

**Goal**: el recorrido se opera con teclado y se anuncia a lectores de pantalla.

**Independent Test**: recorrer el tour solo con teclado y cerrarlo con Escape.

### Tests for User Story 4

- [ ] T024 [P] [US4] Playwright en `tests/e2e/tour.spec.js`: Tab y flechas navegan, Escape cierra y el foco vuelve a la página (FR-011, SC-003)
- [ ] T025 [P] [US4] Probar con un lector de pantalla y registrar el resultado real en `specs/002-guia-interactiva-driverjs/quickstart.md` (research, sección 4)

### Implementation for User Story 4

- [ ] T026 [US4] En `onPopoverRender` de `resources/js/tour/tour.js` añadir `role="dialog"`, `aria-labelledby`, `aria-describedby` y foco si Driver.js no los aporta (FR-011, FR-012)
- [ ] T027 [US4] Confirmar el cierre con Escape y devolver el foco a la página (FR-005, FR-011)

---

## Phase 7: Polish

- [ ] T028 [P] Ejecutar `php artisan test`, `npx playwright test`, `npm run test`, `npm run lint` y `npm run format:check` y registrar resultados reales (SC-005, FR-015)
- [ ] T029 [P] Actualizar `docs/sdd/estado-actual/trazabilidad.md`, `docs/planeacion/parametros-configuracion.md` y `docs/planeacion/casos-de-prueba.md` con el estado real de la funcionalidad
- [ ] T030 [P] Medir el tamaño del bundle antes y después de agregar Driver.js (research, riesgo 4)
- [ ] T032 [P] Medir, cronometrando el recorrido en Playwright, que un recorrido completo dura menos de 60 s y registrar el dato real (SC-001)
- [ ] T031 Revisar con el equipo los textos de `data-model.md` antes de fusionar

---

## Dependencies & Execution Order

- Phase 1 → Phase 2 → historias → Phase 7.
- US1 es el MVP; US2, US3 y US4 dependen de que exista el recorrido de US1 (T012 a T014).
- US2, US3 y US4 son independientes entre sí una vez terminada US1.
- Dentro de cada historia, las pruebas van antes y deben fallar antes de implementar.
- Las pruebas de Vitest y Playwright solo se ejecutan en CI cuando el Issue #138 lo permita.

### Parallel Opportunities

- T002 y T003; T005 y T006; T009, T010 y T011; T015 y T016; T019 y T020; T024 y T025; T028 a T030.

## Implementation Strategy

1. **MVP**: Fases 1, 2 y 3; validar con `quickstart.md`.
2. **Incremental**: US2, luego US3, luego US4, validando cada una por separado.
3. Cada historia se entrega en su propio PR a `develop` con `Closes` de su Issue.
