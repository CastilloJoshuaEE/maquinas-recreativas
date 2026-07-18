<?php
/**
 * application/queries/recaudacion/ObtenerMaquinasRecaudacionHandler.php
 *
 * Manejador del query ObtenerMaquinasRecaudacion.
 *
 * @package maquinas_recreativas\Application\Queries\Recaudacion
 */

namespace maquinas_recreativas\Application\Queries\Recaudacion;

use maquinas_recreativas\Domain\Recaudacion\RecaudacionRepository;

/**
 * Class ObtenerMaquinasRecaudacionHandler
 */
final class ObtenerMaquinasRecaudacionHandler
{
    private RecaudacionRepository $recaudacionRepository;

    public function __construct(RecaudacionRepository $recaudacionRepository)
    {
        $this->recaudacionRepository = $recaudacionRepository;
    }

    /**
     * Maneja el query de obtener máquinas en recaudación.
     *
     * @param ObtenerMaquinasRecaudacionQuery $query
     * @return array
     */
    public function handle(ObtenerMaquinasRecaudacionQuery $query): array
    {
        return $this->recaudacionRepository->findMaquinasRecaudacion();
    }
}