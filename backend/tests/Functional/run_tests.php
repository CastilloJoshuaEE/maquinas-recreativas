<?php
// tests/Functional/run_tests.php

echo "\n";
echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║     PRUEBAS FUNCIONALES (END-TO-END) - SISTEMA RECREASYS   ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

set_time_limit(600);

define('BASE_PATH', realpath(__DIR__ . '/../..'));

require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/helper/CifradoHelper.php';
require_once __DIR__ . '/../TestDatabase.php';

require_once __DIR__ . '/HttpTestCase.php';
require_once __DIR__ . '/UserFlowsTest.php';
require_once __DIR__ . '/AdminFlowsTest.php';
require_once __DIR__ . '/ReporteFlowsTest.php';

// ─────────────────────────────────────────
// Verificar servidor
// ─────────────────────────────────────────
echo "📡 Verificando servidor backend...\n";
$ch = curl_init('http://localhost:8000/health');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "❌ ERROR: Servidor backend no disponible en http://localhost:8000\n";
    echo "   Ejecuta: php backend/serve.php\n";
    exit(1);
}
echo "✅ Servidor backend OK\n\n";

// ─────────────────────────────────────────
// Función auxiliar: resetear rate limits via HTTP
// ─────────────────────────────────────────
function resetRateLimits() {
    $ch = curl_init('http://localhost:8000/reset-rate-limits');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code === 200) {
        echo "   🔄 Rate limits reseteados correctamente.\n";
    } else {
        echo "   ⚠️  No se pudo resetear rate limits (HTTP $code): $body\n";
    }
}

// ─────────────────────────────────────────
// Base de datos de pruebas
// ─────────────────────────────────────────
echo "📋 EJECUTANDO PRUEBAS FUNCIONALES (UNA POR UNA)\n";
echo "================================================\n\n";

try {
    $testDb = new TestDatabase();
    echo "✅ Base de datos de pruebas lista\n\n";
} catch (Exception $e) {
    echo "⚠️  Usando base de datos existente: " . $e->getMessage() . "\n\n";
}

$tests = [
    'UserFlowsTest'   => new UserFlowsTest(),
    'AdminFlowsTest'  => new AdminFlowsTest(),
    'ReporteFlowsTest' => new ReporteFlowsTest(),
];

$totalAssertions = 0;
$totalFailures   = 0;
$testResults     = [];

foreach ($tests as $name => $test) {
    echo "\n🔬 EJECUTANDO: $name\n";
    echo str_repeat("─", strlen($name) + 14) . "\n";

    // *** Resetear rate limits ANTES de cada suite ***
    resetRateLimits();

    // Limpiar cookies de sesiones anteriores
    $test->clearCookies();

    try {
        if ($name === 'UserFlowsTest') {
            $test->testFlujoCompletoUsuario();
        } elseif ($name === 'AdminFlowsTest') {
            $test->testFlujoCompletoAdministrador();
        } elseif ($name === 'ReporteFlowsTest') {
            $test->testFlujoCompletoReportes();
        }

        $summary         = $test->getAssertionSummary();
        $totalAssertions += $summary['total'];
        $totalFailures   += $summary['failures'];

        $testResults[$name] = [
            'assertions'    => $summary['total'],
            'failures'      => $summary['failures'],
            'failures_list' => $summary['failures_list'],
            'status'        => $summary['failures'] === 0 ? '✅' : '❌',
        ];

        if ($summary['failures'] > 0) {
            echo "\n   ❌ Fallos en $name:\n";
            foreach ($summary['failures_list'] as $failure) {
                echo "      • $failure\n";
            }
        } else {
            echo "\n   ✅ Todos los tests pasaron\n";
        }

    } catch (Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
        echo $e->getTraceAsString() . "\n";

        $testResults[$name] = [
            'assertions'    => 0,
            'failures'      => 1,
            'failures_list' => [$e->getMessage()],
            'status'        => '❌',
        ];
        $totalFailures++;
    }

    // Pequeña pausa entre suites (no es estrictamente necesaria dado el reset, pero ayuda)
    echo "\n⏱️  Pausa de 3 segundos antes de la siguiente prueba...\n";
    sleep(3);
}

// ─────────────────────────────────────────
// Resumen final
// ─────────────────────────────────────────
echo "\n\n";
echo "📊 RESUMEN DE PRUEBAS FUNCIONALES\n";
echo "==================================\n\n";

foreach ($testResults as $name => $result) {
    echo "{$result['status']} $name: {$result['assertions']} aserciones, {$result['failures']} fallos\n";
}

echo "\n";
echo str_repeat("─", 40) . "\n";
echo "TOTAL: $totalAssertions aserciones, $totalFailures fallos\n";

if ($totalFailures === 0) {
    echo "\n✅ TODAS LAS PRUEBAS FUNCIONALES EXITOSAS\n";
    exit(0);
} else {
    echo "\n❌ HAY FALLOS EN LAS PRUEBAS FUNCIONALES\n";
    exit(1);
}