<?php

declare(strict_types=1);

namespace maquinas_recreativas\Application\Query\Usuario;

use maquinas_recreativas\Application\Query\Query;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

/**
 * Query para obtener usuarios por tipo (con opción de excluir un ID).
 *
 * @package maquinas_recreativas\Application\Query\Usuario
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