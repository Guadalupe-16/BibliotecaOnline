# Data Model: Pruebas E2E (spec 001)

No hay entidades nuevas en la aplicación. Estas son las entidades de la suite.

## Usuario de prueba

| Campo | Valor |
|---|---|
| `rol` | `usuario` \| `admin` \| `superadmin` |
| `email` | `<rol>@biblioteca.com` |
| `password` | definida en `database/seeders/RolesSeeder.php` |
| `email_verified_at` | no nulo (el seeder los crea verificados) |

Regla: se leen de `tests/e2e/helpers/usuarios.js`; ninguna prueba escribe credenciales en línea.

## Sesión guardada (`storageState`)

| Campo | Valor |
|---|---|
| Ruta | `playwright/.auth/<rol>.json` (ignorada por git) |
| Creada por | `tests/e2e/auth.setup.js` (proyecto `setup`) |
| Vida | Una ejecución de la suite |

Estados: `inexistente → creada por setup → reutilizada por las pruebas`.

## Libro de prueba

| Campo | Regla |
|---|---|
| Origen | `BibliotecaSeeder` |
| Selección | Primera tarjeta del catálogo (`a[href*="/libros/"]`) o por título exacto del seeder en búsqueda |
| Estado de favorito | Debe ser "no favorito" al iniciar y al terminar cada prueba de US3 |

## Matriz de acceso esperada (US1, US4)

| Ruta | Invitado | `usuario` | `admin` | `superadmin` |
|---|---|---|---|---|
| `/favoritos`, `/perfil`, `/dashboard` | → `/login` | 200 | 200 | 200 |
| `/admin/roles` | → `/login` | 403 | 200 | 200 |
| `/superadmin` | → `/login` | 403 | 403 | 200 |
| `/usuarios` | → `/login` | **403 esperado, 200 hoy** (defecto) | 200 | 200 |
