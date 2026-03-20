<?php
/**
 * RecreaSys - Componente Routes
 * 
 * Rutas para gestión de componentes.
 * 
 * @package RecreaSys\Interfaces\Http\Routes
 * @author Tu Equipo
 * @version 1.0
 */

return [
    // Obtener componentes
    [
        'method' => 'GET',
        'path' => '/componentes',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\ComponenteController::class, 'obtenerComponentes'],
        'middleware' => []
    ],
    
    // Obtener componentes disponibles
    [
        'method' => 'GET',
        'path' => '/componentes/disponibles',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\ComponenteController::class, 'obtenerComponentesDisponibles'],
        'middleware' => []
    ],
    
    // Usar componente
    [
        'method' => 'POST',
        'path' => '/componentes/usar',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\ComponenteController::class, 'usarComponente'],
        'middleware' => []
    ],
    
    // Liberar componente
    [
        'method' => 'POST',
        'path' => '/componentes/liberar',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\ComponenteController::class, 'liberarComponente'],
        'middleware' => []
    ],
    
    // Asignar carcasa
    [
        'method' => 'POST',
        'path' => '/componentes/asignar-carcasa',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\ComponenteController::class, 'asignarCarcasa'],
        'middleware' => []
    ],
    
    // Liberar componentes por cancelación
    [
        'method' => 'POST',
        'path' => '/componentes/liberar-cancelacion',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\ComponenteController::class, 'liberarComponentesCancelacion'],
        'middleware' => []
    ],
    
    // Obtener componentes en uso
    [
        'method' => 'GET',
        'path' => '/componentes/en-uso/:uuid',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\ComponenteController::class, 'obtenerComponentesEnUso'],
        'middleware' => []
    ]
];