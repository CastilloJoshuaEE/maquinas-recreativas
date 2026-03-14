<?php
// Script para servir el backend en un puerto independiente
$port = 8000;
$host = 'localhost';
$documentRoot = __DIR__ . '/public';

echo "Servidor backend iniciado en http://{$host}:{$port}\n";
echo "Presiona Ctrl+C para detener\n\n";

// Configurar CORS para permitir peticiones desde el frontend
$cmd = sprintf(
    'php -S %s:%d -t %s',
    $host,
    $port,
    $documentRoot
);

system($cmd);