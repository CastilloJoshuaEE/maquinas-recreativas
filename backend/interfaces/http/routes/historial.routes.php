<?php
/**
 * RecreaSys - Historial Routes
 * 
 * Rutas para consulta de historial.
 * 
 * @package RecreaSys\Interfaces\Http\Routes
 * @author Tu Equipo
 * @version 1.0
 */

return [
    // Historial por máquina
    [
        'method' => 'GET',
        'path' => '/historial/maquina/:uuid',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\HistorialMaquinaController::class, 'getHistorialPorMaquina'],
        'middleware' => []
    ],
    
    // Historial por usuario
    [
        'method' => 'GET',
        'path' => '/historial/usuario/:uuid',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\HistorialMaquinaController::class, 'getHistorialPorUsuario'],
        'middleware' => []
    ],
    
    // Historial general con filtros
    [
        'method' => 'GET',
        'path' => '/historial/general',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\HistorialMaquinaController::class, 'getHistorialGeneral'],
        'middleware' => []
    ],
    
    // Resumen de actividades recientes
    [
        'method' => 'GET',
        'path' => '/historial/resumen',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\HistorialMaquinaController::class, 'getResumenReciente'],
        'middleware' => []
    ]
];