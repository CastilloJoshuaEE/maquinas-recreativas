<?php
/**
 * RecreaSys - Dependencies Configuration
 *
 * Archivo para configurar la inyección de dependencias.
 *
 * @package RecreaSys\Config
 * @author Tu Equipo
 * @version 1.0
 */

use RecreaSys\Infrastructure\Persistence\Repository\MySQLUsuarioRepository;
use RecreaSys\Infrastructure\Security\BcryptPasswordHasher;
use RecreaSys\Application\Command\Usuario\RegistrarUsuarioHandler;
use RecreaSys\UI\Http\Controller\UsuarioController;

// Obtener conexión a la base de datos (asumiendo que Database::getConnection() existe)
require_once __DIR__ . '/database.php';
$database = new Database();
$connection = $database->getConnection();

// --- Repositorios ---
$usuarioRepository = new MySQLUsuarioRepository($connection);

// --- Servicios de Infraestructura ---
$passwordHasher = new BcryptPasswordHasher(12);

// --- Handlers de Aplicación (Casos de Uso) ---
$registrarUsuarioHandler = new RegistrarUsuarioHandler($usuarioRepository, $passwordHasher);

// --- Controladores UI ---
$usuarioController = new UsuarioController($registrarUsuarioHandler, $usuarioRepository);

// Puedes exponer estos objetos globalmente o usar un array
$GLOBALS['container'] = [
    'UsuarioController' => $usuarioController,
    // ... otros controladores
];