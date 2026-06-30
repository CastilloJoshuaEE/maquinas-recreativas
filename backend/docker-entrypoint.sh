#!/bin/bash

set -e

echo "Iniciando backend..."

mkdir -p Storage/Logs
mkdir -p Storage/Cache

chmod -R 775 Storage || true

echo "Configuración completada"

exec apache2-foreground