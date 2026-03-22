<?php

declare(strict_types=1);

use RecreaSys\Interfaces\Http\Controllers\UsuarioController;

/**
 * Definición de rutas públicas para el módulo de Usuario.
 * No requieren autenticación.
 * 
 * @package RecreaSys\Interfaces\Http\Routes
 * @version 1.0
 */

// Registro de nuevo usuario
$router->post('/usuario/register', UsuarioController::class, 'register');

// Inicio de sesión
$router->post('/usuario/login', UsuarioController::class, 'login');

// Buscar usuario por email (público por necesidad de recuperación)
$router->post('/usuario/buscar-email', UsuarioController::class, 'buscarPorEmail');

// Recuperar contraseña
$router->post('/usuario/recuperar-contrasena', UsuarioController::class, 'resetPassword');

// Recuperar/actualizar nombre de usuario
$router->post('/usuario/recuperar-usuario', UsuarioController::class, 'updateUsername');

// Cierre de sesión (requiere sesión iniciada, pero no tiene contenido en el cuerpo)
$router->post('/usuario/logout', UsuarioController::class, 'logout');

// Obtener técnicos por especialidad (público porque se usa en formularios)
$router->get('/usuario/tecnicos/{especialidad}', UsuarioController::class, 'obtenerTecnicos');

// Obtener usuarios por tipo (con parámetros en query string)
$router->get('/usuarios/por-tipo', UsuarioController::class, 'getByTipo');