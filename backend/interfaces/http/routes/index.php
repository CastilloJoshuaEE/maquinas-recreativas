<?php
/**
 * maquinas_recreativas - Routes Index
 * 
 * Archivo principal de rutas que carga todas las rutas organizadas.
 * 
 * @package maquinas_recreativas\Interfaces\Http\Routes
 * @author Tu Equipo
 * @version 1.0
 */

return [
    // Health check
    [
        'method' => 'GET',
        'path' => '/health',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controller\HealthController::class, 'check'],
        'middleware' => []
    ],
    
    // Test DB
    [
        'method' => 'GET',
        'path' => '/test-db',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controller\HealthController::class, 'testDb'],
        'middleware' => []
    ]
];