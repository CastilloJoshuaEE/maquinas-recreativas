<?php
/**
 * maquinas_recreativas - Notificacion Routes
 * 
 * Rutas para gestión de notificaciones.
 * 
 * @package maquinas_recreativas\Interfaces\Http\Routes
 * @author Tu Equipo
 * @version 1.0
 */

return [
    // Obtener notificaciones de máquina por usuario
    [
        'method' => 'GET',
        'path' => '/notificaciones_maquina/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\NotificacionController::class, 'obtenerPorUsuario'],
        'middleware' => []
    ],
    
    // Obtener notificaciones de reporte por usuario
    [
        'method' => 'GET',
        'path' => '/notificaciones/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\NotificacionController::class, 'getNotificaciones'],
        'middleware' => []
    ],
    
    // Marcar notificación como leída
    [
        'method' => 'POST',
        'path' => '/notificaciones/:uuid/marcarla-leida',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\NotificacionController::class, 'marcarComoLeidaNotificacion'],
        'middleware' => []
    ],
    [
    'method' => 'POST',
    'path' => '/notificaciones_maquina/:uuid/marcar-leida',
    'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\NotificacionController::class, 'marcarComoLeidaMaquina'],
    'middleware' => []
],
    
    // Marcar todas como leídas
    [
        'method' => 'POST',
        'path' => '/notificaciones/marcarla-todas-leidas',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\NotificacionController::class, 'marcarTodasComoLeidas'],
        'middleware' => []
    ],
    
    // Crear notificación
    [
        'method' => 'POST',
        'path' => '/notificaciones/create',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\NotificacionController::class, 'create'],
        'middleware' => []
    ],
    
    // Marcar como leída (versión legacy)
    [
        'method' => 'POST',
        'path' => '/notificaciones/marcar-leida',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\NotificacionController::class, 'marcarComoLeida'],
        'middleware' => []
    ],
    
    // Obtener no leídas
    [
        'method' => 'GET',
        'path' => '/notificaciones/no-leidas/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\NotificacionController::class, 'obtenerNoLeidas'],
        'middleware' => []
    ]
];