<?php
/**
 * application/queries/maquina/ObtenerMaquinasPorEtapa.php
 *
 * Query para obtener máquinas por etapa.
 *
 * @package maquinas_recreativas\Application\Queries\Maquina
 */

namespace maquinas_recreativas\Application\Queries\Maquina;

/**
 * Class ObtenerMaquinasPorEtapaQuery
 */
final class ObtenerMaquinasPorEtapaQuery
{
    private string $etapa;

    public function __construct(string $etapa)
    {
        $this->etapa = $etapa;
    }

    public function getEtapa(): string { return $this->etapa; }
}