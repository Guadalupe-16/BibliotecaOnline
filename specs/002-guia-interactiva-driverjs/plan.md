# Implementation Plan: Guía interactiva para usuarios nuevos

**Branch**: `docs/137-casos-prueba-spec-002` | **Date**: 2026-09-25 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/002-guia-interactiva-driverjs/spec.md`

**Estado del piloto**: PLANEADO. Este plan describe cómo se implementaría; Driver.js no está instalado ni hay código de la funcionalidad en el repositorio.

## Summary

Un recorrido guiado de cinco pasos (bienvenida, catálogo, buscador, favoritos y perfil) que se muestra
una sola vez a quien llega por primera vez y se puede relanzar desde el menú. Se implementa con la
librería Driver.js sobre el menú lateral ya existente (`components/navigation.blade.php`), con la lógica
(pasos, clave de almacenamiento, filtrado de pasos ausentes) aislada en un módulo JavaScript propio y
probable con Vitest, y el estado "tour visto" guardado en `localStorage` con una clave por visitante o
por usuario. No hay cambios de base de datos ni rutas nuevas. La decisión de biblioteca, almacenamiento
y anclajes está razonada en [research.md](research.md).

## Technical Context

**Language/Version**: JavaScript (ES modules, Vite 7.3.1) y Blade (Laravel 12.52.0, PHP ^8.2)

**Primary Dependencies**: `driver.js` `^1.8.0` (última versión estable en npm al 2026-09-25, licencia MIT); Alpine.js 3.15.8 ya instalado

**Storage**: `localStorage` del navegador; sin cambios de base de datos

**Testing**: Vitest 4.1.0 (lógica pura), Playwright 1.58.2 (comportamiento en navegador), PHPUnit 11.5.55 (que el HTML incluya los anclajes)

**Target Platform**: Navegadores modernos de escritorio y móvil; el menú tiene versión de escritorio (`lg:` en adelante) y versión móvil (drawer)

**Project Type**: Aplicación web monolítica Laravel con Blade

**Performance Goals**: El tour no debe retrasar la carga de la página: el módulo se carga en el bundle de `app.js` y solo crea el objeto de Driver cuando corresponde iniciar

**Constraints**: Español; operable con teclado; sin errores en consola si falta un anclaje; sin cambios de esquema (constitución, principio V); no romper las pruebas existentes

**Scale/Scope**: 5 pasos, 1 módulo JS nuevo, atributos `data-*` en 1 vista Blade (`navigation`) y un enlace "Ver guía"

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principio | Cumplimiento |
|---|---|
| I. Especificación antes de código | Cumple: existe `spec.md`; no se escribe código en este piloto |
| II. Trazabilidad | Cumple: Issue #137 → esta spec → `tasks.md` → futuros commits y PR |
| III. Protección de ramas | Cumple: trabajo en rama `docs/137-...`, PR a `develop` |
| IV. Pruebas obligatorias | Cumple: `tasks.md` exige Vitest, Playwright y PHPUnit antes de la implementación |
| V. Migraciones | No aplica: no hay cambios de esquema |
| VI. Seguridad por rol | Cumple: no se agregan rutas; el identificador de usuario expuesto en HTML es solo el id numérico de la sesión propia |
| VII. CI en verde | Riesgo abierto: el CI actual no ejecuta Vitest ni Playwright (Issue #138); las pruebas del tour deben poder correr en él |
| VIII. Evidencia honesta | Cumple: estado PLANEADO explícito; lo no verificado de Driver.js (accesibilidad) se marca en `research.md` |

Resultado: sin violaciones que justificar. Reevaluado tras el diseño de la Fase 1: sin cambios.

## Project Structure

### Documentation (this feature)

```text
specs/002-guia-interactiva-driverjs/
├── spec.md              # Especificación (/speckit-specify)
├── plan.md              # Este archivo (/speckit-plan)
├── research.md          # Decisiones de tecnología (Fase 0)
├── data-model.md        # Entidades y estado (Fase 1)
├── quickstart.md        # Guía de validación (Fase 1)
├── contracts/
│   └── tour-ui-contract.md  # Anclajes y eventos de la interfaz (Fase 1)
├── checklists/
│   └── requirements.md  # Calidad de la spec (/speckit-specify)
├── tasks.md             # Tareas (/speckit-tasks)
└── analysis.md          # Informe de consistencia (/speckit-analyze)
```

### Source Code (repository root)

Estructura real del proyecto y archivos que tocaría la implementación (nuevos marcados con `+`):

```text
resources/
├── js/
│   ├── app.js                       # importa e inicializa el tour
│   ├── tour/
│   │   ├── tour-logic.js            # + pasos, claves de almacenamiento, filtrado (funciones puras)
│   │   └── tour.js                  # + integración con Driver.js y el DOM
│   └── tests/
│       └── tour.test.js             # + Vitest sobre tour-logic.js
├── css/app.css                      # importa driver.css y ajustes de tema
└── views/components/navigation.blade.php   # atributos data-tour-* y enlace "Ver guía"

