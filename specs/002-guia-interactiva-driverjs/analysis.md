# Análisis de consistencia: guía interactiva para usuarios nuevos

Fecha: 2026-09-25 · Equivalente a `/speckit-analyze` (solo lectura) sobre `spec.md`, `plan.md`,
`research.md`, `data-model.md`, `contracts/`, `quickstart.md` y `tasks.md`.

El análisis se hizo comparando los archivos reales: se extrajeron los identificadores `FR-*` y `SC-*`
de la spec y se buscó en `tasks.md` qué tareas los mencionan. Los números de abajo salen de ese
recuento, no de una estimación.

## 1. Resultado del recuento

| Elemento | Total | Cubiertos por al menos una tarea |
|---|---:|---|
| Requisitos funcionales (`FR-001` a `FR-016`) | 16 | 16 |
| Criterios de éxito (`SC-001` a `SC-005`) | 5 | 1 en el primer recuento (SC-005); ver hallazgos |
| Historias de usuario (US1 a US4) | 4 | 4, cada una con pruebas, implementación y prueba independiente |
| Tareas | 31 en el primer recuento | — |
| Marcadores `[NEEDS CLARIFICATION]` o de plantilla sin reemplazar | 0 | — |

## 2. Hallazgos

| ID | Severidad | Ubicación | Hallazgo | Recomendación |
|---|---|---|---|---|
| A1 | Media | `spec.md` FR-013, `tasks.md`, `playwright.config.js` | FR-013 (funcionar en escritorio y móvil) tenía tareas de implementación (T007, T008, T021, T022) pero ninguna prueba. Además `playwright.config.js` solo define el proyecto `chromium` con `devices['Desktop Chrome']` | Añadir una prueba Playwright con viewport móvil |
| A2 | Media | `spec.md` SC-001 | El criterio "recorrer el tour en menos de 60 s" no tenía ninguna tarea que lo midiera | Añadir una tarea de medición con dato real |
| A3 | Baja | `spec.md` SC-002, SC-003, SC-004 | Los criterios no se referenciaban en `tasks.md` aunque sus pruebas existían (T009 a T020, T024) | Referenciar el criterio en la tarea correspondiente |
| A4 | Baja | `spec.md` Historia 1, escenario 1 | Decía "1 de 5", pero `plan.md` y `quickstart.md` indican que el total depende de los pasos disponibles (4 como visitante) | Corregir a "1 de N" |
| A5 | Baja | `tasks.md` T029 | Referencia `docs/planeacion/parametros-configuracion.md` y `casos-de-prueba.md`, que dependen de los Issues #139 y #137 y todavía no están en `develop` | Dejarlo como dependencia explícita; se resuelve al fusionar esos PR |
| A6 | Baja | `research.md` sección 4, `spec.md` FR-005 | El cierre con Escape se exige como requisito, pero la documentación de Driver.js no lo confirma explícitamente | Ya cubierto por la prueba T024 y la tarea T027; no requiere cambio |
| A7 | Informativa | `plan.md` Constitution Check VII | El CI actual no ejecuta Vitest ni Playwright; las pruebas del tour dependen del Issue #138 | Mantener el riesgo documentado |

No se encontraron contradicciones entre `spec.md` y `plan.md` sobre almacenamiento (`localStorage` sin
cambios de base de datos), anclajes (`data-tour`), ni sobre el estado PLANEADO. Ningún requisito quedó
sin historia de usuario ni sin tarea.

## 3. Correcciones aplicadas después del análisis

| Hallazgo | Corrección |
|---|---|
| A1 | Se agregó la tarea T033 (Playwright con viewport de 390 x 844) |
| A2 | Se agregó la tarea T032 (medición del recorrido completo) |
| A3 | Se añadieron las referencias a SC-003 (T024) y SC-004 (T020) |
| A4 | Se corrigió el escenario 1 de la Historia 1 en `spec.md` |
| A5, A6, A7 | Sin cambio, según la recomendación |

Estado final: 33 tareas y 16 de 16 requisitos con tarea. Los criterios SC-001, SC-003, SC-004 y SC-005
tienen tarea que los referencia (T032, T024, T020 y T028); SC-002 se cubre con el conjunto de pruebas
T009 a T020 pero no lo nombra una tarea.

## 4. Matriz requisito → tarea (estado final)

| Requisito | Tareas |
|---|---|
| FR-001 | T004, T012 |
| FR-002 | T004, T007 |
| FR-003 | T006 |
| FR-004 | T012, T014 |
| FR-005 | T027 |
| FR-006 | T013 |
| FR-007 | T005, T014 |
| FR-008 | T017, T018 |
| FR-009 | T006, T020, T021 |
| FR-010 | T006 |
| FR-011 | T024, T026, T027 |
| FR-012 | T026 |
| FR-013 | T007, T008, T021, T022, T033 |
| FR-014 | T005 |
| FR-015 | T028 |
| FR-016 | T004 |

## 5. Conclusión

La especificación es consistente con el plan y las tareas tras las correcciones. El piloto queda listo
para implementarse, **con estado PLANEADO**: no existe código, no se instaló Driver.js y ninguna
tarea está marcada como hecha. El mayor riesgo pendiente es la accesibilidad, que la documentación de
la biblioteca no respalda y hay que comprobar con pruebas y lector de pantalla (T024, T025).
