<?php
/**
 * maquinas_recreativas - Componente Routes
 * 
 * Rutas para gestión de componentes.
 * 
 * @package maquinas_recreativas\Interfaces\Http\Routes
 * @author Tu Equipo
 * @version 1.0
 */

return [
    // Obtener componentes
    [
        'method' => 'GET',
        'path' => '/componentes',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\ComponenteController::class, 'obtenerComponentes'],
        'middleware' => []
    ],
    
    // Obtener componentes disponibles
    [
        'method' => 'GET',
        'path' => '/componentes/disponibles',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\ComponenteController::class, 'obtenerComponentesDisponibles'],
        'middleware' => []
    ],
    
    // Usar componente
    [
        'method' => 'POST',
        'path' => '/componentes/usar',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\ComponenteController::class, 'usarComponente'],
        'middleware' => []
    ],
    
    // Liberar componente
    [
        'method' => 'POST',
        'path' => '/componentes/liberar',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\ComponenteController::class, 'liberarComponente'],
        'middleware' => []
    ],
    
    // Asignar carcasa
    [
        'method' => 'POST',
        'path' => '/componentes/asignar-carcasa',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\ComponenteController::class, 'asignarCarcasa'],
        'middleware' => []
    ],
    
    // Liberar componentes por cancelación
    [
        'method' => 'POST',
        'path' => '/componentes/liberar-cancelacion',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\ComponenteController::class, 'liberarComponentesCancelacion'],
        'middleware' => []
    ],
    
    // Obtener componentes en uso
    [
        'method' => 'GET',
        'path' => '/componentes/en-uso/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\ComponenteController::class, 'obtenerComponentesEnUso'],
        'middleware' => []
    ]
];