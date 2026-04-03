<?php
// tests/Performance/run_stress_test.php

echo "\n";
echo "╔════════════════════════════════════════════════════════════════════════════╗\n";
echo "║              PRUEBAS DE ESTRÉS - SISTEMA maquinas_recreativas             ║\n";
echo "╚════════════════════════════════════════════════════════════════════════════╝\n\n";

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