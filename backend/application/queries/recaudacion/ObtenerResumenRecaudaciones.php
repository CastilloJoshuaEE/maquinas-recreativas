<?php
/**
 * application/queries/recaudacion/ObtenerResumenRecaudaciones.php
 *
 * Query para obtener resumen de recaudaciones por tipo de comercio.
 *
 * @package maquinas_recreativas\Application\Queries\Recaudacion
 */

namespace maquinas_recreativas\Application\Queries\Recaudacion;

/**
 * Class ObtenerResumenRecaudacionesQuery
 */
final class ObtenerResumenRecaudacionesQuery
{
    private ?int $limit;

    public function __construct(?int $limit = null)
    {
        $this->limit = $limit;
    }

    public function getLimit(): ?int { return $this->limit; }
}