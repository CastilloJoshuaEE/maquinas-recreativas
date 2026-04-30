.PHONY: help up down build logs shell test test-smoke test-functional test-unit test-coverage test-performance clean

# Colores para output
GREEN  := $(shell tput -Txterm setaf 2)
YELLOW := $(shell tput -Txterm setaf 3)
WHITE  := $(shell tput -Txterm setaf 7)
RESET  := $(shell tput -Txterm sgr0)

help: ## Muestra esta ayuda
	@printf '\n${YELLOW}Comandos disponibles:${RESET}\n'
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "${GREEN}%-25s${RESET} %s\n", $$1, $$2}'
	@echo ""

up: ## Levantar todos los servicios Docker
	cd docker && docker-compose up -d
	@echo "${GREEN} Servicios levantados${RESET}"
	@echo "    API: http://localhost:8000"
	@echo "    PHPMyAdmin: http://localhost:8080"
	@echo "    MailHog: http://localhost:8025"

down: ## Detener todos los servicios Docker
	cd docker && docker-compose down
	@echo "${GREEN} Servicios detenidos${RESET}"

build: ## Reconstruir imágenes Docker
	cd docker && docker-compose build --no-cache
	@echo "${GREEN} Imágenes reconstruidas${RESET}"

logs: ## Ver logs de Docker
	cd docker && docker-compose logs -f

shell: ## Acceder al contenedor PHP
	docker exec -it maquinas_recreativas_php sh

mysql: ## Acceder a MySQL principal
	docker exec -it maquinas_recreativas_mysql mysql -u recrea_user -precrea_pass123 bd_recrea_sys

mysql-test: ## Acceder a MySQL de pruebas
	docker exec -it maquinas_recreativas_mysql_test mysql -u recrea_user -precrea_pass123 test_bd_recrea_sys

composer-install: ## Instalar dependencias Composer
	docker exec -it maquinas_recreativas_php composer install

composer-update: ## Actualizar dependencias Composer
	docker exec -it maquinas_recreativas_php composer update

test: ## Ejecutar todos los tests
	docker exec -it maquinas_recreativas_php vendor/bin/phpunit -c tests/phpunit.xml

test-smoke: ## Ejecutar tests smoke
	docker exec -it maquinas_recreativas_php vendor/bin/phpunit -c tests/phpunit.xml --testsuite Smoke

test-functional: ## Ejecutar tests funcionales
	docker exec -it maquinas_recreativas_php vendor/bin/phpunit -c tests/phpunit.xml --testsuite Functional

test-unit: ## Ejecutar tests unitarios
	docker exec -it maquinas_recreativas_php vendor/bin/phpunit -c tests/phpunit.xml --testsuite Unit

test-integration: ## Ejecutar tests de integración
	docker exec -it maquinas_recreativas_php vendor/bin/phpunit -c tests/phpunit.xml --testsuite Integration

test-coverage: ## Ejecutar tests con cobertura de código
	docker exec -it maquinas_recreativas_php vendor/bin/phpunit -c tests/phpunit.xml --coverage-html coverage
	@echo "${GREEN} Reporte de cobertura generado en backend/coverage/index.html${RESET}"

test-performance: ## Ejecutar tests de performance
	docker exec -it maquinas_recreativas_php php tests/Performance/run_stress_test.php

test-security: ## Ejecutar tests de seguridad
	docker exec -it maquinas_recreativas_php php tests/Security/security-test.php

clean: ## Limpiar contenedores, volúmenes y cache
	cd docker && docker-compose down -v
	docker system prune -f
	@echo "${GREEN} Limpieza completada${RESET}"

restart: down up ## Reiniciar todos los servicios

status: ## Ver estado de los servicios
	cd docker && docker-compose ps

init: build up composer-install ## Inicializar proyecto completo
	@echo "${GREEN} Proyecto inicializado correctamente${RESET}"


redis-cli: ## Conectar a Redis CLI
	docker exec -it maquinas_recreativas_redis redis-cli

redis-flush: ## Limpiar toda la cache de Redis
	docker exec -it maquinas_recreativas_redis redis-cli FLUSHDB

redis-keys: ## Ver todas las claves en Redis
	docker exec -it maquinas_recreativas_redis redis-cli KEYS "maquinas:*"

redis-stats: ## Ver estadísticas de Redis
	docker exec -it maquinas_recreativas_redis redis-cli INFO stats

redis-monitor: ## Monitorear comandos Redis en tiempo real
	docker exec -it maquinas_recreativas_redis redis-cli MONITOR