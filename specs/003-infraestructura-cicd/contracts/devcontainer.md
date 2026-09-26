# Contrato: entorno de desarrollo (`.devcontainer/`)

## Garantías tras la preparación automática

| Garantía | Cómo se cumple |
|---|---|
| PHP 8.2 + Composer | Imagen `devcontainers/php:1-8.2-bookworm` |
| Node 20 + npm | Feature `ghcr.io/devcontainers/features/node:1` |
| SQLite (extensión PHP y CLI `sqlite3`) | Extensión incluida en la imagen; CLI instalada por `post-create.sh` |
| Dependencias instaladas | `composer install` y `npm ci` |
| `.env` válido | Copiado de `.env.example` **solo si no existe**, más `php artisan key:generate` |
| Base de datos lista | `database/database.sqlite` migrada con seed |
| Chromium para Playwright | `npx playwright install --with-deps chromium` |
| Puertos | 8000 (Laravel) y 5173 (Vite), reenviados con etiqueta |

## No garantiza

- Credenciales de servicios externos (correo, APIs): el `.env` usa `MAIL_MAILER=log`.
- MySQL: el entorno usa SQLite, igual que las pruebas.
- Recarga en caliente de Vite dentro de Codespaces: se documenta `npm run build` como alternativa.

## Comandos documentados

| Tarea | Comando |
|---|---|
| Levantar la app | `php artisan serve --host=0.0.0.0 --port=8000` |
| Assets en desarrollo | `npm run dev -- --host 0.0.0.0` |
| Assets compilados | `npm run build` |
| PHPUnit | `php artisan test` |
| Vitest | `npm test` |
| Playwright | `npm run build` + servidor levantado + `npx playwright test` |
