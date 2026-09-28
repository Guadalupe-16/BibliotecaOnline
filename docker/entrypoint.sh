#!/bin/sh
# Arranque del contenedor de liberacion (Issue #156).
#   RUN_MIGRATIONS=true  -> espera la BD, migra y siembra solo si la BD es nueva (servicio "app")
#   RUN_MIGRATIONS=false -> solo espera la BD (servicio "worker")
set -e

cd /var/www/html

echo "[entrypoint] BibliotecaOnline $(cat VERSION 2>/dev/null)"

echo "[entrypoint] Esperando la base de datos ${DB_HOST}:${DB_PORT}..."
intentos=0
until php -r '
    try {
        new PDO("mysql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT").";dbname=".getenv("DB_DATABASE"),
                getenv("DB_USERNAME"), getenv("DB_PASSWORD"));
    } catch (Throwable $e) { exit(1); }
' 2>/dev/null; do
    intentos=$((intentos + 1))
    if [ "$intentos" -ge 60 ]; then
        echo "[entrypoint] La base de datos no respondio en 120 s" >&2
        exit 1
    fi
    sleep 2
done

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force

    # Datos iniciales solo si el catalogo esta vacio (primer despliegue o arranque interrumpido).
    # DatabaseSeeder no sirve aqui: usa factories con Faker, que es dependencia de desarrollo.
    libros=$(php -r '
        $pdo = new PDO("mysql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT").";dbname=".getenv("DB_DATABASE"),
                       getenv("DB_USERNAME"), getenv("DB_PASSWORD"));
        echo $pdo->query("SELECT COUNT(*) FROM libros")->fetchColumn();
    ')
    if [ "$libros" = "0" ] && [ "${SEED_ON_FIRST_DEPLOY:-true}" = "true" ]; then
        for clase in ${SEED_CLASSES:-BibliotecaSeeder RolesSeeder}; do
            echo "[entrypoint] Catalogo vacio: sembrando ${clase}"
            php artisan db:seed --class="$clase" --force
        done
    fi

    php artisan storage:link --force >/dev/null 2>&1 || true
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
