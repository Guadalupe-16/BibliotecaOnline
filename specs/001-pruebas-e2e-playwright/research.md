# Research: Pruebas E2E con Playwright (spec 001)

**Fecha**: 2026-09-25 · **Issue**: #140

Etiquetas: **IMPLEMENTADO**, **VALIDADO**, **PLANEADO**.

---

## 1. Línea base de la suite — VALIDADO

Ejecución real en una copia limpia de `develop` (`60affd9`), 2026-09-25, con base SQLite sembrada y
`npm run build`:

| Archivo | Prueba | Resultado |
|---|---|---|
| `catalogo.spec.js` | muestra la página del catálogo correctamente | ✓ |
| `catalogo.spec.js` | muestra la página de detalle de un libro | ✓ **(sin aserciones, ver §2)** |
| `catalogo.spec.js` | muestra el buscador dinámico | ✓ |
| `contacto.spec.js` | muestra el catalogo correctamente | ✓ |
| `contacto.spec.js` | muestra el login correctamente | ✓ |
| `login.spec.js` | muestra la página de login correctamente | ✓ |
| `login.spec.js` | muestra error con credenciales incorrectas | ✓ |
| `login.spec.js` | redirige al login si no está autenticado | ✓ |
| `navegacion.spec.js` | muestra el menu lateral correctamente | ✓ |
| `navegacion.spec.js` | navega al catalogo correctamente | ✓ |
| `navegacion.spec.js` | navega a la pagina about | ✓ **(no abre about)** |
| `navegacion.spec.js` | navega al formulario de contacto | ✓ **(no abre contacto)** |

