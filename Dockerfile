FROM php:8.2-apache

# Installe les dépendances système
RUN apt-get update && apt-get install -y \
    git zip unzip curl libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl

# Active mod_rewrite d'Apache
RUN a2enmod rewrite

# Copie le code Laravel dans le conteneur
COPY . /var/www/html

# Change les permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Copie le fichier virtual host Laravel
COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf

# Installe Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Passe à /var/www/html comme répertoire de travail
WORKDIR /var/www/html

# Installe les dépendances Laravel
RUN composer install --no-dev --optimize-autoloader

# Lancer les migrations (si tu veux)
# RUN php artisan migrate --force

EXPOSE 80
