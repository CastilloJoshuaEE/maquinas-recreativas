<?php
/**
 * maquinas_recreativas - Usuario Public Routes
 * 
 * Rutas públicas de usuario (no requieren autenticación).
 * 
 * @package maquinas_recreativas\Interfaces\Http\Routes
 * @author Tu Equipo
 * @version 1.0
 */

return [
    // Registro de nuevo usuario
    [
        'method' => 'POST',
        'path' => '/usuario/register',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'register'],
        'middleware' => []
    ],
    
    // Inicio de sesión
    [
        'method' => 'POST',
        'path' => '/usuario/login',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'login'],
        'middleware' => []
    ],
    
    // Buscar usuario por email
    [
        'method' => 'POST',
        'path' => '/usuario/buscar-email',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'buscarPorEmail'],
        'middleware' => []
    ],
    
    // Recuperar contraseña
    [
        'method' => 'POST',
        'path' => '/usuario/recuperar-contrasena',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'resetPassword'],
        'middleware' => []
    ],
    
    // Recuperar/actualizar nombre de usuario
    [
        'method' => 'POST',
        'path' => '/usuario/recuperar-usuario',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'updateUsername'],
        'middleware' => []
    ],
    
    // Cierre de sesión
    [
        'method' => 'POST',
        'path' => '/usuario/logout',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'logout'],
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
    ]

];