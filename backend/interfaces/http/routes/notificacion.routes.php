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
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controller\NotificacionController::class, 'obtenerPorUsuario'],
        'middleware' => []
    ],
    
    // Obtener notificaciones de reporte por usuario
    [
        'method' => 'GET',
        'path' => '/notificaciones/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controller\NotificacionController::class, 'getNotificaciones'],
        'middleware' => []
    ],
    
    // Marcar notificación como leída
    [
        'method' => 'POST',
        'path' => '/notificaciones/:uuid/marcarla-leida',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controller\NotificacionController::class, 'marcarComoLeidaNotificacion'],
        'middleware' => []
    ],
    
    // Marcar todas como leídas
    [
        'method' => 'POST',
        'path' => '/notificaciones/marcarla-todas-leidas',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controller\NotificacionController::class, 'marcarTodasComoLeidas'],
        'middleware' => []
    ],
    
    // Crear notificación
    [
        'method' => 'POST',
        'path' => '/notificaciones/create',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controller\NotificacionController::class, 'create'],
        'middleware' => []
    ],
    
    // Marcar como leída (versión legacy)
    [
        'method' => 'POST',
        'path' => '/notificaciones/marcar-leida',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controller\NotificacionController::class, 'marcarComoLeida'],
        'middleware' => []
    ],
    
    // Obtener no leídas
    [
        'method' => 'GET',
        'path' => '/notificaciones/no-leidas/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controller\NotificacionController::class, 'obtenerNoLeidas'],
        'middleware' => []
    ]
];