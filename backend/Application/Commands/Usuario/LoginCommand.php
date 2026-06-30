<?php
/**
 * Comando para iniciar sesión de usuario
 */

namespace maquinas_recreativas\Application\Commands\Usuario;

use maquinas_recreativas\Application\Commands\Command;

class LoginCommand implements Command
{
    private string $usuarioAsignado;
    private string $contrasena;
    private ?string $ipAddress;
    private ?string $userAgent;
    
    public function __construct(
        string $usuarioAsignado,
        string $contrasena,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ) {
        $this->usuarioAsignado = $usuarioAsignado;
        $this->contrasena = $contrasena;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
    }
    
    public function getUsuarioAsignado(): string { return $this->usuarioAsignado; }
    public function getContrasena(): string { return $this->contrasena; }
    public function getIpAddress(): ?string { return $this->ipAddress; }
    public function getUserAgent(): ?string { return $this->userAgent; }
}