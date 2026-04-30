# ENTORNO EN CMD DE WINDOWS

# 1. estructura

maquinas-recreativas/
├── backend/
│   ├── Dockerfile
│   ├── docker-entrypoint.sh
│   ├── .dockerignore
│   ├── php.ini
│   ├── xdebug.ini
│   ├── .env
│   ├── composer.json
│   ├── vendor/
│   ├── src/
│   ├── tests/
│   │   ├── bootstrap.php
│   │   ├── TestDatabase.php
│   │   ├── Domain/
│   │   ├── Application/
│   │   ├── Infrastructure/
│   │   ├── Integration/
│   │   ├── Functional/
│   │   │   ├── HttpTestCase.php
│   │   │   ├── AdminFlowsTest.php
│   │   │   ├── ReporteFlowsTest.php
│   │   │   ├── UserFlowsTest.php
│   │   │   └── run_tests.php
│   │   ├── Smoke/
│   │   │   ├── SmokeTestCase.php
│   │   │   ├── SmokeHealthTest.php
│   │   │   ├── SmokeAuthTest.php
│   │   │   ├── SmokeMaquinaTest.php
│   │   │   ├── SmokeComercioTest.php
│   │   │   ├── SmokeReporteTest.php
│   │   │   └── SmokeUsuarioTest.php
│   │   ├── Performance/
│   │   │   ├── HttpStressTestCase.php
│   │   │   ├── StressTest.php
│   │   │   ├── run_stress_test.php
│   │   │   └── breakpoint-analyzer.php
│   │   └── Security/
│   │       └── security-test.php
│   ├── public/
│   │   ├── index.php
│   │   ├── serve.php
│   │   └── docs/
│   └── storage/
│       ├── logs/
│       └── cache/
├── docker/
│   ├── docker-compose.yml
│   ├── start.bat
│   ├── stop.bat
│   ├── mysql/
│   │   ├── Dockerfile
│   │   ├── init.sql
│   │   └── init_test.sql
│   └── nginx/
│       ├── Dockerfile
│       └── default.conf
├── k8s/
│   ├── namespace.yaml
│   ├── backend-deployment.yaml
│   ├── backend-service.yaml
│   ├── mysql-deployment.yaml
│   ├── mysql-service.yaml
│   ├── ingress.yaml
│   ├── configmap.yaml
│   └── secret.yaml
├── observability/
│   ├── prometheus.yaml
│   ├── grafana.yaml
│   ├── loki.yaml
│   └── promtail.yaml
├── .env.docker
└── Makefilemaquinas-recreativas/
├── backend/
│   ├── Dockerfile
│   ├── docker-entrypoint.sh
│   ├── .dockerignore
│   ├── php.ini
│   ├── xdebug.ini
│   ├── .env
│   ├── composer.json
│   ├── vendor/
│   ├── src/
│   ├── tests/
│   │   ├── bootstrap.php
│   │   ├── TestDatabase.php
│   │   ├── Domain/
│   │   ├── Application/
│   │   ├── Infrastructure/
│   │   ├── Integration/
│   │   ├── Functional/
│   │   │   ├── HttpTestCase.php
│   │   │   ├── AdminFlowsTest.php
│   │   │   ├── ReporteFlowsTest.php
│   │   │   ├── UserFlowsTest.php
│   │   │   └── run_tests.php
│   │   ├── Smoke/
│   │   │   ├── SmokeTestCase.php
│   │   │   ├── SmokeHealthTest.php
│   │   │   ├── SmokeAuthTest.php
│   │   │   ├── SmokeMaquinaTest.php
│   │   │   ├── SmokeComercioTest.php
│   │   │   ├── SmokeReporteTest.php
│   │   │   └── SmokeUsuarioTest.php
│   │   ├── Performance/
│   │   │   ├── HttpStressTestCase.php
│   │   │   ├── StressTest.php
│   │   │   ├── run_stress_test.php
│   │   │   └── breakpoint-analyzer.php
│   │   └── Security/
│   │       └── security-test.php
│   ├── public/
│   │   ├── index.php
│   │   ├── serve.php
│   │   └── docs/
│   └── storage/
│       ├── logs/
│       └── cache/
├── docker/
│   ├── docker-compose.yml
│   ├── start.bat
│   ├── stop.bat
│   ├── mysql/
│   │   ├── Dockerfile
│   │   ├── init.sql
│   │   └── init_test.sql
│   └── nginx/
│       ├── Dockerfile
│       └── default.conf
├── k8s/
│   ├── namespace.yaml
│   ├── backend-deployment.yaml
│   ├── backend-service.yaml
│   ├── mysql-deployment.yaml
│   ├── mysql-service.yaml
│   ├── ingress.yaml
│   ├── configmap.yaml
│   └── secret.yaml
├── observability/
│   ├── prometheus.yaml
│   ├── grafana.yaml
│   ├── loki.yaml
│   └── promtail.yaml
├── .env.docker
└── Makefilemaquinas-recreativas/
├── backend/
│   ├── Dockerfile
│   ├── docker-entrypoint.sh
│   ├── .dockerignore
│   ├── php.ini
│   ├── xdebug.ini
│   ├── .env
│   ├── composer.json
│   ├── vendor/
│   ├── src/
│   ├── tests/
│   │   ├── bootstrap.php
│   │   ├── TestDatabase.php
│   │   ├── Domain/
│   │   ├── Application/
│   │   ├── Infrastructure/
│   │   ├── Integration/
│   │   ├── Functional/
│   │   │   ├── HttpTestCase.php
│   │   │   ├── AdminFlowsTest.php
│   │   │   ├── ReporteFlowsTest.php
│   │   │   ├── UserFlowsTest.php
│   │   │   └── run_tests.php
│   │   ├── Smoke/
│   │   │   ├── SmokeTestCase.php
│   │   │   ├── SmokeHealthTest.php
│   │   │   ├── SmokeAuthTest.php
│   │   │   ├── SmokeMaquinaTest.php
│   │   │   ├── SmokeComercioTest.php
│   │   │   ├── SmokeReporteTest.php
│   │   │   └── SmokeUsuarioTest.php
│   │   ├── Performance/
│   │   │   ├── HttpStressTestCase.php
│   │   │   ├── StressTest.php
│   │   │   ├── run_stress_test.php
│   │   │   └── breakpoint-analyzer.php
│   │   └── Security/
│   │       └── security-test.php
│   ├── public/
│   │   ├── index.php
│   │   ├── serve.php
│   │   └── docs/
│   └── storage/
│       ├── logs/
│       └── cache/
├── docker/
│   ├── docker-compose.yml
│   ├── start.bat
│   ├── stop.bat
│   ├── mysql/
│   │   ├── Dockerfile
│   │   ├── init.sql
│   │   └── init_test.sql
│   └── nginx/
│       ├── Dockerfile
│       └── default.conf
├── k8s/
│   ├── namespace.yaml
│   ├── backend-deployment.yaml
│   ├── backend-service.yaml
│   ├── mysql-deployment.yaml
│   ├── mysql-service.yaml
│   ├── ingress.yaml
│   ├── configmap.yaml
│   └── secret.yaml
├── observability/
│   ├── prometheus.yaml
│   ├── grafana.yaml
│   ├── loki.yaml
│   └── promtail.yaml
├── .env.docker
└── Makefile

