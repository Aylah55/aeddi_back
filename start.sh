#!/bin/sh

composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan serve --host 0.0.0.0 --port $PORT
