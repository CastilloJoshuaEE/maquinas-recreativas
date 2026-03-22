<?php

declare(strict_types=1);

namespace RecreaSys\Application\Query\Usuario;

use RecreaSys\Application\Query\Query;
use RecreaSys\Domain\Shared\ValueObjects\Uuid;

/**
 * Query para obtener el historial de actividades de un usuario.
 *
 * @package RecreaSys\Application\Query\Usuario
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