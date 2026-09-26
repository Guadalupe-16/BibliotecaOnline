# Plan de pruebas — BibliotecaOnline

> Issue: #140 · Spec relacionada: [`specs/001-pruebas-e2e-playwright/`](../../specs/001-pruebas-e2e-playwright/spec.md)
> Datos verificados el 2026-09-25 sobre `develop` (`60affd9`).

Cada elemento lleva su estado: **IMPLEMENTADO** (existe), **VALIDADO** (existe y se ejecutó con el
resultado indicado) o **PLANEADO** (propuesta). Los números de este documento salen de ejecuciones
reales (§9); lo que no se ejecutó se dice explícitamente.

---

## 1. Objetivo

Asegurar que cada cambio que llega a `develop` y `main` mantiene funcionando las reglas de negocio,
la autorización por rol y los flujos principales de usuario, con evidencia automática en cada Pull
Request.

## 2. Alcance

**Dentro del alcance**

- Autenticación: login, logout, límite de intentos, recuperación de contraseña, verificación de
  correo con PIN.
- Catálogo, detalle de libro y búsqueda dinámica (Livewire, combos dependientes).
- Favoritos.
- Perfil, CRUD de usuarios, RBAC, panel de superadministrador y gráfica de usuarios.
- Integración con Open Library (con HTTP simulado).
- Calidad estática del JavaScript (ESLint, Prettier).

**Fuera del alcance**

- Pruebas de carga, rendimiento y penetración.
- Navegadores distintos de Chromium.
- Envío real de correos (se usa `MAIL_MAILER=array` en PHPUnit y `log` en E2E).
- Ambientes Staging/Production: no existen (ver [`estrategia-despliegue.md`](estrategia-despliegue.md)).

La cobertura por módulo está en
[`docs/sdd/estado-actual/trazabilidad.md`](../sdd/estado-actual/trazabilidad.md) §2 y no se repite
aquí.

## 3. Estrategia por niveles

