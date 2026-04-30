# ususarios iniciales rPOSE ELIMINA LA IOMAGEN QUE SE CREO DEL PROYECTO PARA SERUIR USANDO EL

| `DB_HOST=mysql` no se resuelve | Cambiar a `DB_HOST=127.0.0.1` |
| -------------------------------- | ------------------------------- |

# si funciono docker no olvidar ejecutar los usuarios iniciales


## Usar PowerShell para ejecutar el script en el contenedor directamente

**powershell**

```
# Desde PowerShell/CMD en Windows
docker exec -it maquinas_recreativas_php php /var/www/html/backend/Scripts/insertar-usuarios-iniciales.php
```



PS C:\Windows\system32> D:
PS D:\> cd D:\xampp\htdocs\maquinas-recreativas\docker
PS D:\xampp\htdocs\maquinas-recreativas\docker> docker exec -it maquinas_recreativas_php php /var/www/html/backend/Scripts/insertar-usuarios-iniciales.php
Cannot load Xdebug - it was already loaded
Xdebug: [Log Files] File '/var/log/php/xdebug.log' could not be opened.
Xdebug: [Step Debug] Could not connect to debugging client. Tried: host.docker.internal:9003 (fallback through xdebug.client_host/xdebug.client_port).
✓ Conectando a la base de datos...
✓ Conexión establecida a: bd_recrea_sys
⚠ Los usuarios iniciales ya fueron insertados anteriormente.
  Lock file: /var/www/html/backend/Scripts/../Config/.usuarios_iniciales.lock
  Si deseas reiniciar la inserción, elimina este archivo y vuelve a ejecutar el script.
¿Deseas forzar la inserción de todos modos? (s/N): s
 Forzando inserción...

Insertando usuarios iniciales...
--------------------------------

 Usuarios insertados correctamente!
 Lock file creado en: /var/www/html/backend/Scripts/../Config/.usuarios_iniciales.lock

RESUMEN DE USUARIOS INSERTADOS:
-------------------------------

• admin1 | Jean Castro | Administrador | Activo
• esb | Edú Sabando | Logistica | Activo
• euro | Euro Quiroz | Tecnico | Activo
• joel | Joel Gabino | Tecnico | Activo
• joshua | Joshúa Castillo | Tecnico | Activo
• sebas | Sebastián Ramírez | Contabilidad | Activo

🔍 Verificando que los datos se pueden desencriptar correctamente...
--------------------------------------------------------------------

 admin1: Email=jean@admin.com, CI=1111111111
 euro: Email=euro@gmail.com, CI=0987667890
 joshua: Email=joshua@gmail.com, CI=0987654321
 joel: Email=joel@gmail.com, CI=0980980987

🎉 Proceso completado!

What's next:

D:\xampp\htdocs\maquinas-recreativas\docker>docker logs -f maquinas_recreativas_php usa ese comando si quieres saber como va tu programa usando docker




# SI ES LA PRIMERA VEZ:

# comprobar

D:\xampp\htdocs\maquinas-recreativas>type backend\.env | findstr "DB_HOST"

# DESARROLLO LOCAL Y TEST: DB_HOST=127.0.0.1

DB_HOST=mysql
DB_HOST_TEST=mysql_test

D:\xampp\htdocs\maquinas-recreativas>type docker\.env.docker | findstr "DB_HOST"
El sistema no puede encontrar el archivo especificado.

D:\xampp\htdocs\maquinas-recreativas>type .env.docker | findstr "DB_HOST"

# DESARROLLO LOCAL Y TEST: DB_HOST=127.0.0.1

DB_HOST=mysql
DB_HOST_TEST=mysql_test

2. Reconstruir y levantar servicios
   bash
   cd docker

# Detener todo (por si acaso)

docker-compose down -v

# Reconstruir solo PHP (ya lo hiciste)

docker-compose build --no-cache php

# Levantar todos los servicios

docker-compose up -d
3. Verificar que todos los contenedores están corriendo
bash
docker ps
Deberías ver:

maquinas_recreativas_php

maquinas_recreativas_nginx

maquinas_recreativas_mysql

maquinas_recreativas_redis

maquinas_recreativas_mailhog

maquinas_recreativas_phpmyadmin

4. Verificar que PHP-FPM está funcionando
   bash

# Ver logs de PHP

docker-compose logs php

# Verificar que PHP-FPM está escuchando

docker exec maquinas_recreativas_php netstat -tlnp | grep 9000

# Comandos útiles para Docker Compose, en el caso de que ya lo hayas ejecutado en tu máquina recuerda aplicar siempre esté pasó ya que creaste la imagen con el DB_HOST= mysql, por lo que ya no usas el DB_HOST:127.0.0.1 o 8000 por lo que te daría error, para ello debes tener el docker desktop abierto

cd D:\xampp\htdocs\maquinas-recreativas\docker

# Iniciar

start.bat

![1777527644276](image/despliegue/1777527644276.png)

