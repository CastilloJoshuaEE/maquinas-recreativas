<?php

declare(strict_types=1);

namespace RecreaSys\Application\Query\Usuario;

use RecreaSys\Application\Query\Query;
use RecreaSys\Domain\Shared\ValueObjects\Uuid;

/**
 * Query para obtener usuarios por tipo (con opción de excluir un ID).
 *
 * @package RecreaSys\Application\Query\Usuario
 * @version 1.0
 */
final class ObtenerUsuariosPorTipoQuery implements Query
{
    public string $tipo;
    public ?Uuid $excluirId;
     /**
     * Constructor de la query.
     *
     * @param string $tipo
     * @param Uuid|null $excluirId
     */
    public function __construct(string $tipo, ?Uuid $excluirId = null)
    {
        $this->tipo = $tipo;
        $this->excluirId = $excluirId;
    }       
}