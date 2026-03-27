<?php
/**
 * application/queries/maquina/ObtenerMaquinasPorTecnicoMantenimientoQuery.php
 *
 * Query para obtener máquinas asignadas a un técnico de mantenimiento.
 *
 * @package maquinas_recreativas\Application\Queries\Maquina
 */

namespace maquinas_recreativas\Application\Queries\Maquina;

/**
 * Class ObtenerMaquinasPorTecnicoMantenimientoQuery
 */
final class ObtenerMaquinasPorTecnicoMantenimientoQuery
{
    private string $idTecnico;

    public function __construct(string $idTecnico)
    {
        $this->idTecnico = $idTecnico;
    }

    public function getIdTecnico(): string { return $this->idTecnico; }
}