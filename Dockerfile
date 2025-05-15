# Utiliser une image de base PHP avec Apache
FROM php:8.1-apache

# Définir les variables d'environnement nécessaires
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    APP_ENV=production \
    APP_DEBUG=false

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    libpq-dev \
    git \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Activer les modules Apache nécessaires
RUN a2enmod rewrite headers

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Créer et définir le répertoire de travail
WORKDIR /var/www/html

# Copier uniquement les fichiers nécessaires pour l'installation des dépendances
COPY composer.json composer.lock ./

# Installer les dépendances PHP
RUN composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction --no-scripts || \
    (echo "Composer install with --ignore-platform-reqs" && \
     composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction --no-scripts --ignore-platform-reqs)

# Copier le reste de l'application
COPY . .

# Créer la structure de dossiers nécessaire
RUN mkdir -p storage/app/public \
    && mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs

# Configurer les permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Créer manuellement le lien symbolique au lieu d'utiliser artisan storage:link
RUN ln -sf /var/www/html/storage/app/public /var/www/html/public/storage

# Copier la configuration Apache
COPY laravel.conf /etc/apache2/sites-available/000-default.conf

# Définir l'utilisateur pour Apache
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# Exposer le port 10000 (port par défaut de Render)
EXPOSE 10000

# Commande de démarrage
CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]