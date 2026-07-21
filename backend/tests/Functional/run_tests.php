<?php
// tests/Functional/run_tests.php

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║     PRUEBAS FUNCIONALES (END-TO-END) - SISTEMA RECREATIVA  ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

set_time_limit(600);

define('BASE_PATH', realpath(__DIR__ . '/../..'));

if (file_exists(BASE_PATH . '/Config/constants.php')) {
    require_once BASE_PATH . '/Config/constants.php';
}
if (file_exists(BASE_PATH . '/Infrastructure/Security/CifradoHelper.php')) {
    require_once BASE_PATH . '/Infrastructure/Security/CifradoHelper.php';
}

require_once __DIR__ . '/HttpTestCase.php';
require_once __DIR__ . '/UserFlowsTest.php';
require_once __DIR__ . '/AdminFlowsTest.php';
require_once __DIR__ . '/ReporteFlowsTest.php';
require_once __DIR__ . '/../bootstrap.php'; 
echo "Verificando servidor backend...\n";
$ch = curl_init('http://localhost:8000/api/public/health');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$body = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "ERROR: Servidor backend no disponible en http://localhost:8000\n";
    echo "   Ejecuta: php -S localhost:8000 -t backend/public\n";
    exit(1);
}
echo "Servidor backend OK\n\n";

function resetRateLimits() {
    $ch = curl_init('http://localhost:8000/reset-rate-limits');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code === 200) {
        echo "   Rate limits reseteados correctamente.\n";
    } else {
        echo "   No se pudo resetear rate limits (HTTP $code)\n";
    }
}

echo "EJECUTANDO PRUEBAS FUNCIONALES\n";
echo "================================\n\n";

$tests = [
    'UserFlowsTest'   => new UserFlowsTest(),
    'AdminFlowsTest'  => new AdminFlowsTest(),
    'ReporteFlowsTest' => new ReporteFlowsTest(),
];

$totalAssertions = 0;
$totalFailures   = 0;
$testResults     = [];

foreach ($tests as $name => $test) {
    echo "\nEJECUTANDO: $name\n";
    echo str_repeat("-", strlen($name) + 14) . "\n";

    resetRateLimits();
    cleanTestDatabase();
    $test->clearCookies();

    try {
        if ($name === 'UserFlowsTest') {
            $test->testFlujoCompletoUsuario();
        } elseif ($name === 'AdminFlowsTest') {
            $test->testFlujoCompletoAdministrador();
        } elseif ($name === 'ReporteFlowsTest') {
            $test->testFlujoCompletoReportes();
        }

        $summary = $test->getAssertionSummary();
        $totalAssertions += $summary['total'];
        $totalFailures   += $summary['failures'];

        $testResults[$name] = [
            'assertions'    => $summary['total'],
            'failures'      => $summary['failures'],
            'failures_list' => $summary['failures_list'],
            'status'        => $summary['failures'] === 0 ? 'PASS' : 'FAIL',
        ];

        if ($summary['failures'] > 0) {
            echo "\n   Fallos en $name:\n";
            foreach ($summary['failures_list'] as $failure) {
                echo "      - $failure\n";
            }
        } else {
            echo "\n   Todos los tests pasaron\n";
        }

    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";

        $testResults[$name] = [
            'assertions'    => 0,
            'failures'      => 1,
            'failures_list' => [$e->getMessage()],
            'status'        => 'FAIL',
        ];
        $totalFailures++;
    }

    echo "\nPausa de 2 segundos antes de la siguiente prueba...\n";
    sleep(2);
}

echo "\n\n";
echo "RESUMEN DE PRUEBAS FUNCIONALES\n";
echo "==================================\n\n";

foreach ($testResults as $name => $result) {
    echo "{$result['status']} $name: {$result['assertions']} aserciones, {$result['failures']} fallos\n";
}

echo "\n";
echo str_repeat("-", 40) . "\n";
echo "TOTAL: $totalAssertions aserciones, $totalFailures fallos\n";

if ($totalFailures === 0) {
    echo "\nTODAS LAS PRUEBAS FUNCIONALES EXITOSAS\n";
    exit(0);
} else {
    echo "\nHAY FALLOS EN LAS PRUEBAS FUNCIONALES\n";
    exit(1);
}