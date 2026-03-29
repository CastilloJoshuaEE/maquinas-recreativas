<?php

declare(strict_types=1);

namespace maquinas_recreativas\Application\Queries\Usuario;

use maquinas_recreativas\Application\Queries\Query;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

/**
 * Query para obtener el historial de actividades de un usuario.
 *
 * @package maquinas_recreativas\Application\Query\Usuario
 * @version 1.0
 */
final class ObtenerHistorialActividadesQuery implements Query
{
    public Uuid $usuarioId;

    /**
     * Constructor de la query.
     *
     * @param Uuid $usuarioId
     */
    public function __construct(Uuid $usuarioId)
    {
        $this->usuarioId = $usuarioId;
    }
}