1. **Descargar e instalar Docker Desktop** desde: [https://www.docker.com/products/docker-desktop/](https://www.docker.com/products/docker-desktop/)
2. **Habilitar WSL 2** (Windows Subsystem for Linux)
3. **Reiniciar Windows**
4. **Iniciar Docker Desktop**

SI AUN FALLA DETEN TODO DEL

# Ver logs

docker-compose logs -f

esperar unos minutos a que mysql estlisto y esperar un resultado como este en tu simbolo del sistema de windows

D:\xampp\htdocs\maquinas-recreativas\docker>docker exec maquinas_recreativas_mysql mysqladmin ping -h localhost -u root -proot123
mysqladmin: [Warning] Using a password on the command line interface can be insecure.
mysqld is alive

### **Si todo funciona, instalar dependencias de Composer(si es que no se instalo ya)**

docker exec -it maquinas_recreativas_php composer install

ir a (usando postman o el navegador de internet)

http://localhost:8000/health

{"success":true,"status":"ok","message":"API de Máquinas Recreativas funcionando correctamente","timestamp":"2026-04-29 00:19:25","version":"1.0.0","cache":"redis"}

o a localhost:8080/index.php para usar el phpmyadmin

# Ejecutar tests

docker exec -it maquinas_recreativas_php vendor/bin/phpunit -c tests/phpunit.xml --testsuite Smoke

# Detener si consideras necesario

stop.bat

kubernetes configuracion

CONFIGURACION DE KUBERNETES: 1. Eliminar el clúster actual, si es que lo creaste antes

# Eliminar el clúster Kind

kind delete cluster --name recrea-cluster

# Verificar que no existe

kind get clusters
2. Crear un nuevo clúster
bash

# Crear nuevo clúster

kind create cluster --name recrea-cluster

# Verificar que está funcionando

kubectl cluster-info --context kind-recrea-cluster
 ESPERA A QUE EL NODO ESTÉ READY kubectl get nodes -w 3. Crear el namespace
bash
kubectl create namespace recrea-sys  si sale alguna advertencia,es normal y no afecta la funcionalidad. El namespace ya existía del clúster anterior y se está actualizando. continua con los siguientes : 5. Aplicar en orden
bash
cd D:\xampp\htdocs\maquinas-recreativas\k8s

# Aplicar todos los manifiestos

kubectl apply -f namespace.yaml
kubectl apply -f configmap.yaml
kubectl apply -f secret.yaml
kubectl apply -f mysql-deployment.yaml
kubectl apply -f mysql-service.yaml
kubectl apply -f backend-deployment.yaml
kubectl apply -f backend-service.yaml

kubectl apply -f redis-deployment.yaml

kubectl apply -f backend-secret.yaml

# Esperar a que MySQL esté listo

kubectl wait --for=condition=ready pod -l app=mysql -n recrea-sys --timeout=120s

# Esperar a que Redis esté listo

kubectl wait --for=condition=ready pod -l app=redis -n recrea-sys --timeout=60s

# Verificar que están corriendo

kubectl get pods -n recrea-sys

Cargar la imagen en el nuevo clúster
bash

# Cargar la imagen latest

kind load docker-image recrea-backend:latest --name recrea-cluster
7. Verificar
bash

# Ver pods- ESPERAR

kubectl get pods -n recrea-sys -w

# Ver logs (debe mostrar PHP-FPM) ESPERAR

kubectl logs -n recrea-sys deployment/backend --tail=20

en el caso de que D:\xampp\htdocs\maquinas-recreativas\k8s>kubectl exec -it -n recrea-sys deployment/backend -- sh
/var/www/html/backend # nc -z mysql-service 3306 && echo OK
Connection to mysql-service (10.96.78.105) 3306 port [tcp/mysql] succeeded!
OK
/var/www/html/backend # ps aux|grep php-fpm
    1 root      0:08 {docker-entrypoi} /bin/sh /usr/local/bin/docker-entrypoint.sh php-fpm -F
/var/www/html/backend # php-fpm -F &
/var/www/html/backend # Cannot load Xdebug - it was already loaded
[29-Apr-2026 02:04:40] NOTICE: fpm is running, pid 402
[29-Apr-2026 02:04:40] NOTICE: ready to handle connections

VERIFICAR

D:\xampp\htdocs\maquinas-recreativas\k8s>kubectl get pods -n recrea-sys
NAME                       READY   STATUS    RESTARTS   AGE
backend-8688645795-fxchb   1/1     Running   0          5m46s
mysql-cd484bc4d-x9dc5      1/1     Running   0          3h55m
redis-969cfd65-7j99b       1/1     Running   0          170m

D:\xampp\htdocs\maquinas-recreativas\k8s>kubectl get svc -n recrea-sys
NAME              TYPE        CLUSTER-IP      EXTERNAL-IP   PORT(S)    AGE
backend-service   ClusterIP   10.96.115.177   `<none>`        80/TCP     4h4m
mysql-service     ClusterIP   10.96.78.105    `<none>`        3306/TCP   4h4m
redis-service     ClusterIP   10.96.11.52     `<none>`        6379/TCP   179m

D:\xampp\htdocs\maquinas-recreativas\k8s>

# Probar

kubectl port-forward -n recrea-sys service/backend-service 8082:80 &
curl http://localhost:8082/health

# Ver logs (debe mostrar PHP-FPM)

kubectl logs -n recrea-sys deployment/backend --tail=20

# Probar

kubectl port-forward -n recrea-sys service/backend-service 8082:80 &
curl http://localhost:8082/health
