<?php
/**
 * maquinas_recreativas - Contabilidad Routes
 * 
 * Rutas para gestión de contabilidad y recaudaciones.
 * 
 * @package maquinas_recreativas\Interfaces\Http\Routes
 * @author Tu Equipo
 * @version 1.0
 */

return [
    // Registrar recaudación
    [
        'method' => 'POST',
        'path' => '/contabilidad/registrar-recaudacion',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\InformeController::class, 'registrarRecaudacion'],
        'middleware' => []
    ],
    
    // Obtener recaudaciones
    [
        'method' => 'GET',
        'path' => '/contabilidad/recaudaciones',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\InformeController::class, 'obtenerRecaudaciones'],
        'middleware' => []
    ],
    
    // Obtener recaudación por ID
    [
        'method' => 'GET',
        'path' => '/contabilidad/recaudaciones/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\InformeController::class, 'obtenerRecaudacion'],
        'middleware' => []
    ],
    
    // Obtener resumen de recaudaciones
    [
        'method' => 'GET',
        'path' => '/contabilidad/resumen-recaudaciones',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\InformeController::class, 'obtenerResumenRecaudaciones'],
        'middleware' => []
    ],
    
    // Actualizar recaudación
    [
        'method' => 'PUT',
        'path' => '/contabilidad/actualizar-recaudacion',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\InformeController::class, 'actualizarRecaudacion'],
        'middleware' => []
    ],
    
    // Eliminar recaudación
    [
        'method' => 'DELETE',
        'path' => '/contabilidad/eliminar-recaudacion/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\InformeController::class, 'eliminarRecaudacion'],
        'middleware' => []
    ],
    
    // Obtener máquinas para recaudación
    [
        'method' => 'GET',
        'path' => '/contabilidad/maquinas-recaudacion',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\InformeController::class, 'obtenerMaquinasRecaudacion'],
        'middleware' => []
    ],
    
    // Obtener máquinas operativas por comercio
    [
        'method' => 'GET',
        'path' => '/contabilidad/maquinas-operativas-por-comercio',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\InformeController::class, 'obtenerMaquinasOperativasPorComercio'],
        'middleware' => []
    ],
    
    // Obtener máquina para recaudación
    [
        'method' => 'GET',
        'path' => '/contabilidad/maquina-recaudacion',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\InformeController::class, 'obtenerMaquinaRecaudacion'],
        'middleware' => []
    ],
    
    // Obtener comercio para recaudación
    [
        'method' => 'GET',
        'path' => '/contabilidad/comercio-recaudacion/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\InformeController::class, 'obtenerComercioRecaudacion'],
        'middleware' => []
    ],
    
    // Guardar informe
    [
        'method' => 'POST',
        'path' => '/contabilidad/guardar-informe',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\InformeController::class, 'guardarInforme'],
        'middleware' => []
    ],
    
    // Obtener informe por recaudación
    [
        'method' => 'GET',
        'path' => '/contabilidad/informe/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\InformeController::class, 'obtenerInformePorRecaudacion'],
        'middleware' => []
    ]
];