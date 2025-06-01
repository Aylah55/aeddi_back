FROM php:8.1-apache

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    APP_ENV=production \
    APP_DEBUG=false \
    SANCTUM_STATEFUL_DOMAINS=aeddi-antsiranana.onrender.com \
    SESSION_DOMAIN=.onrender.com \
    CORS_ALLOWED_ORIGINS="https://aeddi-antsiranana.onrender.com"

RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip libpq-dev git \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite headers

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY . .

RUN mkdir -p storage/app/public storage/framework/sessions storage/framework/views storage/framework/cache storage/logs \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

RUN ln -sf /var/www/html/storage/app/public /var/www/html/public/storage

# Copier la config Apache
COPY laravel.conf /etc/apache2/sites-available/laravel.conf

# Désactiver le site par défaut et activer le notre
RUN a2dissite 000-default.conf \
    && a2ensite laravel.conf

WORKDIR /var/www/html/public

# Expose port 80, port par défaut d'Apache
EXPOSE 80

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
