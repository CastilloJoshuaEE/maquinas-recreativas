#!/bin/sh
set -e

# Esperar a que MySQL esté listo
echo "Esperando a que MySQL esté listo..."
while ! nc -z mysql 3306; do
  sleep 1
done
echo "MySQL está listo!"

# Instalar dependencias de Composer si no existen
if [ ! -d "vendor" ]; then
    echo "Instalando dependencias de Composer..."
    composer install --no-interaction --optimize-autoloader
fi

# Crear directorios necesarios
mkdir -p storage/cache storage/logs

# Establecer permisos
chmod -R 777 storage

# Ejecutar el comando principal
exec "$@"