# 2. Copiar el .env

en cd cd D:\xampp\htdocs\maquinas-recreativas

hacer

cd D:\xampp\htdocs\maquinas-recreativas\backend
copy ..\.env.docker .env y Sí

# 3. Construir y levantar servicios

D:\xampp\htdocs\maquinas-recreativas\backend\docker>

docker-compose build --no-cache
docker-compose up -d

docker ps

# 4. Instalar dependencias Composer

docker exec -it maquinas_recreativas_php composer install

# 5. Ejecutar tests para verificar

docker exec -it maquinas_recreativas_php vendor/bin/phpunit -c tests/phpunit.xml --testsuite Smoke


### **1. Verificar Docker**

```bash
docker ps
# Debe mostrar: php, nginx, mysql, mysql_test, redis, mailhog, phpmyadmin
```

### **2. Verificar Backend**

```bash
curl http://localhost:8000/health
# Respuesta: {"status":"ok"}
```

### **3. Verificar Tests**

```bash
vendor/bin/phpunit -c tests/phpunit.xml --testsuite Smoke
# Todos deben pasar (0 failures)
```

### **4. Verificar Kubernetes (si aplica)**

```bash
kubectl get pods -n recrea-sys
# Todos deben estar en Running
```

### **5. Verificar Observabilidad**

```bash
# Prometheus
kubectl port-forward -n recrea-sys deployment/prometheus 9090:9090
# Abrir: http://localhost:9090

# Grafana
kubectl port-forward -n recrea-sys deployment/grafana 3000:3000
# Abrir: http://localhost:3000 (admin/admin)
```



### **1. Verificar Docker**

```bash
docker ps
# Debe mostrar: php, nginx, mysql, mysql_test, redis, mailhog, phpmyadmin
```

### **2. Verificar Backend**

```bash
curl http://localhost:8000/health
# Respuesta: {"status":"ok"}
```

### **3. Verificar Tests**

```bash
vendor/bin/phpunit -c tests/phpunit.xml --testsuite Smoke
# Todos deben pasar (0 failures)
```

### **4. Verificar Kubernetes (si aplica)**

```bash









kubectl get pods -n recrea-sys
# Todos deben estar en Running
```

## **ALTERNATIVA: Usar Kind (Kubernetes in Docker)**

Si Docker Desktop no funciona, puedes usar  **Kind** :

**bash**

```
# Instalar Kind (si no está)
winget install kind

# Crear clúster
kind create cluster --name recrea-cluster

# Verificar
kubectl cluster-info --context kind-recrea-cluster
kubectl get nodes
```


```
# 2. Aplicar los manifiestos (con validación desactivada para Kind)
kubectl apply -f k8s/ --validate=false

# 3. Verificar que los pods se están creando
kubectl get pods -n recrea-sys -w

# 4. Ver servicios
kubectl get svc -n recrea-sys
```


### **5. Verificar Observabilidad**

```bash
# Prometheus
kubectl port-forward -n recrea-sys deployment/prometheus 9090:9090
# Abrir: http://localhost:9090

# Grafana
kubectl port-forward -n recrea-sys deployment/grafana 3000:3000
# Abrir: http://localhost:3000 (admin/admin)
```
