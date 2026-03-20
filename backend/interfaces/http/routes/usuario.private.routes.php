<?php
/**
 * RecreaSys - Usuario Private Routes
 * 
 * Rutas de usuario que requieren autenticación.
 * 
 * @package RecreaSys\Interfaces\Http\Routes
 * @author Tu Equipo
 * @version 1.0
 */

return [
    // Obtener perfil
    [
        'method' => 'GET',
        'path' => '/usuario/perfil',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\UsuarioController::class, 'getProfile'],
        'middleware' => []
    ],
    
    // Obtener perfil por ID
    [
        'method' => 'GET',
        'path' => '/usuario/profile/:uuid',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\UsuarioController::class, 'getProfile'],
        'middleware' => []
    ],
    
    // Actualizar perfil
    [
        'method' => 'POST',
        'path' => '/usuario/actualizar-perfil',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\UsuarioController::class, 'updateProfile'],
        'middleware' => []
    ],
    
    // Obtener técnicos por especialidad
    [
        'method' => 'GET',
        'path' => '/usuario/tecnicos/:slug',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\UsuarioController::class, 'obtenerTecnicos'],
        'middleware' => []
    ],
    
    // Obtener usuarios por tipo
    [
        'method' => 'GET',
        'path' => '/usuarios/por-tipo',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\UsuarioController::class, 'getByTipo'],
        'middleware' => []
    ],
    
    // Registrar actividad
    [
        'method' => 'POST',
        'path' => '/historial-actividades',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\UsuarioController::class, 'registrarActividad'],
        'middleware' => []
    ],
    
    // Obtener historial de actividades
    [
        'method' => 'GET',
        'path' => '/historial-actividades',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\UsuarioController::class, 'obtenerHistorialActividades'],
        'middleware' => []
    ]
];