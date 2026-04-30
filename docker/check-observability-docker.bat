@echo off
title Verificar Observabilidad Docker - Recrea Sys
color 0A

echo ========================================
echo    VERIFICANDO OBSERVABILIDAD DOCKER SIN KUBERNETES
echo ========================================
echo.

echo [1] Contenedores corriendo:
echo.
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
echo.

echo [2] Verificando puertos:
echo.
netstat -an | findstr ":3000" >nul && echo Grafana (puerto 3000): ACTIVO || echo Grafana (puerto 3000): INACTIVO
netstat -an | findstr ":9090" >nul && echo Prometheus (puerto 9090): ACTIVO || echo Prometheus (puerto 9090): INACTIVO
netstat -an | findstr ":3100" >nul && echo Loki (puerto 3100): ACTIVO || echo Loki (puerto 3100): INACTIVO
echo.

echo [3] Para ver logs de cada contenedor:
echo.
echo   docker logs --tail=50 NOMBRE_CONTENEDOR
echo.

echo [4] Accesos:
echo   Grafana:     http://localhost:3000 (admin/admin)
echo   Prometheus:  http://localhost:9090
echo   Loki:        http://localhost:3100
echo.

pause