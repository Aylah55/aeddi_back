FROM php:8.2-apache

# Installe les dépendances système
RUN apt-get update && apt-get install -y \
    git zip unzip curl libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl curl

# Active mod_rewrite et headers pour .htaccess (important pour Sanctum / CORS)
RUN a2enmod rewrite headers

# Installe Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copie le projet Laravel
COPY . /var/www/html

# Donne les bonnes permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Passe à /var/www/html comme répertoire de travail
WORKDIR /var/www/html

# Installe les dépendances Laravel
RUN composer install --no-dev --optimize-autoloader

# Génère les caches Laravel (important pour .env et config)
RUN php artisan config:clear \
 && php artisan route:clear \
 && php artisan view:clear \
 && php artisan config:cache

# Copie la configuration Apache personnalisée
COPY ./000-default.conf /etc/apache2/sites-available/000-default.conf

# Expose le port 80
EXPOSE 80