FROM php:8.2-fpm-alpine

# Instalar dependencias del sistema
RUN apk add --no-cache \
    git \
    unzip \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    autoconf \
    g++ \
    make \
    linux-headers \
    mysql-client

# Instalar extensiones PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    mysqli \
    zip \
    gd \
    mbstring \
    opcache \
    bcmath \
    sockets

# Instalar Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Instalar XDebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Instalar Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Crear directorio para logs
RUN mkdir -p /var/log/php && chmod 777 /var/log/php

# Configurar PHP
COPY php.ini /usr/local/etc/php/php.ini
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Configurar usuario no root
RUN addgroup -g 1000 -S www && adduser -u 1000 -S www -G www

# Establecer permisos
RUN chown -R www:www /var/www/html

# Script de entrada
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/html/backend

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]