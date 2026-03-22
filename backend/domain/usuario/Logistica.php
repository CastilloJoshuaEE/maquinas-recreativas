<?php

declare(strict_types=1);

namespace RecreaSys\Domain\Usuario;

use RecreaSys\Domain\Shared\ValueObjects\Uuid;

/**
 * Entidad de dominio que representa a un usuario de Logística.
 * 
 * @package RecreaSys\Domain\Usuario
 * @version 1.0
 */
final class Logistica extends Usuario
{
    /**
     * Constructor de la entidad Logística.
     *
     * @param Uuid $id
     * @param string $nombre
     * @param string $apellido
     * @param string $ci
     * @param Email $email
     * @param string $usuarioAsignado
     * @param string $contrasenaHash
     * @param EstadoUsuario $estado
     */
    public function __construct(
        Uuid $id,
        string $nombre,
        string $apellido,
        string $ci,
        Email $email,
        string $usuarioAsignado,
        string $contrasenaHash,
        EstadoUsuario $estado
    ) {
        parent::__construct(
            $id,
            $nombre,
            $apellido,
            $ci,
            $email,
            $usuarioAsignado,
            $contrasenaHash,
            new TipoUsuario(TipoUsuario::LOGISTICA),
            $estado
        );
    }

    /**
     * Convierte la entidad a un array para persistencia o respuesta.
     *
     * @return array
     */
    public function toArray(): array
    {
        return parent::toArray();
    }    
}