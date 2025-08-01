#!/bin/sh

echo "🚀 Démarrage de l'application Laravel..."

# Définir les variables d'environnement par défaut si elles n'existent pas
export APP_ENV=${APP_ENV:-production}
export APP_DEBUG=${APP_DEBUG:-false}
export APP_KEY=${APP_KEY:-base64:lRfSzEBXmA2GXhNb0YjZVhogf5AEZa7DVgE9Vf4d+ko=}
export APP_URL=${APP_URL:-https://aeddi-back.onrender.com}
export DB_CONNECTION=${DB_CONNECTION:-pgsql}
export DB_HOST=${DB_HOST:-dpg-d262i1ffte5s73e6iit0-a.oregon-postgres.render.com}
export DB_PORT=${DB_PORT:-5432}
export DB_DATABASE=${DB_DATABASE:-aeddi_db}
export DB_USERNAME=${DB_USERNAME:-aeddi_db_user}
export DB_PASSWORD=${DB_PASSWORD:-}
export LOG_CHANNEL=${LOG_CHANNEL:-stack}
export LOG_LEVEL=${LOG_LEVEL:-error}
export CACHE_DRIVER=${CACHE_DRIVER:-file}
export SESSION_DRIVER=${SESSION_DRIVER:-file}
export QUEUE_CONNECTION=${QUEUE_CONNECTION:-sync}
export SANCTUM_STATEFUL_DOMAINS=${SANCTUM_STATEFUL_DOMAINS:-localhost:3000,aeddi-front.onrender.com}
export SESSION_DOMAIN=${SESSION_DOMAIN:-aeddi-front.onrender.com}
export FRONTEND_URL=${FRONTEND_URL:-https://aeddi-front.onrender.com}

# Installer les dépendances (si pas déjà fait)
if [ ! -d "vendor" ]; then
    echo "📦 Installation des dépendances Composer..."
    composer install --no-dev --optimize-autoloader
fi

# Vérifier que Socialite est installé
echo "🔍 Vérification de Laravel Socialite..."
if [ ! -d "vendor/laravel/socialite" ]; then
    echo "❌ Laravel Socialite n'est pas installé. Installation..."
    composer require laravel/socialite
else
    echo "✅ Laravel Socialite est installé"
fi

# Créer les dossiers nécessaires et définir les permissions
echo "📁 Création des dossiers et permissions..."
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Vider les caches
echo "🧹 Nettoyage des caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Vérifier la configuration
echo "🔧 Vérification de la configuration..."
echo "APP_ENV: $APP_ENV"
echo "APP_DEBUG: $APP_DEBUG"
echo "APP_KEY: $APP_KEY"

# Test de connexion à la base de données
echo "🗄️ Test de connexion à la base de données..."
php artisan tinker --execute="try { DB::connection()->getPdo(); echo '✅ Connexion DB OK'; } catch(Exception \$e) { echo '❌ Erreur DB: ' . \$e->getMessage(); }"

# Exécuter les migrations
echo "🔄 Exécution des migrations..."
php artisan migrate --force

# Créer le lien symbolique pour le stockage
echo "🔗 Création du lien symbolique pour le stockage..."
php artisan storage:link

# Vérifier les routes
echo "🛣️ Vérification des routes..."
php artisan route:list --compact

# Démarrer le serveur
echo "🌐 Démarrage du serveur..."
if [ -f "/etc/apache2/apache2.conf" ]; then
    # Mode Docker avec Apache
    echo "🐳 Mode Docker avec Apache"
    apache2-foreground
else
    # Mode Render avec serveur PHP intégré
    echo "☁️ Mode Render avec serveur PHP intégré sur le port $PORT"
    php artisan serve --host 0.0.0.0 --port $PORT
fi
