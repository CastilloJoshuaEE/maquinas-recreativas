<?php
/**
 * maquinas_recreativas - Comentario Routes
 * 
 * Rutas para gestión de comentarios.
 * 
 * @package maquinas_recreativas\Interfaces\Http\Routes
 * @author Tu Equipo
 * @version 1.0
 */
return [
    // Crear comentario
    [
        'method' => 'POST',
        'path' => '/comentarios',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\ComentarioController::class, 'create'],
        'middleware' => []
    ],
    
    // Editar comentario
    [
        'method' => 'PUT',
        'path' => '/comentarios/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\ComentarioController::class, 'update'],
        'middleware' => []
    ],
    
    // Eliminar comentario
    [
        'method' => 'DELETE',
        'path' => '/comentarios/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\ComentarioController::class, 'delete'],
        'middleware' => []
    ],
    
    // Obtener comentarios por reporte
    [
        'method' => 'GET',
        'path' => '/comentarios/reporte/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\ComentarioController::class, 'getByReporte'],
        'middleware' => []
    ]
];