<?php
/**
 * application/queries/maquina/ObtenerMaquinasPorTecnicoComprobadorQuery.php
 *
 * Query para obtener máquinas asignadas a un técnico comprobador.
 *
 * @package maquinas_recreativas\Application\Queries\Maquina
 */

namespace maquinas_recreativas\Application\Queries\Maquina;

/**
 * Class ObtenerMaquinasPorTecnicoComprobadorQuery
 */
final class ObtenerMaquinasPorTecnicoComprobadorQuery
{
    private string $idTecnico;

    public function __construct(string $idTecnico)
    {
        $this->idTecnico = $idTecnico;
    }

    public function getIdTecnico(): string { return $this->idTecnico; }
}