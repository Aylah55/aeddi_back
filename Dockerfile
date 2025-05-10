# Utiliser une image de base PHP avec Apache et une version plus récente de PHP
FROM php:8.1-apache

# Installer les dépendances nécessaires
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip

# Activer le module Apache rewrite
RUN a2enmod rewrite

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copier le projet Laravel dans le conteneur
COPY . /var/www/html

# Définir les permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copier le fichier de configuration Apache dans le conteneur
COPY laravel.conf /etc/apache2/sites-available/000-default.conf

# Installer les dépendances PHP
RUN composer install --prefer-dist --no-dev --optimize-autoloader

# Exposer le port 80
EXPOSE 80
