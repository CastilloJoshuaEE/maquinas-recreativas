<?php
/**
 * RecreaSys - Contabilidad Routes
 * 
 * Rutas para gestión de contabilidad y recaudaciones.
 * 
 * @package RecreaSys\Interfaces\Http\Routes
 * @author Tu Equipo
 * @version 1.0
 */

return [
    // Registrar recaudación
    [
        'method' => 'POST',
        'path' => '/contabilidad/registrar-recaudacion',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\InformeController::class, 'registrarRecaudacion'],
        'middleware' => []
    ],
    
    // Obtener recaudaciones
    [
        'method' => 'GET',
        'path' => '/contabilidad/recaudaciones',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\InformeController::class, 'obtenerRecaudaciones'],
        'middleware' => []
    ],
    
    // Obtener recaudación por ID
    [
        'method' => 'GET',
        'path' => '/contabilidad/recaudaciones/:uuid',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\InformeController::class, 'obtenerRecaudacion'],
        'middleware' => []
    ],
    
    // Obtener resumen de recaudaciones
    [
        'method' => 'GET',
        'path' => '/contabilidad/resumen-recaudaciones',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\InformeController::class, 'obtenerResumenRecaudaciones'],
        'middleware' => []
    ],
    
    // Actualizar recaudación
    [
        'method' => 'PUT',
        'path' => '/contabilidad/actualizar-recaudacion',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\InformeController::class, 'actualizarRecaudacion'],
        'middleware' => []
    ],
    
    // Eliminar recaudación
    [
        'method' => 'DELETE',
        'path' => '/contabilidad/eliminar-recaudacion/:uuid',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\InformeController::class, 'eliminarRecaudacion'],
        'middleware' => []
    ],
    
    // Obtener máquinas para recaudación
    [
        'method' => 'GET',
        'path' => '/contabilidad/maquinas-recaudacion',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\InformeController::class, 'obtenerMaquinasRecaudacion'],
        'middleware' => []
    ],
    
    // Obtener máquinas operativas por comercio
    [
        'method' => 'GET',
        'path' => '/contabilidad/maquinas-operativas-por-comercio',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\InformeController::class, 'obtenerMaquinasOperativasPorComercio'],
        'middleware' => []
    ],
    
    // Obtener máquina para recaudación
    [
        'method' => 'GET',
        'path' => '/contabilidad/maquina-recaudacion',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\InformeController::class, 'obtenerMaquinaRecaudacion'],
        'middleware' => []
    ],
    
    // Obtener comercio para recaudación
    [
        'method' => 'GET',
        'path' => '/contabilidad/comercio-recaudacion/:uuid',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\InformeController::class, 'obtenerComercioRecaudacion'],
        'middleware' => []
    ],
    
    // Guardar informe
    [
        'method' => 'POST',
        'path' => '/contabilidad/guardar-informe',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\InformeController::class, 'guardarInforme'],
        'middleware' => []
    ],
    
    // Obtener informe por recaudación
    [
        'method' => 'GET',
        'path' => '/contabilidad/informe/:uuid',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\InformeController::class, 'obtenerInformePorRecaudacion'],
        'middleware' => []
    ]
];