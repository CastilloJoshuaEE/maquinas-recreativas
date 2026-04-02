<?php
// tests/scripts/functional/check_database.php

require_once __DIR__ . '/../../bootstrap.php';

echo " VERIFICANDO BASE DE DATOS\n";
echo "============================\n\n";

$conn = $testDb->getConnection();

// Verificar qué base de datos estamos usando
$result = $conn->query("SELECT DATABASE() as db");
$row = $result->fetch_assoc();
echo " Base de datos actual: " . $row['db'] . "\n\n";

// Contar usuarios
$result = $conn->query("SELECT COUNT(*) as total FROM usuario");
$row = $result->fetch_assoc();
echo "👥 Total usuarios: " . $row['total'] . "\n";

// Mostrar algunos usuarios de ejemplo
$result = $conn->query("SELECT ID_Usuario, usuario_asignado, tipo FROM usuario LIMIT 5");
echo "\n📋 Primeros 5 usuarios:\n";
while ($row = $result->fetch_assoc()) {
    echo "   - {$row['usuario_asignado']} ({$row['tipo']}) [{$row['ID_Usuario']}]\n";
}

echo "\n Verificación completada\n";