<?php
/**
 * application/queries/componente/ObtenerComponentes.php
 *
 * Query para obtener componentes con paginación y filtro por tipo.
 *
 * @package maquinas_recreativas\Application\Queries\Componente
 */

namespace maquinas_recreativas\Application\Queries\Componente;

/**
 * Class ObtenerComponentesQuery
 */
final class ObtenerComponentesQuery
{
    private ?string $tipo;
    private int $limit;
    private int $offset;

    public function __construct(?string $tipo = null, int $limit = 10, int $offset = 0)
    {
        $this->tipo = $tipo;
        $this->limit = $limit;
        $this->offset = $offset;
    }

    public function getTipo(): ?string { return $this->tipo; }
    public function getLimit(): int { return $this->limit; }
    public function getOffset(): int { return $this->offset; }
}