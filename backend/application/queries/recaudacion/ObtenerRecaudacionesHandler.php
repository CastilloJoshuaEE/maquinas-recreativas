<?php
/**
 * application/queries/recaudacion/ObtenerRecaudacionesHandler.php
 *
 * Manejador del query ObtenerRecaudaciones.
 *
 * @package maquinas_recreativas\Application\Queries\Recaudacion
 */

namespace maquinas_recreativas\Application\Queries\Recaudacion;

use maquinas_recreativas\Domain\Recaudacion\RecaudacionRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class ObtenerRecaudacionesHandler
 */
final class ObtenerRecaudacionesHandler
{
    private RecaudacionRepository $recaudacionRepository;

    public function __construct(RecaudacionRepository $recaudacionRepository)
    {
        $this->recaudacionRepository = $recaudacionRepository;
    }

    /**
     * Maneja el query de obtener recaudaciones.
     *
     * @param ObtenerRecaudacionesQuery $query
     * @return array
     * @throws DomainException
     */
    public function handle(ObtenerRecaudacionesQuery $query): array
    {
        $filters = [];

        if ($query->getFechaInicio() !== null) {
            $filters['fecha_inicio'] = $query->getFechaInicio();
        }

        if ($query->getFechaFin() !== null) {
            $filters['fecha_fin'] = $query->getFechaFin();
        }

        if ($query->getIdMaquina() !== null) {
            $filters['ID_Maquina'] = $query->getIdMaquina();
        }

        if ($query->getTipoComercio() !== null) {
            $tiposValidos = ['Minorista', 'Mayorista'];
            if (!in_array($query->getTipoComercio(), $tiposValidos, true)) {
                throw new DomainException('Tipo de comercio no válido.');
            }
            $filters['Tipo_Comercio'] = $query->getTipoComercio();
        }

        $recaudaciones = $this->recaudacionRepository->findAll(
            $filters,
            $query->getLimit(),
            $query->getOffset()
        );

        return [
            'recaudaciones' => $recaudaciones,
            'total' => count($recaudaciones)
        ];
    }
}