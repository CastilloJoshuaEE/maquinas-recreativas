<?php
/**
 * RecreaSys - Comentario Routes
 * 
 * Rutas para gestión de comentarios.
 * 
 * @package RecreaSys\Interfaces\Http\Routes
 * @author Tu Equipo
 * @version 1.0
 */

return [
    // Crear comentario
    [
        'method' => 'POST',
        'path' => '/comentarios',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\ComentarioController::class, 'create'],
        'middleware' => []
    ],
    
    // Obtener comentarios por reporte
    [
        'method' => 'GET',
        'path' => '/comentarios/reporte/:uuid',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\ComentarioController::class, 'getByReporte'],
        'middleware' => []
    ]
];