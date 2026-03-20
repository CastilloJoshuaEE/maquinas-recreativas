<?php
/**
 * RecreaSys - Administrador Routes
 * 
 * Rutas exclusivas para administradores.
 * 
 * @package RecreaSys\Interfaces\Http\Routes
 * @author Tu Equipo
 * @version 1.0
 */

return [
    // Obtener todos los usuarios
    [
        'method' => 'GET',
        'path' => '/administrador/usuarios',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\AdministradorController::class, 'getAllUsers'],
        'middleware' => ['role:Administrador']
    ],
    
    // Registrar usuario como administrador
    [
        'method' => 'POST',
        'path' => '/administrador/usuarios',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\AdministradorController::class, 'registerAdmin'],
        'middleware' => ['role:Administrador']
    ],
    
    // Obtener usuario por ID
    [
        'method' => 'GET',
        'path' => '/administrador/usuarios/:uuid',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\AdministradorController::class, 'getUser'],
        'middleware' => ['role:Administrador']
    ],
    
    // Actualizar usuario completo
    [
        'method' => 'PUT',
        'path' => '/administrador/usuarios/:uuid',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\AdministradorController::class, 'updateUser'],
        'middleware' => ['role:Administrador']
    ],
    
    // Actualizar usuario parcialmente
    [
        'method' => 'PATCH',
        'path' => '/administrador/usuarios/:uuid',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\AdministradorController::class, 'partialUpdateUser'],
        'middleware' => ['role:Administrador']
    ],
    
    // Eliminar usuario
    [
        'method' => 'DELETE',
        'path' => '/administrador/usuarios/:uuid',
        'handler' => [\RecreaSys\Interfaces\Http\Controller\AdministradorController::class, 'deleteUser'],
        'middleware' => ['role:Administrador']
    ]
];