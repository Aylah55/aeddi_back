#!/bin/sh

echo "🚀 Démarrage de l'application Laravel..."

# Installer les dépendances (si pas déjà fait)
if [ ! -d "vendor" ]; then
    echo "📦 Installation des dépendances Composer..."
    composer install --no-dev --optimize-autoloader
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
php artisan config:show APP_ENV
php artisan config:show APP_DEBUG
php artisan config:show APP_KEY

# Test de connexion à la base de données
echo "🗄️ Test de connexion à la base de données..."
php artisan tinker --execute="try { DB::connection()->getPdo(); echo '✅ Connexion DB OK'; } catch(Exception \$e) { echo '❌ Erreur DB: ' . \$e->getMessage(); }"

# Exécuter les migrations
echo "🔄 Exécution des migrations..."
php artisan migrate --force

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
