<?php
/**
 * RecreaSys - Routes Index
 * 
 * Archivo principal de rutas que carga todas las rutas organizadas.
 * 
 * @package RecreaSys\Interfaces\Http\Routes
 * @author Tu Equipo
 * @version 1.0
 */

return [
    // Health check
    [
        'method' => 'GET',
        'path' => '/health',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\HealthController::class, 'check'],
        'middleware' => []
    ],
    
    // Test DB
    [
        'method' => 'GET',
        'path' => '/test-db',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\HealthController::class, 'testDb'],
        'middleware' => []
    ]
];