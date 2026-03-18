<?php
// tests/scripts/test_latency.php

require_once __DIR__ . '/bootstrap.php';

echo "🔍 PRUEBA DE LATENCIA EN INSERCIÓN DE USUARIOS\n";
echo "==============================================\n\n";

$conn = $testDb->getConnection();

// Crear un usuario de prueba
$usuario_asignado = 'test_latency_' . uniqid();

$sql = "INSERT INTO usuario (ID_Usuario, nombre, apellido, ci, email, usuario_asignado, contrasena, tipo, estado) 
        VALUES (UUID(), 'Test', 'Latency', '12345678', 'test@test.com', ?, 'password', 'Usuario', 'Activo')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario_asignado);
$start = microtime(true);
$stmt->execute();
$insertTime = microtime(true) - $start;

echo "⏱️  Tiempo de inserción: " . round($insertTime * 1000, 2) . " ms\n";

// Intentar recuperar inmediatamente
$getIdSql = "SELECT ID_Usuario FROM usuario WHERE usuario_asignado = ?";
$getIdStmt = $conn->prepare($getIdSql);
$getIdStmt->bind_param("s", $usuario_asignado);
$getIdStmt->execute();
$result = $getIdStmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "✅ UUID recuperado inmediatamente: " . $row['ID_Usuario'] . "\n";
} else {
    echo "❌ No se pudo recuperar inmediatamente\n";
    
    // Esperar y reintentar
    sleep(1);
    $getIdStmt->execute();
    $result = $getIdStmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "✅ UUID recuperado después de 1 segundo: " . $row['ID_Usuario'] . "\n";
    }
}

echo "\n✅ Prueba completada\n";