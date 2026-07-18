<?php
/**
 * maquinas_recreativas - Historial Routes
 * 
 * Rutas para consulta de historial.
 * 
 * @package maquinas_recreativas\Interfaces\Http\Routes
 * @author Tu Equipo
 * @version 1.0
 */

return [
    // Historial por máquina
    [
        'method' => 'GET',
        'path' => '/historial/maquina/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\HistorialMaquinaController::class, 'getHistorialPorMaquina'],
        'middleware' => []
    ],
    
    // Historial por usuario
    [
        'method' => 'GET',
        'path' => '/historial/usuario/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\HistorialMaquinaController::class, 'getHistorialPorUsuario'],
        'middleware' => []
    ],
    
    // Historial general con filtros
    [
        'method' => 'GET',
        'path' => '/historial/general',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\HistorialMaquinaController::class, 'getHistorialGeneral'],
        'middleware' => []
    ],
    
    // Resumen de actividades recientes
    [
        'method' => 'GET',
        'path' => '/historial/resumen',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\HistorialMaquinaController::class, 'getResumenReciente'],
        'middleware' => []
    ]
];