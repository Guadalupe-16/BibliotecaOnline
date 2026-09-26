#!/usr/bin/env bash
# Preparacion del devcontainer de BibliotecaOnline (Issue #138, spec 003).
# No usa secretos: el .env sale de .env.example (SQLite, MAIL_MAILER=log).
set -euo pipefail

echo "==> Instalando cliente sqlite3"
sudo apt-get update -y
sudo apt-get install -y --no-install-recommends sqlite3

echo "==> Instalando dependencias PHP y JS"
composer install --no-interaction --prefer-dist
npm ci

if [ ! -f .env ]; then
  echo "==> Creando .env desde .env.example"
  cp .env.example .env
  php artisan key:generate
else
  echo "==> .env ya existe, no se sobrescribe"
fi

echo "==> Preparando base de datos SQLite"
if [ ! -f database/database.sqlite ]; then
  touch database/database.sqlite
  php artisan migrate --seed --force
else
  # Al reconstruir el contenedor la base ya existe: el seed duplicaria el usuario de prueba
  php artisan migrate --force
fi

echo "==> Instalando Chromium para Playwright"
npx playwright install --with-deps chromium

echo "==> Listo. Ver README.md > Entorno de desarrollo con Dev Containers / Codespaces"
