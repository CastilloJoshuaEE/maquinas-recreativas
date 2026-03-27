<?php
/**
 * application/queries/historial/ObtenerResumenRecienteQuery.php
 *
 * Query para obtener resumen de actividades recientes.
 *
 * @package maquinas_recreativas\Application\Queries\Historial
 */

namespace maquinas_recreativas\Application\Queries\Historial;

/**
 * Class ObtenerResumenRecienteQuery
 */
final class ObtenerResumenRecienteQuery
{
    private int $limite;

    public function __construct(int $limite = 20)
    {
        $this->limite = $limite;
    }

    public function getLimite(): int { return $this->limite; }
}