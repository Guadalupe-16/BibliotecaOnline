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
