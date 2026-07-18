<?php
/**
 * application/queries/distribucion/ObtenerInformesDistribucionHandler.php
 *
 * Manejador del query ObtenerInformesDistribucion.
 *
 * @package maquinas_recreativas\Application\Queries\Distribucion
 */

namespace maquinas_recreativas\Application\Queries\Distribucion;

use maquinas_recreativas\Domain\Distribucion\DistribucionRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class ObtenerInformesDistribucionHandler
 */
final class ObtenerInformesDistribucionHandler
{
    private DistribucionRepository $distribucionRepository;

    public function __construct(DistribucionRepository $distribucionRepository)
    {
        $this->distribucionRepository = $distribucionRepository;
    }

    /**
     * Maneja el query de obtener informes de distribución.
     *
     * @param ObtenerInformesDistribucionQuery $query
     * @return array
     * @throws DomainException
     */
    public function handle(ObtenerInformesDistribucionQuery $query): array
    {
        $filters = [];

        if ($query->getEstado() !== null) {
            $estadosValidos = ['Operativa', 'Retirada', 'No operativa', 'Distribuyendose'];
            if (!in_array($query->getEstado(), $estadosValidos, true)) {
                throw new DomainException('Estado no válido.');
            }
            $filters['estado'] = $query->getEstado();
        }

        if ($query->getIdComercio() !== null) {
            $filters['ID_Comercio'] = $query->getIdComercio();
        }

        if ($query->getIdMaquina() !== null) {
            $filters['ID_Maquina'] = $query->getIdMaquina();
        }

        if ($query->getFechaInicio() !== null) {
            $filters['fecha_inicio'] = $query->getFechaInicio();
        }

        if ($query->getFechaFin() !== null) {
            $filters['fecha_fin'] = $query->getFechaFin();
        }

        $informes = $this->distribucionRepository->findAll(
            $filters,
            $query->getLimit(),
            $query->getOffset()
        );

        return [
            'informes' => $informes,
            'total' => count($informes)
        ];
    }
}