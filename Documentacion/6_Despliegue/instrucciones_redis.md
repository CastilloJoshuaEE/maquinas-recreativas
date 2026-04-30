# ENTORNO EN CMD DE WINDOWS

# 1. Levantar servicios

cd docker
docker-compose up -d

# 2. Verificar que Redis está corriendo

docker ps | findstr redis

# 3. Conectar a Redis CLI

docker exec -it maquinas_recreativas_redis redis-cli

# 4. Probar conexión desde PHP

docker exec -it maquinas_recreativas_php php -r "$redis = new Redis(); $redis->connect('redis', 6379); $redis->set('test_key', 'Hello Redis!'); echo $redis->get('test_key');"


# 5. Ver la clave creada

docker exec -it maquinas_recreativas_redis redis-cli GET test_key

# 6. Ver todas las claves de tu app

docker exec -it maquinas_recreativas_redis redis-cli KEYS '*'
