#!/bin/sh
set -eu
cd /var/www

# Logos y fotos viven en el volumen /var/www/storage. public/assets/imgs
# solo es un enlace: un deploy nuevo no borra los archivos ya subidos.
IMG_PERSISTENTE=/var/www/storage/app/public/assets/imgs
mkdir -p \
    bootstrap/cache \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    "$IMG_PERSISTENTE/users" \
    "$IMG_PERSISTENTE/empresas" \
    "$IMG_PERSISTENTE/logos" \
    public/assets

if [ -d public/assets/imgs ] && [ ! -L public/assets/imgs ]; then
    cp -a public/assets/imgs/. "$IMG_PERSISTENTE/"
    rm -rf public/assets/imgs
fi
ln -sfn "$IMG_PERSISTENTE" /var/www/public/assets/imgs

chown -R www-data:www-data bootstrap/cache storage || true
chmod -R ug+rwX bootstrap/cache storage || true

php artisan package:discover --ansi || true

# Cada deploy/restart aplica migraciones pendientes (evita 500 por columnas nuevas sin SSH).
php artisan migrate --force --no-interaction
php artisan correo:olvidar-alerta --no-interaction 2>/dev/null || true

php-fpm -D
exec nginx -g 'daemon off;'
