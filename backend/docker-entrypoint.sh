#!/bin/sh
set -e

echo "Iniciando backend..."

# Crear directorios necesarios
mkdir -p storage/logs storage/cache

# Dar permisos
chmod -R 775 storage || true

echo "Configuración completada"

# Ejecutar el comando principal
exec "$@"