**Total: 12 pasadas en 5.6 s.** En CI: run
[36205499377](https://github.com/Guadalupe-16/BibliotecaOnline/actions/runs/36205499377), job
`E2E Playwright` en verde (1 min 9 s).

## 2. Defectos de la suite actual — VALIDADO

| Prueba | Problema | Evidencia |
|---|---|---|
| detalle de un libro | El selector `a` con texto `/ver\|detalle\|más/i` encuentra **0** elementos (las tarjetas del catálogo son `<a href="/libros/{id}">` sin esos textos) y el `if (count > 0)` deja pasar la prueba sin aserciones | Verificado con Playwright contra la base sembrada (spec 003, research.md §6) |
| navega a la pagina about | Abre `/catalogo` y verifica un `h1`; no hay ruta `/about` (`about.blade.php` existe, pero sin ruta) | `php artisan route:list`; `docs/sdd/estado-actual/trazabilidad.md` |
| navega al formulario de contacto | Abre `/catalogo` y verifica el `aside`; no hay ruta `/contacto` | Ídem |
| grupo "Formulario de contacto" | Sus 2 pruebas verifican catálogo y login (duplicados) | `tests/e2e/contacto.spec.js` |

Decisiones:
- **Detalle**: hacer clic en la primera tarjeta (`a[href*="/libros/"]`), sin `if`; verificar URL y
  título.
- **about/contacto**: mientras no existan las rutas, las 2 pruebas se renombran a lo que realmente
  verifican (enlaces del menú lateral). Si se agregan rutas en otro Issue, se agregan sus pruebas.
- **contacto.spec.js**: se elimina (duplicado). No contradice el principio VII: no se elimina para
  pasar el CI (pasa) sino porque no prueba lo que dice.

## 3. Autenticación en las pruebas

- **Decision**: proyecto `setup` (`tests/e2e/auth.setup.js`) que inicia sesión por la UI con cada
  usuario del `RolesSeeder` y guarda `playwright/.auth/{usuario,admin,superadmin}.json`. Las pruebas
  usan `test.use({ storageState: ... })`. `playwright/.auth/` va a `.gitignore`.
- **Rationale**: el limitador `login` permite 5 intentos por minuto por `email|ip`
  (`AppServiceProvider::registrarLimitesDePeticiones`). Con sesiones guardadas hay 3 logins por
  ejecución. Es el patrón recomendado por la documentación de Playwright.
- **Alternatives considered**: login por UI en cada prueba (choca con el límite); crear la sesión
  por una ruta de test (agrega una ruta insegura, justo lo que se eliminó en #116); desactivar el
  limitador en E2E (cambia el comportamiento que se prueba).
- Las pruebas de login/logout (US1) sí usan el formulario, en un contexto propio y una sola vez,
  para no superar el límite.
- **Ninguna prueba cierra sesión con una sesión guardada**: `logout` invalida la sesión en el
  servidor (`SESSION_DRIVER=database`) y las demás pruebas que reutilizan ese `storageState`
  quedarían sin sesión.

## 4. Datos de prueba

| Dato | Origen | Uso |
|---|---|---|
| `usuario@biblioteca.com` / `Usuario123!` | `RolesSeeder` | US1, US3, US4 |
| `admin@biblioteca.com` / `Admin123!` | `RolesSeeder` | US4 |
| `superadmin@biblioteca.com` / `Superadmin123!` | `RolesSeeder` | US4 |
| 6 categorías, 5 autores y libros | `BibliotecaSeeder` | US2, US3 |

Son datos de desarrollo versionados en el seeder, no secretos. Se centralizan en
`tests/e2e/helpers/usuarios.js`.

## 5. Aislamiento y estabilidad

- `workers: 1` y `fullyParallel: false` se mantienen (ya configurados): la base es compartida.
- Favoritos: cada prueba parte de "libro no favorito" y termina quitándolo; un `afterEach` quita el
  favorito si la prueba falló a medias (FR-007).
- Búsqueda: el componente tiene `debounce.300ms`; se usan aserciones con reintento automático
  (`expect(...).toBeVisible()`, `toHaveText`) en vez de `waitForTimeout` (FR-008).
- 403: `VerificarRol` responde `abort(403)`; se verifica con el `status()` de la respuesta de
  `page.goto`.

## 6. Defecto de la aplicación encontrado — `/usuarios` sin restricción de rol

- `routes/web.php`: `/usuarios`, `/usuarios/{id}/edit`, `PUT` y `DELETE /usuarios/{id}` están en un
  grupo con solo `auth`. Cualquier usuario con sesión puede listar, editar y **eliminar** usuarios.
- `UsuarioControllerTest` prueba el CRUD con un usuario sin rol especial, es decir, valida el
  comportamiento actual.
- Ya figura en `docs/sdd/estado-actual/trazabilidad.md` ("Implementado; **sin restricción de rol**").
- **Decision**: proponer un Issue `fix/` (agregar `role:admin,superadmin` y actualizar
  `UsuarioControllerTest`). El escenario US4-5 se implementa junto con esa corrección (FR-010). No se
  agrega hoy una prueba que falle ni una prueba desactivada.

## 7. Playwright MCP — decisión: PLANEADO

- **Qué es**: servidor MCP (`@playwright/mcp`) que permite a un agente de IA (Claude Code, Copilot)
  controlar un navegador por el árbol de accesibilidad.
- **Estado**: no está configurado en el repositorio (no hay `.mcp.json`).
- **Decision**: no integrarlo en esta spec; queda como **PLANEADO**.
- **Rationale**:
  1. No aporta a la ejecución de la suite: el CI ejecuta pruebas escritas en código con
     `@playwright/test`; el MCP no participa en ese flujo.
  2. Su valor es de autoría (exploración y generación de pruebas asistida por IA), no de
     verificación; la calidad de las pruebas generadas se revisaría igual a mano.
  3. Agrega una dependencia y configuración por máquina que el equipo tendría que mantener, sin
     criterio de aceptación en el Issue que dependa de ella.
- **Cuándo reconsiderarlo**: si el equipo quiere usar un agente para escribir las ~18 pruebas
  nuevas de `tasks.md`, agregar `.mcp.json` con `npx @playwright/mcp@latest` y documentar su uso en
  `docs/sdd/`.

## 8. Otras brechas (fuera de alcance)

| Brecha | Estado |
|---|---|
| Vitest no corre en CI y sus 4 archivos no importan código de la app (redefinen las funciones dentro del test) | PLANEADO — ver `docs/sdd/estado-actual/trazabilidad.md` |
| Registro y verificación con PIN sin E2E (requieren correo) | Cubierto por PHPUnit; E2E fuera de alcance |
| Selenium IDE (7 pruebas grabadas) no se ejecuta en CI | Documentación de pruebas manuales |
| Panel de logs, chat de soporte y sitemap sin pruebas automáticas | PLANEADO — trazabilidad |
