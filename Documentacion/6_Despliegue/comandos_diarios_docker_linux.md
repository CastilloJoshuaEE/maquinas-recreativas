# ENTORNO EN CMD DE LINUX

# Ver todos los comandos disponibles

make help

# Levantar servicios

make up

# Ejecutar tests smoke

make test-smoke

# Ejecutar tests funcionales

make test-functional

# Ejecutar todos los tests

make test

# Ejecutar tests con cobertura

make test-coverage

# Ejecutar tests de performance

make test-performance

# Ver logs

make logs

# Acceder al contenedor PHP

make shell

# Detener servicios

make down

sin make:

# Levantar servicios

cd docker
start.bat

# Ejecutar tests

docker exec -it maquinas_recreativas_php vendor/bin/phpunit -c tests/phpunit.xml --testsuite Smoke

# Acceder al contenedor

docker exec -it maquinas_recreativas_php sh

# Detener servicios

cd docker
stop.bat

### URLs de Acceso

| Servicio           | URL                                                                                 |
| ------------------ | ----------------------------------------------------------------------------------- |
| API                | [http://localhost:8000](http://localhost:8000/)                                        |
| API Health Check   | [http://localhost:8000/api/public/health](http://localhost:8000/api/public/health)                           |
| PHPMyAdmin         | [http://localhost:8080](http://localhost:8080/)                                        |
| MailHog (emails)   | [http://localhost:8025](http://localhost:8025/)                                        |
| Cobertura de tests | [http://localhost:8000/coverage/index.html](http://localhost:8000/coverage/index.html) |

### 5. Credenciales por defecto

| Servicio            | Usuario     | Contraseña    |
| ------------------- | ----------- | -------------- |
| MySQL (principal)   | recrea_user | recrea_pass123 |
| MySQL (root)        | root        | root123        |
| PHPMyAdmin          | recrea_user | recrea_pass123 |
| Usuario Admin (app) | admin1      | 12345678       |
