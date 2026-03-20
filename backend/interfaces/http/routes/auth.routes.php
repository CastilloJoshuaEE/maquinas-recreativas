<?php
/**
 * RecreaSys - Authentication Routes
 * 
 * Rutas relacionadas con autenticación (login, logout, registro público).
 * 
 * @package RecreaSys\Interfaces\Http\Routes
 * @author Tu Equipo
 * @version 1.0
 */

return [
    // Login
    [
        'method'=>'POST',
        'path'=> '/usuario/login',
        'handler'=> [\RecreaSys\Interfaces\Http\Controller\UsuarioController::class, 'login'],
        'middleware' =>[]
    ],
    // Logout
    [
        'method'=> 'POST',
        'path'=> '/usuario/logout',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\UsuarioController::class, 'logout'],
        'middleware'=>[]
    ],
    // Registro público
    [
        'method'=> 'POST',
        'path'=> '/usuario/register',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\UsuarioController::class, 'register'],
        'middleware'=>[]
    ],
    // Recuperar contraseña
    [
        'method'=> 'POST',
        'path'=> '/usuario/recuperar-contrasena',
        'handler'=> [\RecreaSys\Interfaces\Http\Controller\UsuarioController::class, 'resetPassword'],
        'middleware'=>[]
    ],
    // Recuperar usuario (cambiar username)
    [
        'method'=> 'POST',
        'path'=> '/usuario/recuperar-usuario',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\UsuarioController::class, 'updateUsername'],
        'middleware'=>[]
    ],
    // Buscar por email
    [
        'method'=> 'POST',
        'path'=> '/usuario/buscar-email',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\UsuarioController::class, 'buscarPorEmail'],
        'middleware'=>[]

    ]
    
];