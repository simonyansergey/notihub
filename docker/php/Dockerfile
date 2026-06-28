FROM php:8.5-fpm

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Match UID/GID to the host user so files created in the container are owned by you
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data
USER www-data
