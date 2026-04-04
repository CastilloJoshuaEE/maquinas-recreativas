@echo off
echo ========================================
echo  Maquinas Recreativas - Docker Startup
echo ========================================
echo.

:: Verificar si Docker está instalado
where docker >nul 2>nul
if %errorlevel% neq 0 (
    echo ERROR: Docker no está instalado o no está en el PATH
    echo Por favor, instala Docker Desktop desde https://docker.com
    pause
    exit /b 1
)

:: Verificar si Docker está corriendo
docker info >nul 2>nul
if %errorlevel% neq 0 (
    echo ERROR: Docker no está corriendo
    echo Por favor, inicia Docker Desktop
    pause
    exit /b 1
)

:: Crear directorios necesarios
if not exist "..\backend\storage\cache" mkdir "..\backend\storage\cache"
if not exist "..\backend\storage\logs" mkdir "..\backend\storage\logs"
if not exist "..\backend\coverage" mkdir "..\backend\coverage"

:: Levantar servicios
echo Levantando servicios Docker...
docker-compose up -d

:: Esperar a que los servicios estén listos
echo Esperando a que MySQL esté listo...
timeout /t 10 /nobreak >nul

:: Mostrar información
echo.
echo ========================================
echo  Servicios levantados exitosamente!
echo ========================================
echo.
echo   API: http://localhost:8000
echo   PHPMyAdmin: http://localhost:8080
echo   MailHog: http://localhost:8025
echo   Redis: localhost:6379
echo.
echo  Para ver logs: docker-compose logs -f
echo  Para detener: docker-compose down
echo  Para ejecutar tests: make test
echo.

pause