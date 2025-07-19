#!/bin/sh

# Installer les dépendances (si pas déjà fait)
if [ ! -d "vendor" ]; then
    composer install --no-dev --optimize-autoloader
fi

# Créer les dossiers nécessaires et définir les permissions
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Vider les caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Exécuter les migrations
php artisan migrate --force

# Démarrer Apache en arrière-plan si on est dans un conteneur Docker
if [ -f "/etc/apache2/apache2.conf" ]; then
    # Mode Docker avec Apache
    apache2-foreground
else
    # Mode Render avec serveur PHP intégré
    php artisan serve --host 0.0.0.0 --port $PORT
fi
