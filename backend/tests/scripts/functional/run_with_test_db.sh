#!/bin/bash
# tests/run_with_test_db.sh

echo "🧹 Limpiando archivos de rate limiting..."
rm -f ../storage/rate_limits.json

echo "🗑️  Limpiando base de datos de pruebas..."
mysql -u root -e "DROP DATABASE IF EXISTS prueba_bd_recrea_sys_; CREATE DATABASE prueba_bd_recrea_sys_;"

echo "🚀 Ejecutando pruebas con base de datos de pruebas..."
cd Functional
php run_tests.php