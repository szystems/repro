#!/bin/sh
set -eu
cd /var/www

mkdir -p \
    bootstrap/cache \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    public/assets/imgs/users \
    public/assets/imgs/empresas \
    public/assets/imgs/logos
chown -R www-data:www-data bootstrap/cache storage public/assets/imgs || true
chmod -R ug+rwX bootstrap/cache storage public/assets/imgs || true

php artisan package:discover --ansi || true

# Cada deploy/restart aplica migraciones pendientes (evita 500 por columnas nuevas sin SSH).
php artisan migrate --force --no-interaction
php artisan correo:olvidar-alerta --no-interaction 2>/dev/null || true

php-fpm -D
exec nginx -g 'daemon off;'
