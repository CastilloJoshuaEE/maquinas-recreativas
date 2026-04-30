@echo off
title Setup K8s Recrea Sys - Desde Cero
color 0A

cd /d D:\xampp\htdocs\maquinas-recreativas

echo ========================================
echo   SETUP KUBERNETES - RECREA SYS
echo ========================================
echo.

echo [1/6] Limpiando cluster anterior...
kind delete cluster --name recrea-cluster 2>nul
docker rmi recrea-backend:latest --force 2>nul
echo   Limpieza completada.
echo.

echo [2/6] Construyendo imagen del backend...
cd backend
docker build -t recrea-backend:latest .
if %errorlevel% neq 0 (
    echo ERROR al construir imagen. Revisa el Dockerfile.
    pause
    exit /b 1
)
echo   Imagen construida OK.
cd ..
echo.

echo [3/6] Creando cluster kind...
kind create cluster --name recrea-cluster
if %errorlevel% neq 0 (
    echo ERROR al crear cluster. Verifica que kind esta instalado.
    pause
    exit /b 1
)
echo   Cluster creado OK.
echo.

echo [4/6] Cargando imagen en kind...
kind load docker-image recrea-backend:latest --name recrea-cluster
echo   Imagen cargada en kind OK.
echo.

echo [5/6] Aplicando manifests de Kubernetes...
kubectl apply -f k8s/namespace.yaml
kubectl apply -f k8s/backend-secret.yaml
kubectl apply -f k8s/configmap.yaml
kubectl apply -f k8s/nginx-config.yaml
kubectl apply -f k8s/mysql-deployment.yaml
kubectl apply -f k8s/mysql-service.yaml
kubectl apply -f k8s/redis-deployment.yaml
kubectl apply -f k8s/backend-deployment.yaml
kubectl apply -f k8s/backend-service.yaml
kubectl apply -f k8s\ingress.yaml

echo   Manifests aplicados.
echo.

echo [6/6] Esperando pods (60 segundos)...
timeout /t 60 /nobreak >nul

echo.
echo ========================================
echo   ESTADO DE PODS
echo ========================================
kubectl get pods -n recrea-sys
echo.
kubectl get svc -n recrea-sys
echo.

echo ========================================
echo   PARA ACCEDER AL BACKEND EN K8S
echo ========================================
echo.
echo   Ejecuta en otra terminal:
echo   kubectl port-forward -n recrea-sys svc/backend-service 9000:80
echo   Luego abre: http://localhost:9000/health
echo.

pause