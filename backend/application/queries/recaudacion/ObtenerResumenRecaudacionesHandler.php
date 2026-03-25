<?php
/**
 * application/queries/recaudacion/ObtenerResumenRecaudacionesHandler.php
 *
 * Manejador del query ObtenerResumenRecaudaciones.
 *
 * @package maquinas_recreativas\Application\Queries\Recaudacion
 */

namespace maquinas_recreativas\Application\Queries\Recaudacion;

use maquinas_recreativas\Domain\Recaudacion\RecaudacionRepository;

/**
 * Class ObtenerResumenRecaudacionesHandler
 */
final class ObtenerResumenRecaudacionesHandler
{
    private RecaudacionRepository $recaudacionRepository;

    public function __construct(RecaudacionRepository $recaudacionRepository)
    {
        $this->recaudacionRepository = $recaudacionRepository;
    }

    /**
     * Maneja el query de obtener resumen de recaudaciones.
     *
     * @param ObtenerResumenRecaudacionesQuery $query
     * @return array
     */
    public function handle(ObtenerResumenRecaudacionesQuery $query): array
    {
        return $this->recaudacionRepository->findResumenByTipoComercio($query->getLimit());
    }
}