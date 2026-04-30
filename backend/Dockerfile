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
    mysql-client \
    netcat-openbsd \
    fcgi

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

# Instalar XDebug (solo para desarrollo)
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Instalar Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Crear directorios necesarios
RUN mkdir -p /var/www/html/backend/storage/cache /var/www/html/backend/storage/logs

# Configurar PHP
COPY php.ini /usr/local/etc/php/php.ini
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Copiar script de entrada
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/html/backend

# Copiar el código fuente
COPY . .

# Instalar dependencias de Composer
RUN composer install --no-interaction --optimize-autoloader

# Establecer permisos
RUN chown -R www-data:www-data /var/www/html/backend \
    && chmod -R 775 /var/www/html/backend/storage

# Verificar que PHP-FPM está configurado correctamente
RUN php-fpm -t

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm", "-F"]