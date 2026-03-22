<?php
/**
 * Comando para iniciar sesión de usuario
 * 
 * @package Application\Commands\Usuario
 * @author Tu Nombre
 * @version 1.0.0
 */

namespace Application\Commands\Usuario;

/**
 * @package Application\Commands\Usuario
 * 
 * Comando que encapsula los datos necesarios para
 * autenticar un usuario en el sistema.
 */
class LoginCommand {
    
    /**
     * @var string Nombre de usuario
     */
    private $usuarioAsignado;
    
    /**
     * @var string Contraseña en texto plano
     */
    private $contrasena;
    
    /**
     * @var string|null IP del cliente
     */
    private $ipAddress;
    
    /**
     * @var string|null User agent del cliente
     */
    private $userAgent;
    
    /**
     * Constructor del comando
     * 
     * @param string $usuarioAsignado Nombre de usuario
     * @param string $contrasena Contraseña
     * @param string|null $ipAddress IP del cliente
     * @param string|null $userAgent User agent
     */
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
    
    /**
     * Obtiene el nombre de usuario
     * 
     * @return string
     */
    public function getUsuarioAsignado(): string {
        return $this->usuarioAsignado;
    }
    
    /**
     * Obtiene la contraseña
     * 
     * @return string
     */
    public function getContrasena(): string {
        return $this->contrasena;
    }
    
    /**
     * Obtiene la IP del cliente
     * 
     * @return string|null
     */
    public function getIpAddress(): ?string {
        return $this->ipAddress;
    }
    
    /**
     * Obtiene el user agent
     * 
     * @return string|null
     */
    public function getUserAgent(): ?string {
        return $this->userAgent;
    }
}