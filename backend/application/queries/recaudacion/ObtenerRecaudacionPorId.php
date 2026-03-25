<?php
/**
 * application/queries/recaudacion/ObtenerRecaudacionPorId.php
 *
 * Query para obtener una recaudación por ID.
 *
 * @package maquinas_recreativas\Application\Queries\Recaudacion
 */

namespace maquinas_recreativas\Application\Queries\Recaudacion;

/**
 * Class ObtenerRecaudacionPorIdQuery
 */
final class ObtenerRecaudacionPorIdQuery
{
    private string $idRecaudacion;

    public function __construct(string $idRecaudacion)
    {
        $this->idRecaudacion = $idRecaudacion;
    }

    public function getIdRecaudacion(): string { return $this->idRecaudacion; }
}