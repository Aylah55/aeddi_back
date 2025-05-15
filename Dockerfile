# Utiliser une image de base PHP avec Apache
FROM php:8.1-apache

# Installer les dépendances nécessaires pour PostgreSQL et d'autres extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip

# Activer les modules Apache rewrite ET headers
RUN a2enmod rewrite headers

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Créer un utilisateur non-root pour exécuter Composer et l'application
RUN groupadd -r aeddi && useradd -r -g aeddi aeddi

# Créer le répertoire de l'application
RUN mkdir -p /var/www/html && chown -R aeddi:aeddi /var/www/html

# Copier le projet Laravel dans le conteneur
COPY --chown=aeddi:aeddi . /var/www/html

# Se déplacer dans le répertoire de travail
WORKDIR /var/www/html

# Définir les permissions
RUN chmod -R 775 storage bootstrap/cache

# Installer les dépendances PHP en tant qu'utilisateur non-root
USER aeddi
RUN composer install --prefer-dist --no-dev --optimize-autoloader

# Créer le lien symbolique storage/public
RUN php artisan storage:link

# Nettoyer le cache de configuration
RUN php artisan config:clear && php artisan config:cache

# Revenir à root pour les commandes système
USER root

# Copier le fichier de configuration Apache dans le conteneur
COPY laravel.conf /etc/apache2/sites-available/000-default.conf

# Définir les permissions pour Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Définir l'utilisateur pour Apache
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# Exposer le port 80
EXPOSE 80