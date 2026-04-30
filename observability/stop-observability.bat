@echo off
title Detener Observabilidad - Recrea Sys
color 0C

cd /d D:\xampp\htdocs\maquinas-recreativas\observability

echo ========================================
echo    DETENIENDO OBSERVABILIDAD
echo ========================================
echo.

:: Matar procesos de port-forward
echo Cerrando port-forwards...
taskkill /F /FI "WINDOWTITLE eq Grafana" >nul 2>&1
taskkill /F /FI "WINDOWTITLE eq Prometheus" >nul 2>&1
taskkill /F /FI "WINDOWTITLE eq Loki" >nul 2>&1

:: Eliminar recursos
echo Eliminando recursos de Kubernetes...
kubectl delete -f grafana.yaml --ignore-not-found=true
kubectl delete -f prometheus.yaml --ignore-not-found=true
kubectl delete -f loki.yaml --ignore-not-found=true
kubectl delete -f promtail.yaml --ignore-not-found=true

echo.
echo [OK] Observabilidad detenida.
echo.

pause