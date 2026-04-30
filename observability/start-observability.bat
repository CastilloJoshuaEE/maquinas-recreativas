@echo off
title Iniciar Observabilidad - Recrea Sys
color 0A

cd /d D:\xampp\htdocs\maquinas-recreativas\observability

echo ========================================
echo    INICIANDO OBSERVABILIDAD
echo ========================================
echo.

:: Aplicar configuraciones
echo Aplicando archivos YAML...
kubectl apply -f grafana.yaml
kubectl apply -f prometheus.yaml
kubectl apply -f loki.yaml
kubectl apply -f promtail.yaml

echo.
echo Esperando que los pods esten listos...
timeout /t 30 /nobreak >nul

echo.
echo ========================================
echo    INICIANDO PORT-FORWARD
echo ========================================
echo.

:: Iniciar port-forward en nuevas ventanas
start "Grafana" cmd /c "kubectl port-forward -n recrea-sys svc/grafana-service 3000:3000"
timeout /t 2 /nobreak >nul
start "Prometheus" cmd /c "kubectl port-forward -n recrea-sys svc/prometheus 9090:9090"
timeout /t 2 /nobreak >nul
start "Loki" cmd /c "kubectl port-forward -n recrea-sys svc/loki 3100:3100"

echo.
echo ========================================
echo    SERVICIOS INICIADOS
echo ========================================
echo.
echo   Grafana:     http://localhost:3000 (admin/admin)
echo   Prometheus:  http://localhost:9090
echo   Loki:        http://localhost:3100
echo.
echo Cierra las ventanas de port-forward para detener los túneles.
echo.

pause