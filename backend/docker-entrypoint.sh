#!/bin/sh
set -e

echo "=== Iniciando backend ==="

# Crear directorios necesarios
mkdir -p storage/logs storage/cache

# Dar permisos
chmod -R 775 storage 2>/dev/null || true
echo "=== Iniciando backend ==="
echo "APP_ENV: ${APP_ENV:-development}"
echo "DB_DRIVER: ${DB_DRIVER:-mysql}"
echo "=== Configuración completada ==="
# Ejecutar el comando principal
exec "$@"