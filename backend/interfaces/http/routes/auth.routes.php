<?php
/**
 * maquinas_recreativas - Authentication Routes
 * 
 * Rutas relacionadas con autenticación (login, logout, registro público).
 * 
 * @package maquinas_recreativas\Interfaces\Http\Routes
 * @author Tu Equipo
 * @version 1.0
 */

return [
    // Login
    [
        'method'=>'POST',
        'path'=> '/usuario/login',
        'handler'=> [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'login'],
        'middleware' =>[]
    ],
    // Logout
    [
        'method'=> 'POST',
        'path'=> '/usuario/logout',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'logout'],
        'middleware'=>[]
    ],
    // Registro público
    [
        'method'=> 'POST',
        'path'=> '/usuario/register',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'register'],
        'middleware'=>[]
    ],
    // Recuperar contraseña
    [
        'method'=> 'POST',
        'path'=> '/usuario/recuperar-contrasena',
        'handler'=> [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'resetPassword'],
        'middleware'=>[]
    ],
    // Recuperar usuario (cambiar username)
    [
        'method'=> 'POST',
        'path'=> '/usuario/recuperar-usuario',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'updateUsername'],
        'middleware'=>[]
    ],
    // Buscar por email
    [
        'method'=> 'POST',
        'path'=> '/usuario/buscar-email',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\UsuarioController::class, 'buscarPorEmail'],
        'middleware'=>[]

    ]
    
];