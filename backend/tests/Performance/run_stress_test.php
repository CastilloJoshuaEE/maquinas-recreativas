<?php
// tests/Performance/run_stress_test.php

// ============================================================
// FORZAR ENTORNO DE PRUEBAS - DEBE ESTAR AL PRINCIPIO
// ============================================================

// 1. Definir constante TEST_ENVIRONMENT (la usa EnvManager)
if (!defined('TEST_ENVIRONMENT')) {
    define('TEST_ENVIRONMENT', true);
}

// 2. Forzar APP_ENV ANTES de cualquier cosa
putenv('APP_ENV=testing');
putenv('PHPUNIT_RUNNING=1');
$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';

// 3. Forzar variables de base de datos de pruebas
$dbNameTest = 'test_bd_recrea_sys';
putenv('DB_NAME_TEST=' . $dbNameTest);
putenv('DB_NAME=' . $dbNameTest);
putenv('DB_HOST=127.0.0.1');
putenv('DB_USER=recrea_user');
putenv('DB_PASS=recrea_pass123');
putenv('DB_PORT=3306');

$_ENV['DB_NAME_TEST'] = $dbNameTest;
$_ENV['DB_NAME'] = $dbNameTest;
$_SERVER['DB_NAME_TEST'] = $dbNameTest;
$_SERVER['DB_NAME'] = $dbNameTest;

// 4. Cargar .env.testing manualmente si es necesario
$envFile = __DIR__ . '/../../.env.testing';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1], " \"'");
            // Solo establecer si no existe ya
            if (!getenv($key)) {
                putenv("$key=$value");
            }
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
            }
            if (!isset($_SERVER[$key])) {
                $_SERVER[$key] = $value;
            }
        }
    }
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║              PRUEBAS DE ESTRÉS - SISTEMA maquinas_recreativas             ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "ENTORNO: " . (getenv('APP_ENV') ?: 'NO DEFINIDO') . "\n";
echo "BASE DE DATOS: " . (getenv('DB_NAME') ?: 'NO DEFINIDO') . "\n";
echo "BD PRUEBAS: " . (getenv('DB_NAME_TEST') ?: 'NO DEFINIDO') . "\n";

// Verificar que el backend está corriendo
echo "Verificando servidor backend...\n";
$ch = curl_init('http://localhost:8000/health');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "ERROR: Servidor backend no disponible en http://localhost:8000\n";
    echo "  Ejecuta en otra terminal: php -S localhost:8000 -t public\n";
    exit(1);
}
echo "✓ Servidor backend OK\n\n";

// Cargar la clase
require_once __DIR__ . '/StressTest.php';

// Crear instancia y ejecutar pruebas
echo "Inicializando pruebas de estrés...\n";
$test = new StressTest();
$test->init();
$test->testEjecutarEstres();

echo "\n✓ Pruebas de estrés completadas\n";