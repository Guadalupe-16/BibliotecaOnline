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

Edita el archivo `.env` y configura tu base de datos:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=biblioteca_online
DB_USERNAME=root
DB_PASSWORD=

### 5. Generar la clave de la aplicación

php artisan key:generate

### 6. Crear la base de datos

Crea una base de datos llamada `biblioteca_online` en MySQL con cotejamiento `utf8mb4_unicode_ci`.

### 7. Ejecutar las migraciones

php artisan migrate

### 8. Instalar el hook de conventional commits

cp .githooks/prepare-commit-msg .git/hooks/prepare-commit-msg
chmod +x .git/hooks/prepare-commit-msg

### 9. Ejecutar el servidor de desarrollo

En dos terminales simultáneas:

Terminal 1:
php artisan serve

Terminal 2:
npm run dev

### 10. Abrir en el navegador

http://localhost:8000

---

## Flujo de Ramas

- `main` — rama de producción/deploy
- `develop` — rama principal de desarrollo
- `feat/<issue>-<scope>-<descripcion>` — nuevas funcionalidades
- `fix/<issue>-<scope>-<descripcion>` — corrección de errores

---

## Licencia

MIT License — Ver archivo LICENSE para más detalles.