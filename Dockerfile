# Utiliser une image de base PHP avec Apache
FROM php:8.1-apache

# Définir les variables d'environnement nécessaires
ENV COMPOSER_ALLOW_SUPERUSER=1

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip

# Activer les modules Apache nécessaires
RUN a2enmod rewrite headers

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Créer et définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers du projet
COPY . .

# Installer les dépendances PHP
RUN composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction

# Configurer les permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Créer le lien symbolique storage
RUN php artisan storage:link

# Nettoyer le cache
RUN php artisan config:clear \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Copier la configuration Apache
COPY laravel.conf /etc/apache2/sites-available/000-default.conf

# Définir l'utilisateur pour Apache
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# Définir le répertoire de travail
WORKDIR /var/www/html

# Exposer le port 10000 (port par défaut de Render)
EXPOSE 10000

# Commande de démarrage
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]