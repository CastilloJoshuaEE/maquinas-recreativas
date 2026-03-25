<?php
/**
 * maquinas_recreativas - Reporte Routes
 * 
 * Rutas para gestión de reportes.
 * 
 * @package maquinas_recreativas\Interfaces\Http\Routes
 * @author Tu Equipo
 * @version 1.0
 */

return [
    // Crear reporte
    [
        'method' => 'POST',
        'path' => '/reportes/crear',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controller\ReporteController::class, 'create'],
        'middleware' => []
    ],
    
    // Obtener reportes por usuario
    [
        'method' => 'GET',
        'path' => '/reportes/usuario/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controller\ReporteController::class, 'getByUser'],
        'middleware' => []
    ],
    
    // Obtener chat entre dos usuarios
    [
        'method' => 'GET',
        'path' => '/reportes/chat/:uuid/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controller\ReporteController::class, 'getChat'],
        'middleware' => []
    ],
    
    // Actualizar estado de reporte
    [
        'method' => 'PUT',
        'path' => '/reportes/:uuid/estado',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controller\ReporteController::class, 'updateStatus'],
        'middleware' => []
    ],
    
    // Obtener usuarios de chat
    [
        'method' => 'GET',
        'path' => '/reportes/usuarios-chat',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controller\ReporteController::class, 'getUsuariosChat'],
        'middleware' => []
    ],
    
    // Obtener chat completo
    [
        'method' => 'GET',
        'path' => '/reportes/chat-completo',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controller\ReporteController::class, 'getCompleteChat'],
        'middleware' => []
    ]
];