tests/
├── e2e/tour.spec.js                 # + Playwright
└── Feature/TourAnclajesTest.php     # + PHPUnit: el HTML expone anclajes e id de usuario
```

**Structure Decision**: se mantiene la estructura Laravel + Vite existente. La lógica pura va en
`tour-logic.js` para que los tests de Vitest importen código real (a diferencia de los tests actuales,
que definen sus propias funciones; ver brecha T4 en `docs/sdd/estado-actual/analisis-brechas.md`).

## Design Overview

- **Anclajes**: cada paso apunta a un atributo `data-tour="<id>"` puesto en el enlace del menú:
  `catalogo`, `buscar`, `favoritos`, `perfil`. El paso de bienvenida no tiene anclaje (se muestra
  centrado). Detalle en [contracts/tour-ui-contract.md](contracts/tour-ui-contract.md).
- **Doble menú**: el menú existe dos veces en el DOM (barra lateral de escritorio y drawer móvil). Se
  resuelve, para cada paso, el primer elemento visible con ese `data-tour`. En pantallas pequeñas el
  drawer se abre antes del recorrido mediante el evento `tour:abrir-menu` y se cierra al terminar.
- **Pasos ausentes**: antes de crear el tour se filtran los pasos cuyo anclaje no resuelve a un elemento
  visible; así el indicador "X de Y" cuenta solo los pasos mostrados (FR-003, FR-009). Si solo queda la
  bienvenida y ningún paso anclado, no se inicia (FR-010).
- **Estado**: clave `bo_tour_visto:guest` para visitantes y `bo_tour_visto:user:<id>` para cada usuario;
  el valor guarda `completado` o `cerrado` y la fecha. Todas las lecturas y escrituras van dentro de
  `try/catch` (FR-014). Detalle en [data-model.md](data-model.md).
- **Inicio**: al cargar una página que incluye el menú, si no hay estado guardado y hay al menos un paso
  anclado disponible, se inicia el tour.
- **Relanzar**: el enlace "Ver guía" del menú borra el estado y llama a `iniciar({ forzar: true })`; un
  bloqueo evita dos tours simultáneos.
- **Accesibilidad**: Driver.js declara control por teclado, pero su documentación no describe manejo de
  foco ni roles ARIA (ver `research.md`). Se verifica en implementación; si faltan, `onPopoverRender`
  añade `role="dialog"`, `aria-labelledby`/`aria-describedby` y mueve el foco al popover.
- **Textos**: en español, en `tour-logic.js`, incluidos `nextBtnText`, `prevBtnText`, `doneBtnText` y
  `progressText` (`{{current}} de {{total}}`).

## Complexity Tracking

Sin violaciones de la constitución; no se requiere justificación.
