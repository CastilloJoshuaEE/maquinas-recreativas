<?php
/**
 * application/queries/historial/ObtenerResumenRecienteHandler.php
 *
 * Manejador del query ObtenerResumenReciente.
 *
 * @package maquinas_recreativas\Application\Queries\Historial
 */

namespace maquinas_recreativas\Application\Queries\Historial;

use maquinas_recreativas\Domain\Historial\HistorialRepository;

/**
 * Class ObtenerResumenRecienteHandler
 */
final class ObtenerResumenRecienteHandler
{
    private HistorialRepository $historialRepository;

    public function __construct(HistorialRepository $historialRepository)
    {
        $this->historialRepository = $historialRepository;
    }

    /**
     * Maneja el query de obtener resumen de actividades recientes.
     *
     * @param ObtenerResumenRecienteQuery $query
     * @return array
     */
    public function handle(ObtenerResumenRecienteQuery $query): array
    {
        return $this->historialRepository->getResumenReciente($query->getLimite());
    }
}