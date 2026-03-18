<?php
// tests/scripts/test_connection.php

require_once __DIR__ . '/bootstrap.php';

echo "🔍 VERIFICANDO CONEXIÓN A BASE DE DATOS DE PRUEBAS\n";
echo "==================================================\n\n";

// Verificar qué base de datos estamos usando
$result = $conn->query("SELECT DATABASE() as db");
$row = $result->fetch_assoc();
echo "📊 Base de datos actual: " . $row['db'] . "\n\n";

// Verificar que las tablas existen
$tables = ['usuario', 'tecnico', 'logistica', 'comercio', 'maquinarecreativa'];
echo "📋 Verificando tablas:\n";
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "   ✅ Tabla '$table' existe\n";
    } else {
        echo "   ❌ Tabla '$table' NO existe\n";
    }
}

echo "\n✅ Verificación completada\n";