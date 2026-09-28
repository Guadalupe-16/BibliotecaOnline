# Imagen de liberacion de BibliotecaOnline (Issue #156).
# Guia: docs/cicd/pipeline-liberacion-despliegue.md
#
#   docker build -t bibliotecaonline:<version> .
#
# Etapas: dependencias PHP -> assets con Vite -> imagen final PHP 8.2 + Apache (sin dev deps).

# 1) Dependencias PHP de produccion
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts --no-autoloader
COPY . .
RUN composer dump-autoload --no-dev --optimize --no-scripts

# 2) Assets compilados (Tailwind lee las vistas de paginacion de vendor/)
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js ./
COPY resources ./resources
COPY --from=vendor /app/vendor/laravel/framework/src/Illuminate/Pagination/resources/views \
     ./vendor/laravel/framework/src/Illuminate/Pagination/resources/views
RUN npm run build

# 3) Imagen final
FROM php:8.2-apache

ARG APP_VERSION=dev
LABEL org.opencontainers.image.title="BibliotecaOnline" \
      org.opencontainers.image.version="${APP_VERSION}"

RUN apt-get update \
    && apt-get install -y --no-install-recommends curl \
    && docker-php-ext-install pdo_mysql opcache \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-bibliotecaonline.ini
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

RUN php artisan package:discover --ansi \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views \
       storage/logs storage/app/public bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && echo "${APP_VERSION}" > VERSION

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

ENV APP_VERSION=${APP_VERSION}
EXPOSE 80
HEALTHCHECK --interval=10s --timeout=5s --start-period=30s --retries=12 \
    CMD curl -fsS http://127.0.0.1/up || exit 1

ENTRYPOINT ["entrypoint"]
CMD ["apache2-foreground"]
