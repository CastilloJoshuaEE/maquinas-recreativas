<?php
// tests/Performance/run_stress_test.php
if (!defined('APP_ENV')) {
    define('APP_ENV', 'testing');
}
putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';
$_SERVER['APP_ENV'] = 'testing';

// Cargar variables de entorno de testing
$envFile = __DIR__ . '/../../.env.testing';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \"'");
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Forzar la base de datos de pruebas
putenv('DB_NAME=' . ($_ENV['DB_NAME_TEST'] ?? 'test_bd_recrea_sys'));
putenv('DB_NAME_TEST=' . ($_ENV['DB_NAME_TEST'] ?? 'test_bd_recrea_sys'));

echo "\n";
echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║              PRUEBAS DE ESTRÉS - SISTEMA maquinas_recreativas             ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

echo "ENTORNO: " . APP_ENV . "\n";
echo "BASE DE DATOS: " . getenv('DB_NAME') . "\n\n";
// Verificar que el backend está corriendo
echo "Verificando servidor backend...\n";
$ch = curl_init('http://localhost:8000/api/public/health');
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