#!/bin/sh
set -e

echo "=== Iniciando backend ==="

# Crear directorios necesarios
mkdir -p storage/logs storage/cache

# Dar permisos
chmod -R 775 storage 2>/dev/null || true

# Verificar variables de entorno
echo "APP_ENV: ${APP_ENV:-development}"
echo "DB_DRIVER: ${DB_DRIVER:-mysql}"

# Esperar a que la base de datos esté lista según el driver
if [ "$DB_DRIVER" = "pgsql" ]; then
    echo "Esperando a PostgreSQL..."
    until PGPASSWORD=$DB_PASS psql -h "$DB_HOST" -U "$DB_USER" -d "postgres" -c '\q' 2>/dev/null; do
        >&2 echo "PostgreSQL no está disponible - esperando..."
        sleep 2
    done
    echo "PostgreSQL está listo!"
fi
echo "=== Configuración completada ==="

# Ejecutar el comando principal
exec "$@"