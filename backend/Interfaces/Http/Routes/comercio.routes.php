<?php
/**
 * maquinas_recreativas - Comercio Routes
 * 
 * Rutas para gestión de comercios.
 * 
 * @package maquinas_recreativas\Interfaces\Http\Routes
 * @author Tu Equipo
 * @version 1.0
 */

return [
    // Registrar comercio
    [
        'method' => 'POST',
        'path' => '/comercio/register',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\ComercioController::class, 'register'],
        'middleware' => []
    ],
    
    // Obtener todos los comercios
    [
        'method' => 'GET',
        'path' => '/comercio/all',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\ComercioController::class, 'obtenerComercios'],
        'middleware' => []
    ],
    
    //  Actualizar comercio
    [
        'method' => 'PUT',
        'path' => '/comercio/actualizar/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\ComercioController::class, 'actualizar'],
        'middleware' => []
    ],
    
    //  Eliminar comercio
    [
        'method' => 'DELETE',
        'path' => '/comercio/eliminar/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\ComercioController::class, 'eliminar'],
        'middleware' => []
    ]
];