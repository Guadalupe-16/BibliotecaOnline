# Research: Guía interactiva para usuarios nuevos

Fecha: 2026-09-25 · Fuente principal: documentación oficial de Driver.js (https://driverjs.com/docs),
registro de npm y repositorio de GitHub (redirige a `nilbuild/driver.js`).

Lo que no se pudo verificar se indica como **NO VERIFICADO**; se resolverá en la implementación.

## 1. Biblioteca del recorrido

- **Decisión**: usar Driver.js `^1.8.0`.
- **Datos verificados**: versión estable más reciente 1.8.0 (registro de npm y release de GitHub);
  licencia MIT; "5kb gzipped" según su README; sin dependencias externas; escrita en TypeScript;
  se instala con `npm install driver.js` y se importa con
  `import { driver } from "driver.js"; import "driver.js/dist/driver.css";`.
- **Fecha de publicación**: la página del release dice "17 Jul" sin año; el año 2026 es una inferencia
  a partir de una marca interna del registro, no un dato declarado.
- **Razón**: la actividad lo pide expresamente, es ligera, sin dependencias y encaja con Vite (exporta
  módulos ES y CSS).
- **Alternativas consideradas**: Shepherd.js, Intro.js y un tour hecho a mano con Alpine.js. No se
  evaluaron en profundidad (no se probaron); se descartan porque el requisito de la actividad es
  Driver.js. Un tour propio evitaría la dependencia, pero obligaría a implementar resaltado, posicionamiento
  y navegación por teclado desde cero.

## 2. Comportamiento cuando falta un elemento

- **Hecho verificado**: por defecto, si el `element` de un paso no existe se muestra un popover centrado
  de respaldo (no se omite). La opción `skipMissingElement: true` omite el paso en la dirección de
  avance. Los pasos sin `element` son "centrados intencionales" y nunca se omiten. Existe además
  `waitForElement` (milisegundos de espera).
- **NO VERIFICADO**: que el caso de elemento ausente no lance excepción ni escriba en consola; la
  documentación solo describe el popover de respaldo.
- **Decisión**: filtrar los pasos por presencia y visibilidad **antes** de crear el tour, y además activar
  `skipMissingElement: true` como red de seguridad. El filtrado propio permite que el indicador de
  progreso cuente solo los pasos mostrados (FR-003), lo que la opción sola no garantiza.
- **Alternativa descartada**: dejar el popover centrado de respaldo por defecto; contradice FR-009 (omitir
  sin mostrar información confusa).

## 3. Configuración relevante de Driver.js (verificada)

| Necesidad de la spec | Opción de Driver.js |
|---|---|
| Indicador de progreso | `showProgress: true`, `progressText: "{{current}} de {{total}}"` |
| Botones en español | `nextBtnText`, `prevBtnText`, `doneBtnText` |
| Botones visibles | `showButtons: ["next", "previous", "close"]` (valor por defecto en tours) |
| Cierre con Escape | `allowKeyboardControl: true` (por defecto); tecla exacta **NO VERIFICADA** |
| Detectar cierre y finalización | `onDestroyed` y `onDoneClick` |
| Elemento resuelto en el momento | `element` acepta selector, `Element` o función `() => Element` |
| Sin persistencia integrada | La documentación no menciona almacenamiento |

Los valores por defecto de los textos de los botones **no están documentados**; por eso se definen los
tres textos explícitamente.

## 4. Accesibilidad

- **Hechos verificados**: el README afirma "Everything is controllable by keyboard" y existe la opción
  `allowKeyboardControl`.
- **NO VERIFICADO**: teclas concretas (Escape, flechas, Tab), gestión o atrapado de foco, roles ARIA.
  La documentación no tiene sección de accesibilidad.
- **Decisión**: tratarlo como riesgo. La implementación incluye pruebas Playwright de teclado (Tab,
  flechas, Escape) y, si Driver.js no lo cubre, usa `onPopoverRender` para agregar `role="dialog"`,
  `aria-labelledby`, `aria-describedby` y enfocar el popover. El cierre por Escape se comprueba por
  prueba, no por suposición.

## 5. Almacenamiento del estado "tour visto"

- **Decisión**: `localStorage` con clave `bo_tour_visto:guest` o `bo_tour_visto:user:<id>`.
- **Razón**: no requiere migración ni rutas nuevas (constitución, principio V), funciona para visitantes y
  la clave por usuario evita que dos cuentas en el mismo navegador compartan el estado (FR-007, escenario 4
  de la historia 2).
- **Limitación**: el estado es por navegador, no por cuenta; una persona que cambie de equipo verá el tour
  otra vez. Aceptado en la spec (asunción "el estado vive en el navegador").
- **Alternativa descartada**: columna `tour_completado` en `users`. Sincronizaría entre dispositivos, pero
  exige migración, ruta de actualización y solo funciona con sesión; queda como mejora futura.
- **Robustez**: lecturas y escrituras en `try/catch`; si falla, el tour funciona en la sesión y puede
  volver a mostrarse (FR-014).

## 6. Doble presentación del menú

- **Hecho verificado en el código**: `components/navigation.blade.php` contiene dos copias del menú: una
  barra lateral `hidden lg:flex` y un drawer `lg:hidden` que se muestra con `x-show="menuAbierto"`.
  Los enlaces no tienen identificadores propios.
- **Decisión**: agregar `data-tour` a los enlaces de ambas copias y resolver, para cada paso, el primer
  elemento visible. En pantallas pequeñas se abre el drawer con un evento antes del recorrido.
- **Alternativa**: en móvil resaltar solo el botón de hamburguesa con un único paso; se conserva como
  plan de contingencia si abrir el drawer resulta inestable.

## 7. Qué páginas muestran el tour

- **Hecho verificado**: el menú se incluye desde `layouts/app.blade.php` en todas las vistas que extienden
  ese layout.
- **Decisión**: el tour se inicia en cualquier página que tenga el menú. El paso "Buscar" resalta el
  enlace del menú, no el campo de búsqueda (que solo existe en `/buscar`), para mantener el tour en una
  sola página y evitar navegaciones intermedias.

## 8. Estrategia de pruebas

| Comportamiento requerido | Nivel |
|---|---|
| Inicia cuando corresponde; no se repite si se completó; clave por visitante o usuario; filtrado de pasos; robustez sin storage | Vitest sobre `tour-logic.js` |
| Avanza, se cierra, Escape, relanzar, progreso "X de Y", no rompe si falta un selector, teclado | Playwright |
| El HTML incluye `data-tour` en escritorio y móvil y expone el id de usuario o `guest` | PHPUnit Feature |

## 9. Riesgos

1. **Accesibilidad no garantizada por la biblioteca** (sección 4): mitigada con pruebas y ajustes.
2. **CI sin Vitest ni Playwright** (Issue #138): las pruebas del tour dependen de que ese issue se cierre.
3. **Drawer móvil**: abrirlo por evento puede competir con la animación de Alpine; se cubre con una
   espera acotada y el plan de contingencia de la sección 6.
4. **Dependencia nueva**: aumenta el bundle (~5 kB gzip declarado por el proyecto, sin medir aquí).
