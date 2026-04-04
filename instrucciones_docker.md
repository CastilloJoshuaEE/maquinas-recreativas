# 1. estructura

D:\xampp\htdocs\maquinas-recreativas
├── backend/
│   ├── Dockerfile
│   ├── docker-entrypoint.sh
│   ├── .dockerignore
│   ├── php.ini
│   └── xdebug.ini
├── docker/

│   ├── start.bat

│   ├── stop.bat
│   ├── docker-compose.yml
│   ├── mysql/
│   │   ├── Dockerfile
│   │   └── init.sql

│   │   └── init_test.sql

│   └── nginx/
│       ├── Dockerfile
│       └── default.conf
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
