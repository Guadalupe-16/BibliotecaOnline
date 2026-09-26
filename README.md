# 📚 Biblioteca Digital Online

Sistema web de biblioteca digital desarrollado con el TALL Stack (Tailwind, Alpine.js, Laravel, Livewire).

## Equipo de Desarrollo

- Ibarra Rubalcava Joel Armando
- Martínez Delgado Jorge Humberto
- Romo Bernal Javier Antonio
- Amavizca Quinter Guadalupe

## Repositorio

https://github.com/Guadalupe-16/BibliotecaOnline

---

## Requisitos Previos

Asegúrate de tener instalado:

- PHP 8.x
- Composer 2.x
- Node.js 18+ y npm
- MySQL 8.x o superior
- Git

---

## Instalación y Configuración

### 1. Clonar el repositorio

git clone https://github.com/Guadalupe-16/BibliotecaOnline.git
cd BibliotecaOnline

### 2. Instalar dependencias de PHP

composer install

### 3. Instalar dependencias de Node

npm install

### 4. Configurar variables de entorno

cp .env.example .env

### 5. Crear la carpeta .git/hooks

touch .git/hooks/prepare-commit-msg **PREGUNTAR POR CODIGO**
chmod +x .git/hooks/prepare-commit-msg

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
# setup

---

## Acceso al panel de superadministrador

El panel vive en `/superadmin` y exige **sesión iniciada** y **rol `superadmin`**.
La única forma de entrar es por `/login` con una cuenta que tenga ese rol.

### Nota de seguridad (issue #116)

Existía una ruta temporal `/login-super` que iniciaba sesión automáticamente como
el usuario `super@test.com` sin pedir credenciales. Cualquiera con el enlace obtenía
control total del panel (cambiar roles, activar y desactivar usuarios).

**La ruta fue eliminada.** Ya no existe en ningún entorno y responde `404`.
Si necesitas una cuenta de superadmin para desarrollo, créala por seeder o por
`php artisan tinker` asignando `rol = 'superadmin'`, e inicia sesión por `/login`.

La regresión está cubierta por `tests/Feature/RutaLoginSuperTest.php`:

    php artisan test --filter=RutaLoginSuperTest

Evidencia del antes y después: `docs/evidencias/issue-116/`.

---

## Entorno de desarrollo con Dev Containers / Codespaces

Definido en `.devcontainer/` (issue #138, spec `specs/003-infraestructura-cicd/`).

> **Estado:** IMPLEMENTADO, **no validado**. Los archivos existen, pero todavía no se ha probado
> la creación del entorno en Codespaces ni en Docker local. Si lo pruebas, registra el resultado en
> `specs/003-infraestructura-cicd/analysis.md`.

| Elemento | Valor |
|---|---|
| Imagen | `mcr.microsoft.com/devcontainers/php:1-8.2-bookworm` (PHP 8.2 + Composer) |
| Node | 20 (feature `ghcr.io/devcontainers/features/node:1`) |
| Base de datos | SQLite en `database/database.sqlite` |
| Puertos | `8000` Laravel, `5173` Vite |
| Secretos | Ninguno. El `.env` se crea desde `.env.example` (`MAIL_MAILER=log`) |

### Cómo abrirlo

- **Codespaces:** en GitHub, *Code → Codespaces → Create codespace on <rama>*.
- **VS Code local:** requiere Docker y la extensión *Dev Containers*; comando
  *Dev Containers: Reopen in Container*.

Al crearse, `.devcontainer/post-create.sh` instala `sqlite3`, ejecuta `composer install` y `npm ci`,
crea `.env` y `APP_KEY` (solo si no existe `.env`), migra la base con seeders (solo la primera vez)
e instala Chromium para Playwright.

### Comandos dentro del contenedor

    php artisan serve --host=0.0.0.0 --port=8000   # abrir el puerto 8000 reenviado
    npm run build                                  # assets compilados (recomendado en Codespaces)
    npm run dev -- --host 0.0.0.0                  # assets en modo desarrollo
    php artisan test                               # PHPUnit
    npm test                                       # Vitest
    npx playwright test                            # E2E (requiere npm run build y el servidor arriba)

---

## Pruebas E2E con Playwright

Las pruebas viven en `tests/e2e/` y se ejecutan en CI en el job `E2E Playwright`
(`.github/workflows/ci.yml`), que publica el reporte HTML como artefacto `playwright-report`.

Para ejecutarlas en local:

    npm ci
    npx playwright install chromium
    npm run build                  # obligatorio: sin assets compilados fallan 3 pruebas
    php artisan serve              # en otra terminal
    npx playwright test

Si el puerto 8000 está ocupado, levanta el servidor en otro puerto y usa
`PLAYWRIGHT_BASE_URL=http://127.0.0.1:8100 npx playwright test`.
