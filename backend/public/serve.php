<?php
/**
 * RecreaSys - Development Server
 * 
 * Script para iniciar el servidor de desarrollo PHP.
 * 
 * @package RecreaSys\Public
 * @author Tu Equipo
 * @version 1.0
 */
// Configuracion del servidor
$port = 8000;
$host = 'localhost';
$documentRoot = __DIR__;

echo "Servidor backend iniciado en http://{$host}:{$port}\n";
echo "Presiona Ctrl+C para detener\n\n";

// Comando para iniciar el servidor
$cmd = sprintf(
    'php -S %s:%d -t %s',
    $host,
    $port,
    $documentRoot
);

system($cmd);