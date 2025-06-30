#!/bin/sh

# Installer les dépendances PHP
composer install --no-dev --optimize-autoloader

# Lancer les migrations (facultatif)
php artisan migrate --force

# Lancer le serveur Laravel sur le bon port
php artisan serve --host=0.0.0.0 --port=${PORT}
