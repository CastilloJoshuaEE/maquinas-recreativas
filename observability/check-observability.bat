@echo off
title Verificar Observabilidad - Recrea Sys
color 0A

echo ========================================
echo    VERIFICANDO OBSERVABILIDAD
echo ========================================
echo.

:: Cambiar al directorio correcto
cd /d D:\xampp\htdocs\maquinas-recreativas\observability

:: Verificar que kubectl está disponible
echo [1] Verificando kubectl...
kubectl version --client >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] kubectl no encontrado.
    pause
    exit /b 1
)
echo [OK] kubectl disponible.
echo.

:: Verificar namespace
echo [2] Verificando namespace...
kubectl get namespace recrea-sys >nul 2>&1
if %errorlevel% neq 0 (
    echo Creando namespace recrea-sys...
    kubectl create namespace recrea-sys
)
echo [OK] Namespace listo.
echo.

:: Aplicar configuraciones
echo [3] Aplicando configuraciones YAML...
echo.
if exist "grafana.yaml" (
    echo Aplicando grafana.yaml...
    kubectl apply -f grafana.yaml
) else (
    echo [ERROR] grafana.yaml no encontrado en %cd%
)
if exist "prometheus.yaml" (
    echo Aplicando prometheus.yaml...
    kubectl apply -f prometheus.yaml
) else (
    echo [ERROR] prometheus.yaml no encontrado
)
if exist "loki.yaml" (
    echo Aplicando loki.yaml...
    kubectl apply -f loki.yaml
) else (
    echo [ERROR] loki.yaml no encontrado
)
if exist "promtail.yaml" (
    echo Aplicando promtail.yaml...
    kubectl apply -f promtail.yaml
) else (
    echo [ERROR] promtail.yaml no encontrado
)
echo.

:: Esperar pods
echo [4] Esperando que los pods esten listos (30 segundos)...
timeout /t 30 /nobreak >nul

:: Ver estado
echo [5] Estado de los pods:
echo.
kubectl get pods -n recrea-sys
echo.

echo [6] Servicios:
echo.
kubectl get svc -n recrea-sys
echo.

echo ========================================
echo    ACCESOS
echo ========================================
echo.
echo Para acceder, ejecuta en terminales separadas:
echo.
echo   kubectl port-forward -n recrea-sys svc/grafana-service 3000:3000
echo   kubectl port-forward -n recrea-sys svc/prometheus 9090:9090
echo   kubectl port-forward -n recrea-sys svc/loki 3100:3100
echo.
echo Luego abre:
echo   Grafana:     http://localhost:3000 (admin/admin)
echo   Prometheus:  http://localhost:9090
echo   Loki:        http://localhost:3100
echo.

pause