# Contrato: suite E2E (`tests/e2e/`)

## Qué garantiza la suite a quien la ejecuta

| Garantía | Regla |
|---|---|
| Toda prueba tiene aserciones | Ninguna prueba contiene condicionales que omitan sus `expect` |
| Nombre = verificación | El título de cada `test()` describe lo que realmente se comprueba |
| Sin esperas fijas | Prohibido `page.waitForTimeout`; solo aserciones con reintento |
| Sin red externa | Solo se navega a `baseURL` |
| Estado limpio | Las pruebas que mutan datos los restauran (incluso si fallan) |
| Login limitado | Como máximo un login por rol en `auth.setup.js`, más los de US1 |

## Requisitos para ejecutarla

| Requisito | Detalle |
|---|---|
| Base de datos | Migrada y sembrada con `DatabaseSeeder` |
| Assets | `npm run build` (sin build fallan 3 pruebas; spec 003) |
| Servidor | Laravel respondiendo en `PLAYWRIGHT_BASE_URL` (por defecto `http://127.0.0.1:8000`) |
| Navegador | Chromium de Playwright |

## Proyectos de Playwright

| Proyecto | Archivos | Depende de |
|---|---|---|
| `setup` | `tests/e2e/*.setup.js` | — |
| `chromium` | `tests/e2e/*.spec.js` | `setup` |

## Organización por historia

| Historia | Archivo |
|---|---|
| US1 Autenticación | `login.spec.js`, `acceso.spec.js` (invitado) |
| US2 Catálogo y búsqueda | `catalogo.spec.js`, `busqueda.spec.js` |
| US3 Favoritos | `favoritos.spec.js` |
| US4 Roles y seguridad | `acceso.spec.js` (roles) |
| Navegación general | `navegacion.spec.js` |
