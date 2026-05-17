# Multistage build - Production stage
FROM php:${PHP_VERSION}-fpm-alpine AS prod

# Install system dependencies
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    icu-dev \
    libxml2-dev \
    libpng-dev \
    oniguruma-dev

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    zip \
    intl \
    opcache \
    xml \
    mbstring \
    exif \
    bcmath \
    gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy PHP production configuration
COPY docker/prod/php/php.ini /usr/local/etc/php/php.ini
COPY docker/prod/php/php-fpm.conf /usr/local/etc/php-fpm.d/zz-custom.conf

WORKDIR /var/www/html

# Copy application files
COPY app/ /var/www/html/

# Install dependencies (no dev)
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && composer clear-cache

# Optimize Laravel
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan event:cache

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

USER www-data