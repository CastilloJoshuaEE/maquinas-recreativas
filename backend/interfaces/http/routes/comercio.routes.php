<?php
/**
 * RecreaSys - Comercio Routes
 * 
 * Rutas para gestión de comercios.
 * 
 * @package RecreaSys\Interfaces\Http\Routes
 * @author Tu Equipo
 * @version 1.0
 */

return [
    // Registrar comercio
    [
        'method' => 'POST',
        'path' => '/comercio/register',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\ComercioController::class, 'register'],
        'middleware' => []
    ],
    
    // Obtener todos los comercios
    [
        'method' => 'GET',
        'path' => '/comercio/all',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\ComercioController::class, 'obtenerComercios'],
        'middleware' => []
    ]
];