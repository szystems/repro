#!/bin/sh
set -eu
cd /var/www

mkdir -p \
    bootstrap/cache \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs
chown -R www-data:www-data bootstrap/cache storage || true
chmod -R ug+rwX bootstrap/cache storage || true

php artisan package:discover --ansi || true

php-fpm -D
exec nginx -g 'daemon off;'
