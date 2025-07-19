FROM php:8.2-apache

# Installe les dépendances système nécessaires, y compris libcurl4-openssl-dev
RUN apt-get update && apt-get install -y \
    git zip unzip curl libpng-dev libonig-dev libxml2-dev libzip-dev libcurl4-openssl-dev \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl curl

# Active mod_rewrite et headers pour .htaccess (important pour Sanctum / CORS)
RUN a2enmod rewrite headers

# Installe Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copie le projet Laravel
COPY . /var/www/html

# Passe à /var/www/html comme répertoire de travail
WORKDIR /var/www/html

# Crée les dossiers nécessaires et définit les permissions
RUN mkdir -p /var/www/html/storage/framework/cache \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/storage/logs \
    && chown -R www-data:www-data /var/www/html/storage \
    && chown -R www-data:www-data /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Installe les dépendances Laravel
RUN composer install --no-dev --optimize-autoloader

# Copie la configuration Apache personnalisée
COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf

# Script de démarrage
COPY ./start.sh /start.sh
RUN chmod +x /start.sh

# Expose le port 80
EXPOSE 80

# Commande de démarrage
CMD ["/start.sh"]
