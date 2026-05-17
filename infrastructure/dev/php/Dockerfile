# Multistage build - Dev stage
FROM php:8.4-fpm-alpine AS dev

# Install system dependencies
# apk add устанавливает пакеты в Alpine Linux.
RUN apk add --no-cache \
    bash \
    git \
    icu-dev \
    libpq-dev \
    libzip-dev \
    oniguruma-dev \
    tzdata \
    curl \
    zip \
    unzip \
    autoconf 


# Install Composer
# Копируем бинарники из внешних образов.
# Если образы отсутствуют локально, Docker автоматически загрузит их из Docker Hub.
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install PHP extensions installer
# Установщик PHP-расширений (ставит и стандартные, и PECL-расширения одной командой).
COPY --from=mlocati/php-extension-installer:2.7.1 /usr/bin/install-php-extensions /usr/local/bin/

# Даём права на выполнение и запускаем установку PHP-расширений через mlocati
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions \
        intl \
        opcache \
        pdo_pgsql \
        zip \
        redis \
        # Install Xdebug
        xdebug \
        amqp \
        sockets \
        pcntl

# Copy PHP configuration
COPY infrastructure/dev/php/conf/php.ini /usr/local/etc/php/php.ini
COPY infrastructure/dev/php/conf/php-fpm.conf /usr/local/etc/php-fpm.d/zz-custom.conf
COPY infrastructure/dev/php/conf/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

WORKDIR /var/www


# Final stage
FROM dev AS final

ARG USER_ID=1000
ARG GROUP_ID=1000
RUN addgroup -g ${GROUP_ID} appgroup || true \
    && adduser -D -u ${USER_ID} -G appgroup appuser \
    && chown -R appuser:appgroup /var/www

USER appuser