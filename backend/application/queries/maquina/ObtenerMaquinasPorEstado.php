<?php
/**
 * application/queries/maquina/ObtenerMaquinasPorEstado.php
 *
 * Query para obtener máquinas por estado.
 *
 * @package maquinas_recreativas\Application\Queries\Maquina
 */

namespace maquinas_recreativas\Application\Queries\Maquina;

/**
 * Class ObtenerMaquinasPorEstadoQuery
 */
final class ObtenerMaquinasPorEstadoQuery
{
    private string $estado;

    public function __construct(string $estado)
    {
        $this->estado = $estado;
    }

    public function getEstado(): string { return $this->estado; }
}