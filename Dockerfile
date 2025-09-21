FROM php:8.3-fpm

# System deps
RUN apt-get update && apt-get install -y     git curl zip unzip libicu-dev libonig-dev libzip-dev libxml2-dev     && docker-php-ext-install pdo pdo_mysql intl mbstring zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy app (bind-mounted at runtime as well)
COPY . /var/www

# Optimize
RUN composer install --no-interaction --prefer-dist --no-dev || true     && php artisan key:generate --force || true

CMD [php-fpm]
