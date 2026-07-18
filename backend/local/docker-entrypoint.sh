#!/bin/sh
set -e

# Esperar a que MySQL esté listo
echo "Esperando a que MySQL esté listo..."
until nc -z mysql-service 3306 2>/dev/null; do
    echo "Esperando MySQL..."
    sleep 2
done
echo " MySQL está listo!"

# Redis es opcional (no fallar si no existe)
echo "Verificando Redis..."
if nc -z redis-service 6379 2>/dev/null 2>&1; then
    echo "Redis está listo!"
else
    echo "  Redis no disponible, continuando sin caché..."
fi

# Crear directorios necesarios
mkdir -p storage/cache storage/logs
chmod -R 777 storage

# Instalar dependencias de Composer si no existen
if [ ! -d "vendor" ]; then
    echo "Instalando dependencias de Composer..."
    composer install --no-interaction --optimize-autoloader
fi

# Verificar configuración de PHP-FPM
echo "Verificando configuración de PHP-FPM..."
php-fpm -t

# Iniciar PHP-FPM en primer plano
echo "🚀 Iniciando PHP-FPM..."
exec php-fpm -F