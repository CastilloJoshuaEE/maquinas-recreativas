<?php
/**
 * maquinas_recreativas - Administrador Routes
 * 
 * Rutas exclusivas para administradores.
 * 
 * @package maquinas_recreativas\Interfaces\Http\Routes
 * @author Tu Equipo
 * @version 1.0
 */

return [
    // Obtener todos los usuarios
    [
        'method' => 'GET',
        'path' => '/administrador/usuarios',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\AdministradorController::class, 'getAllUsers'],
        'middleware' => ['role:Administrador']
    ],
    
    // Registrar usuario como administrador
    [
        'method' => 'POST',
        'path' => '/administrador/usuarios',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\AdministradorController::class, 'registerAdmin'],
        'middleware' => ['role:Administrador']
    ],
    
    // Obtener usuario por ID
    [
        'method' => 'GET',
        'path' => '/administrador/usuarios/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\AdministradorController::class, 'getUser'],
        'middleware' => ['role:Administrador']
    ],
    
    // Actualizar usuario completo
    [
        'method' => 'PUT',
        'path' => '/administrador/usuarios/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\AdministradorController::class, 'updateUser'],
        'middleware' => ['role:Administrador']
    ],
    
    // Actualizar usuario parcialmente
    [
        'method' => 'PATCH',
        'path' => '/administrador/usuarios/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\AdministradorController::class, 'partialUpdateUser'],
        'middleware' => ['role:Administrador']
    ],
    
    // Eliminar usuario
    [
        'method' => 'DELETE',
        'path' => '/administrador/usuarios/:uuid',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\AdministradorController::class, 'deleteUser'],
        'middleware' => ['role:Administrador']
    ],
 // Enviar email (solo Administrador)
    [
        'method' => 'POST',
        'path' => '/administrador/enviar-email',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\EmailController::class, 'enviarEmail'],
        'middleware' => ['role:Administrador']
    ],
 
    // Verificar estado del servicio de email
    [
        'method' => 'GET',
        'path' => '/administrador/email/estado',
        'handler' => [\maquinas_recreativas\Interfaces\Http\Controllers\EmailController::class, 'estadoServicio'],
        'middleware' => ['role:Administrador']
    ],

];