| Nivel | Herramienta | Qué verifica | Ubicación | Cantidad | En CI | Estado |
|---|---|---|---|---|---|---|
| Unitario (PHP) | PHPUnit | Modelos: relaciones, casts, métodos de dominio | `tests/Unit/` | 26 pruebas | Sí (`PHPUnit Tests`) | **VALIDADO** |
| Integración / Feature (PHP) | PHPUnit | Rutas, controladores, autorización, validación, BD, correo y colas simulados | `tests/Feature/` | 92 pruebas | Sí (`PHPUnit Tests`) | **VALIDADO** |
| End-to-end | Playwright (Chromium) | Flujos en navegador real contra la app levantada | `tests/e2e/` | 12 pruebas | Sí (`E2E Playwright`, desde #138) | **VALIDADO** |
| Unitario (JS) | Vitest (jsdom) | Funciones JS de validación, búsqueda, filtros, favoritos | `resources/js/tests/` | 18 pruebas | No | **IMPLEMENTADO** (ver nota) |
| Pruebas grabadas | Selenium IDE | 7 flujos grabados (login, búsqueda, catálogo, favoritos, panel de logs, gestión de usuarios, Open Library) | `docs/selenium/BibliotecaOnline.side` | 7 pruebas | No | **IMPLEMENTADO** — no ejecutado en esta revisión |
| Estático | ESLint + Prettier | Errores y formato en `resources/js` | `eslint.config.js` | — | Sí (`ESLint + Prettier`) | **VALIDADO** |

Notas:

- **Vitest**: los 4 archivos redefinen dentro del test las funciones que prueban, en lugar de
  importarlas del código de la aplicación; por eso no protegen contra regresiones del código real
  (ver trazabilidad). No corre en CI aunque la constitución lo pide como puerta. **PLANEADO**:
  refactorizar para importar desde `resources/js/` y agregar `npm test` al CI.
- **Playwright**: la suite actual tiene defectos (una prueba sin aserciones, nombres engañosos) y no
  cubre flujos con sesión. Su evolución está especificada en la spec 001 (§11).
- **Selenium IDE**: es evidencia de pruebas manuales asistidas; requiere la extensión del
  navegador y datos locales. No se automatiza.

## 4. Datos de prueba

| Nivel | Base de datos | Datos |
|---|---|---|
| PHPUnit | SQLite en memoria (`phpunit.xml`) | Factories por prueba (`RefreshDatabase`) |
| Playwright | SQLite en archivo, `migrate --seed` | `DatabaseSeeder`: `test@example.com`, `BibliotecaSeeder` (6 categorías, 5 autores, libros), `RolesSeeder` |
| Vitest | Ninguna | Valores en el propio test |
| Selenium IDE | Base local de quien ejecuta | Usuarios y libros locales |

Usuarios del `RolesSeeder` (datos de desarrollo, no secretos):

| Rol | Correo |
|---|---|
| `superadmin` | `superadmin@biblioteca.com` |
| `admin` | `admin@biblioteca.com` |
| `usuario` | `usuario@biblioteca.com` |

Las contraseñas están en `database/seeders/RolesSeeder.php`.

## 5. Ambientes

| Ambiente | Uso | Estado |
|---|---|---|
| Local (máquina de cada integrante) | Todas las suites | **IMPLEMENTADO** |
| GitHub Actions (`ubuntu-latest`) | PHPUnit, E2E, ESLint/Prettier en cada push/PR a `main` y `develop` | **VALIDADO** |
| Devcontainer / Codespaces | Todas las suites con herramientas preinstaladas | **IMPLEMENTADO**, no validado (#138) |
| Staging | Validación antes de producción | **PLANEADO** |

## 6. Criterios de entrada y salida

**Entrada** (para empezar a probar un cambio):

- Existe un Issue y, si es un cambio significativo, su spec (constitución, principio I).
- La rama parte de `develop` actualizado.
- Las dependencias se instalan con `composer install` y `npm ci` sin errores.

**Salida** (para mergear un PR a `develop`):

- Los 3 checks del CI en verde: `ESLint + Prettier`, `PHPUnit Tests`, `E2E Playwright`.
- `npm test` pasa en local (mientras no esté en CI).
- Un cambio funcional incluye sus pruebas; una corrección de bug incluye la prueba que lo reproduce
  (principio IV).
- Ninguna prueba desactivada o eliminada para pasar el CI (principio VII).
- Sin defectos abiertos de severidad Crítica o Alta relacionados con el cambio.
- Aprobación de un integrante distinto del autor.

## 7. Severidad y prioridad de defectos

| Severidad | Definición | Ejemplo en el proyecto | Acción |
|---|---|---|---|
| **Crítica** | Expone datos o permite acciones sin autorización; pérdida de datos | Ruta `/login-super` que iniciaba sesión como superadmin sin credenciales (#116, corregido) | Bloquea el merge; se corrige de inmediato |
| **Alta** | Un flujo principal no funciona o un rol accede a lo que no debe | `/usuarios` accesible para cualquier usuario con sesión (abierto, ver §10) | Bloquea la liberación; Issue `fix/` con prioridad |
| **Media** | Funciona con errores o hay un flujo alterno roto | Prueba E2E que pasa sin verificar (detalle de libro) | Se planifica en el siguiente ciclo |
| **Baja** | Cosmético o de mantenimiento | Advertencia de ESLint `no-unused-vars` | Se corrige cuando se toque el archivo |

**Prioridad** (orden de atención): P1 bloquea el merge o la liberación · P2 se corrige en el ciclo
actual · P3 se agenda. En general, Crítica → P1, Alta → P1/P2, Media → P2, Baja → P3.

## 8. Comandos

| Suite | Comando | Requisitos |
|---|---|---|
| PHPUnit (todo) | `php artisan test` | `composer install`, `.env` con `APP_KEY` |
| PHPUnit por suite | `php artisan test --testsuite=Unit` / `--testsuite=Feature` | Ídem |
| Cobertura PHP | `php artisan test --coverage` | Xdebug o PCOV (CI usa Xdebug); reporte HTML histórico en `docs/coverage/` (#102) |
| Vitest | `npm test` | `npm ci` |
| Cobertura JS | `npm run test:coverage` | `npm ci` |
| ESLint | `npm run lint` | `npm ci` |
| Prettier | `npm run format:check` | `npm ci` |
| Playwright | `npx playwright test` | Base sembrada, `npm run build`, servidor en `:8000` (o `PLAYWRIGHT_BASE_URL`), `npx playwright install chromium` |
| Selenium IDE | Abrir `docs/selenium/BibliotecaOnline.side` en la extensión | App local con datos |

Guía paso a paso del E2E: README › "Pruebas E2E con Playwright".

## 9. Ejecución real

**Fecha**: 2026-09-25 · **Commit**: `60affd9` (`develop`) · **Máquina**: macOS, PHP 8.3.31, Node
25.9.0, copia limpia del repositorio (`.env` desde `.env.example`, `npm ci`, `composer install`).

| Suite | Comando | Resultado | Fallos |
|---|---|---|---|
| PHPUnit Unit | `php artisan test --testsuite=Unit` | **26 pasadas** (28 aserciones) | 0 |
| PHPUnit Feature | `php artisan test --testsuite=Feature` | **92 pasadas** (181 aserciones) | 0 |
| PHPUnit total | `php artisan test` | **118 pasadas** (209 aserciones) en 1.49 s | 0 |
| Vitest | `npm test` | **18 pasadas** en 4 archivos | 0 |
| ESLint | `npm run lint` | 0 errores, **1 advertencia** | `resources/js/tests/favorito.test.js:3` — `'esFavoritoActual' is defined but never used` |
| Prettier | `npm run format:check` | Todos los archivos con formato correcto | 0 |
| Playwright | `npx playwright test` (con base sembrada y `npm run build`) | **12 pasadas** en 5.6 s | 0 (ver defectos de la suite en §10) |
| Playwright sin build | `npx playwright test` sin `npm run build` | 3 fallidas / 11 pasadas (suite de 14, antes de #138) | Buscador dinámico, menú lateral, "formulario de contacto" |
| Selenium IDE | — | **No ejecutado** en esta revisión | — |
| Cobertura PHP | — | **No ejecutada**: la máquina de la revisión no tiene Xdebug ni PCOV | — |

### Evidencia en GitHub Actions — VALIDADO

| Run | Evento | Checks | Enlace |
|---|---|---|---|
| 36205499377 | PR #146 (`dfcccd4`) | `ESLint + Prettier` ✓ 11 s · `PHPUnit Tests` ✓ 22 s · `E2E Playwright` ✓ 1 min 9 s; artefacto `playwright-report` | https://github.com/Guadalupe-16/BibliotecaOnline/actions/runs/36205499377 |
| 36205506706 | Push a `develop` (`60affd9`) | Todos ✓ | https://github.com/Guadalupe-16/BibliotecaOnline/actions/runs/36205506706 |

## 10. Defectos y hallazgos abiertos

| ID | Hallazgo | Severidad | Prioridad | Seguimiento |
|---|---|---|---|---|
| H-01 | `/usuarios` (listar, editar, **eliminar**) solo exige sesión: cualquier `usuario` puede administrar usuarios. `UsuarioControllerTest` valida el comportamiento actual | Alta | P1 | Proponer Issue `fix/`; prueba E2E en spec 001 (T025) |
| H-02 | La prueba E2E "detalle de un libro" pasa sin aserciones (0 enlaces coinciden con su selector) | Media | P2 | Spec 001, T011 |
| H-03 | 2 pruebas E2E con nombre que no coincide con lo que verifican; `contacto.spec.js` duplica catálogo y login | Media | P2 | Spec 001, T026–T027 |
| H-04 | Sin E2E de favoritos, roles, login exitoso ni logout | Media | P2 | Spec 001, US1–US4 |
| H-05 | Vitest no importa código de la app y no corre en CI | Media | P2 | PLANEADO (§3) |
| H-06 | Advertencia ESLint `no-unused-vars` en `favorito.test.js` | Baja | P3 | Corregir al tocar el archivo |

## 11. Relación con la spec 001

[`specs/001-pruebas-e2e-playwright/`](../../specs/001-pruebas-e2e-playwright/spec.md) especifica la
evolución del nivel E2E de este plan:

| Plan de pruebas | Spec 001 |
|---|---|
| Nivel E2E (§3) | Historias US1 Autenticación, US2 Catálogo y búsqueda, US3 Favoritos, US4 Roles y seguridad |
| Datos de prueba (§4) | Sesión guardada por rol del `RolesSeeder` (research.md §3–4) |
| Criterios de salida (§6) | SC-002 (sin pruebas vacías), SC-004 (3 ejecuciones estables) |
| Hallazgos H-01 a H-04 (§10) | Tareas T011, T025–T027 y fases 3–6 de `tasks.md` |
| Herramientas de IA | Playwright MCP: **PLANEADO**, con razones en research.md §7 |

Estado: la spec, el plan, las tareas y el análisis están completos (#140). La escritura de las
pruebas nuevas (T004–T030) es **PLANEADO**; cuando se implementen, se actualizarán §3, §9 y §10.
