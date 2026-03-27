<?php
/**
 * application/queries/maquina/ObtenerComponentesMaquinaQuery.php
 *
 * Query para obtener componentes de una máquina.
 *
 * @package maquinas_recreativas\Application\Queries\Maquina
 */

namespace maquinas_recreativas\Application\Queries\Maquina;

/**
 * Class ObtenerComponentesMaquinaQuery
 */
final class ObtenerComponentesMaquinaQuery
{
    private string $idMaquina;

    public function __construct(string $idMaquina)
    {
        $this->idMaquina = $idMaquina;
    }

    public function getIdMaquina(): string { return $this->idMaquina; }
}