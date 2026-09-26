---

description: "Lista de tareas de la spec 001 — pruebas E2E con Playwright"
---

# Tasks: Pruebas E2E con Playwright — flujos críticos

**Input**: Documentos de diseño en `specs/001-pruebas-e2e-playwright/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/suite-e2e.md

**Tests**: Esta feature *son* pruebas; cada tarea de historia crea o corrige pruebas E2E.

**Estado**: El Issue #140 entrega la especificación. Las tareas T001–T003 se completaron en este
Issue; T004 en adelante quedan **PLANEADO** para uno o más Issues de implementación.

## Format: `[ID] [P?] [Story] Description`

---

## Phase 1: Setup

- [X] T001 Ejecutar la suite actual en una copia limpia y registrar la línea base (12 pasadas) en specs/001-pruebas-e2e-playwright/research.md §1
- [X] T002 Identificar pruebas defectuosas y documentarlas en specs/001-pruebas-e2e-playwright/research.md §2
- [X] T003 Documentar la decisión sobre Playwright MCP en specs/001-pruebas-e2e-playwright/research.md §7

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Autenticación reutilizable; bloquea US1 (parcial), US3 y US4

- [ ] T004 [P] Crear tests/e2e/helpers/usuarios.js que exporte `{ usuario, admin, superadmin }` con `email`, `password` y ruta de `storageState` (`playwright/.auth/<rol>.json`), tomando los valores de database/seeders/RolesSeeder.php
- [ ] T005 Crear tests/e2e/auth.setup.js: para cada rol, llenar `/login`, enviar, esperar salir de `/login` y guardar `page.context().storageState({ path })`
- [ ] T006 En playwright.config.js agregar el proyecto `{ name: 'setup', testMatch: /.*\.setup\.js/ }` y `dependencies: ['setup']` en el proyecto `chromium`
- [ ] T007 [P] Agregar `/playwright/.auth` a .gitignore

**Checkpoint**: `npx playwright test` ejecuta el setup y crea 3 archivos de sesión

---

## Phase 3: User Story 1 - Autenticación (Priority: P1) 🎯 MVP

**Goal**: Login exitoso, logout y redirecciones de invitado

**Independent Test**: `npx playwright test login.spec.js acceso.spec.js -g "invitado|login|logout"`

- [ ] T008 [US1] En tests/e2e/login.spec.js agregar "inicia sesión como usuario y sale de /login" usando `usuarios.usuario` por el formulario, en un contexto nuevo (sin `storageState`)
- [ ] T009 [US1] En la misma prueba de T008, cerrar sesión y verificar que `/favoritos` redirige a `/login`. No usar una sesión guardada: el logout invalida la sesión en el servidor y rompería las pruebas que la reutilizan
- [ ] T010 [P] [US1] Crear tests/e2e/acceso.spec.js con un bloque "invitado" que verifique que `/favoritos`, `/perfil` y `/dashboard` redirigen a `/login`

---

## Phase 4: User Story 2 - Catálogo y búsqueda (Priority: P1)

**Goal**: Detalle real y búsqueda con resultados, sin resultados y filtro

**Independent Test**: `npx playwright test catalogo.spec.js busqueda.spec.js`

- [ ] T011 [US2] En tests/e2e/catalogo.spec.js reescribir "muestra la página de detalle de un libro": clic en `page.locator('a[href*="/libros/"]').first()`, sin `if`; verificar URL `/libros/\d+` y que el título del libro es visible
- [ ] T012 [US2] En tests/e2e/catalogo.spec.js agregar "el catálogo muestra al menos un libro" (`a[href*="/libros/"]` con `count > 0` vía `expect(...).not.toHaveCount(0)`)
- [ ] T013 [P] [US2] Crear tests/e2e/busqueda.spec.js: buscar un título del `BibliotecaSeeder` y ver el libro y el contador "resultado(s)"
- [ ] T014 [US2] En tests/e2e/busqueda.spec.js: buscar un texto inexistente y ver `No se encontraron libros para "<texto>".`
- [ ] T015 [US2] En tests/e2e/busqueda.spec.js: elegir una categoría en el combo "Todas las categorías" y verificar que se muestran exactamente los títulos que el `BibliotecaSeeder` asigna a esa categoría (las tarjetas no muestran la categoría; la lista esperada se toma del seeder)
- [ ] T016 [US2] Mover "muestra el buscador dinámico" de tests/e2e/catalogo.spec.js a tests/e2e/busqueda.spec.js

---

## Phase 5: User Story 3 - Favoritos (Priority: P2)

**Goal**: Agregar, listar y quitar favoritos con sesión

**Independent Test**: `npx playwright test favoritos.spec.js`

- [ ] T017 [US3] Crear tests/e2e/favoritos.spec.js con `test.use({ storageState: usuarios.usuario.storageState })` y un `afterEach` que, si el libro quedó como favorito, pulse "Quitar de favoritos" (FR-007)
- [ ] T018 [US3] En tests/e2e/favoritos.spec.js: en el detalle de un libro, pulsar "Agregar a favoritos" y verificar que el botón cambia a "Quitar de favoritos"
- [ ] T019 [US3] En tests/e2e/favoritos.spec.js: el libro marcado aparece en `/favoritos`; quitarlo desde ahí; recargar y verificar que ya no aparece
- [ ] T020 [P] [US3] En tests/e2e/favoritos.spec.js (bloque sin sesión): el detalle de un libro no muestra el botón de favoritos

---

## Phase 6: User Story 4 - Roles y seguridad (Priority: P2)

**Goal**: Matriz de acceso de data-model.md

**Independent Test**: `npx playwright test acceso.spec.js`

- [ ] T021 [US4] En tests/e2e/acceso.spec.js, bloque `usuario`: `page.goto('/admin/roles')` y `page.goto('/superadmin')` devuelven status 403
- [ ] T022 [US4] En tests/e2e/acceso.spec.js, bloque `admin`: `/admin/roles` muestra el panel (200); `/superadmin` devuelve 403
- [ ] T023 [US4] En tests/e2e/acceso.spec.js, bloque `superadmin`: `/superadmin` muestra el panel (200)
- [ ] T024 [US4] En tests/e2e/acceso.spec.js, bloque invitado: `/admin/roles` y `/superadmin` redirigen a `/login`
- [ ] T025 [US4] Abrir un Issue `fix/` para restringir `/usuarios` con `role:admin,superadmin` (research.md §6); cuando se corrija, agregar en tests/e2e/acceso.spec.js "usuario recibe 403 en /usuarios" (FR-010)

---

## Phase 7: Polish & Cross-Cutting Concerns

- [ ] T026 [P] En tests/e2e/navegacion.spec.js renombrar "navega a la pagina about" y "navega al formulario de contacto" a lo que verifican, o hacer que verifiquen los enlaces del menú (research.md §2)
- [ ] T027 [P] Eliminar tests/e2e/contacto.spec.js (duplica catálogo y login; research.md §2)
- [ ] T028 Verificar `grep -rn "waitForTimeout" tests/e2e` sin resultados y ninguna prueba con `if` alrededor de sus `expect` (SC-002)
- [ ] T029 Ejecutar `npx playwright test --repeat-each=3` en local sin fallos (SC-004) y registrar el resultado en docs/planeacion/plan-de-pruebas.md
- [ ] T030 Registrar el run de CI con la suite nueva (enlace, pruebas, duración) en docs/planeacion/plan-de-pruebas.md y en specs/001-pruebas-e2e-playwright/analysis.md

---

## Dependencies & Execution Order

- **Phase 1**: completada (Issue #140).
- **Phase 2 (T004–T007)**: bloquea US3 y US4. US1 (T008–T010) y US2 no dependen de ella.
- **US1, US2**: independientes entre sí.
- **US3, US4**: dependen de la fase 2; independientes entre sí.
- **T025**: depende de un Issue de corrección externo a esta spec.
- **Polish**: T026–T027 en cualquier momento; T028–T030 al final.

## Parallel Example

```text
Sin esperar la fase 2:  US2 (T011–T016), T008, T010, T026, T027
Tras la fase 2:         US3 (T017–T020) y US4 (T021–T024) en paralelo
```

## Implementation Strategy

1. **MVP**: T011 (quitar la prueba vacía) + US1. Es lo que más reduce el riesgo actual.
2. Luego US2 completa y la fase 2.
3. Después US3 y US4.
4. Cerrar con Polish y la evidencia de CI.
