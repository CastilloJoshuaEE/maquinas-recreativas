<?php

declare(strict_types=1);

namespace maquinas_recreativas\Application\Commands\Usuario;

use maquinas_recreativas\Application\Commands\Command;

/**
 * Comando para registrar un usuario por un administrador.
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