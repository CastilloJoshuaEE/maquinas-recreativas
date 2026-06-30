<?php
/**
 * maquinas_recreativas - Usuario Private Routes
 * 
 * Rutas de usuario que requieren autenticación.
 * 
 * @package maquinas_recreativas\Interfaces\Http\Routes
 * @author Tu Equipo
 * @version 1.0
 */

return [
    // Obtener perfil
    [
        'method' => 'GET',
        'path' => '/usuario/perfil',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'getProfile'],
        'middleware' => []
    ],
    
    // Obtener perfil por ID
    [
        'method' => 'GET',
        'path' => '/usuario/profile/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'getProfile'],
        'middleware' => []
    ],
    
    // Actualizar perfil
    [
        'method' => 'POST',
        'path' => '/usuario/actualizar-perfil',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'updateProfile'],
        'middleware' => []
    ],
    
    // Obtener técnicos por especialidad
    [
        'method' => 'GET',
        'path' => '/usuario/tecnicos/:slug',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'obtenerTecnicos'],
        'middleware' => []
    ],
    
    // Obtener usuarios por tipo
    [
        'method' => 'GET',
        'path' => '/usuarios/por-tipo',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'getByTipo'],
        'middleware' => []
    ],
    
    // Registrar actividad
    [
        'method' => 'POST',
        'path' => '/historial-actividades',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'registrarActividad'],
        'middleware' => []
    ],
    
    // Obtener historial de actividades
    [
        'method' => 'GET',
        'path' => '/historial-actividades',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'obtenerHistorialActividades'],
        'middleware' => []
    ]
];