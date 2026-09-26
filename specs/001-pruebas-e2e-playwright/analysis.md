# Analysis: spec 001 — Pruebas E2E con Playwright

**Fecha**: 2026-09-25 · **Comando**: `/speckit-analyze` · **Issue**: #140

Informe de consistencia entre `spec.md`, `plan.md` y `tasks.md`, contra la constitución 1.0.0.

## Hallazgos

| ID | Categoría | Severidad | Ubicación | Resumen | Resolución |
|----|-----------|-----------|-----------|---------|------------|
| U1 | Subespecificación | MEDIUM | spec.md US2-5; tasks.md T015 | "Solo ve libros de esa categoría", pero las tarjetas de resultado no muestran la categoría | **Corregido**: T015 compara con los títulos que el `BibliotecaSeeder` asigna a la categoría |
| U2 | Diseño | MEDIUM | tasks.md T009 | El logout con una sesión guardada la invalidaría en el servidor y rompería otras pruebas | **Corregido**: login y logout en la misma prueba, en contexto propio (research.md §3) |
| D1 | Constitución | MEDIUM | constitution.md › Base de datos | E2E con SQLite en archivo | **Heredada y justificada** en la spec 003 (plan.md › Complexity Tracking) |
| G1 | Cobertura | MEDIUM | spec.md US4-5; tasks.md T025 | El escenario `/usuarios` → 403 no se puede cumplir hoy por un defecto de la app | **Registrado**: requiere Issue `fix/`; la prueba entra con la corrección (FR-010, principio VII) |
| A1 | Ambigüedad | LOW | spec.md US1-1 | "Ve la interfaz de usuario autenticado" | Aceptado: T008 lo concreta como "sale de `/login`" |
| T1 | Terminología | LOW | spec.md | "lector" y rol `usuario` se usan como sinónimos | Aceptado; el rol técnico siempre aparece como `usuario` |
| E1 | Cobertura | LOW | spec.md FR-009 | Sin tarea propia | Sin cambio: FR-009 exige *no* modificar el workflow |

## Cobertura

| Requisito | Tareas |
|---|---|
| FR-001 (conservar pruebas válidas) | T011, T016 (se preservan y reubican) |
| FR-002 (sin pruebas vacías) | T011, T028 |
| FR-003 (nombre = verificación) | T026, T027 |
| FR-004 (US1–US4) | T008–T025 |
| FR-005 (usuarios del seeder) | T004 |
| FR-006 (un login por rol) | T005, T006 |
| FR-007 (estado limpio) | T017 |
| FR-008 (sin esperas fijas) | T028 |
| FR-009 (sin cambios al CI) | — (restricción) |
| FR-010 (defecto → Issue) | T025 |
| FR-011 (Playwright MCP) | T003 ✔ |
| SC-001 – SC-005 | T008–T030 |

**Métricas**: 11 FR + 5 SC · 30 tareas (3 completadas en #140, 27 PLANEADO) · cobertura 100 % ·
0 duplicados · 0 críticos.

## Constitución

| Principio | Estado |
|---|---|
| IV. Pruebas obligatorias ("una corrección de bug incluye la prueba que lo reproduce") | Alineado: T025 |
| VI. Pruebas negativas por rol | Alineado: T010, T021–T024 |
| VII. No desactivar pruebas | Alineado: no hay `skip`/`fixme`; la eliminación de `contacto.spec.js` se justifica por duplicado |
| VIII. Evidencia honesta | Alineado: línea base real; tareas pendientes marcadas como PLANEADO |

## Evidencia de ejecución

- Línea base local: 12 pasadas en 5.6 s (2026-09-25, `60affd9`).
- CI: run [36205499377](https://github.com/Guadalupe-16/BibliotecaOnline/actions/runs/36205499377),
  job `E2E Playwright` en verde (1 min 9 s).
- Resultados de todos los niveles: `docs/planeacion/plan-de-pruebas.md` §9.
