<?php
/**
 * application/queries/recaudacion/ObtenerComercioRecaudacionQuery.php
 *
 * Query para obtener comercio para recaudación.
 *
 * @package maquinas_recreativas\Application\Queries\Recaudacion
 */

namespace maquinas_recreativas\Application\Queries\Recaudacion;

/**
 * Class ObtenerComercioRecaudacionQuery
 */
final class ObtenerComercioRecaudacionQuery
{
    private string $idComercio;

    public function __construct(string $idComercio)
    {
        $this->idComercio = $idComercio;
    }

    public function getIdComercio(): string { return $this->idComercio; }
}