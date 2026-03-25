<?php
/**
 * application/queries/maquina/ObtenerMaquinasPorTecnicoEnsamblador.php
 *
 * Query para obtener máquinas asignadas a un técnico ensamblador.
 *
 * @package maquinas_recreativas\Application\Queries\Maquina
 */

namespace maquinas_recreativas\Application\Queries\Maquina;

/**
 * Class ObtenerMaquinasPorTecnicoEnsambladorQuery
 */
final class ObtenerMaquinasPorTecnicoEnsambladorQuery
{
    private string $idTecnico;

    public function __construct(string $idTecnico)
    {
        $this->idTecnico = $idTecnico;
    }

    public function getIdTecnico(): string { return $this->idTecnico; }
}