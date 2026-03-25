<?php
/**
 * application/queries/recaudacion/ObtenerMaquinasOperativasPorComercio.php
 *
 * Query para obtener máquinas operativas por comercio.
 *
 * @package maquinas_recreativas\Application\Queries\Recaudacion
 */

namespace maquinas_recreativas\Application\Queries\Recaudacion;

/**
 * Class ObtenerMaquinasOperativasPorComercioQuery
 */
final class ObtenerMaquinasOperativasPorComercioQuery
{
    private string $idComercio;

    public function __construct(string $idComercio)
    {
        $this->idComercio = $idComercio;
    }

    public function getIdComercio(): string { return $this->idComercio; }
}