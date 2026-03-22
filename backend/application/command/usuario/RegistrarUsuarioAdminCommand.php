<?php

declare(strict_types=1);

namespace RecreaSys\Application\Command\Usuario;

use RecreaSys\Application\Command\Command;

/**
 * Comando para registrar un usuario por un administrador.
 *
 * @package RecreaSys\Application\Command\Usuario
 * @version 1.0
 */
final class RegistrarUsuarioAdminCommand implements Command
{
    public string $nombre;
    public string $apellido;
    public string $ci;
    public string $email;
    public string $usuarioAsignado;
    public string $contrasena;
    public string $tipo;
    public string $estado;
    public ?string $especialidad;

    /**
     * Constructor del comando.
     *
     * @param string $nombre
     * @param string $apellido
     * @param string $ci
     * @param string $email
     * @param string $usuarioAsignado
     * @param string $contrasena
     * @param string $tipo
     * @param string $estado
     * @param string|null $especialidad
     */
    public function __construct(
        string $nombre,
        string $apellido,
        string $ci,
        string $email,
        string $usuarioAsignado,
        string $contrasena,
        string $tipo,
        string $estado = 'Activo',
        ?string $especialidad = null
    ) {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->ci = $ci;
        $this->email = $email;
        $this->usuarioAsignado = $usuarioAsignado;
        $this->contrasena = $contrasena;
        $this->tipo = $tipo;
        $this->estado = $estado;
        $this->especialidad = $especialidad;
    }
}