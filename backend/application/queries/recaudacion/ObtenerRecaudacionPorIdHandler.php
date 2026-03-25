<?php
/**
 * application/queries/recaudacion/ObtenerRecaudacionPorIdHandler.php
 *
 * Manejador del query ObtenerRecaudacionPorId.
 *
 * @package maquinas_recreativas\Application\Queries\Recaudacion
 */

namespace maquinas_recreativas\Application\Queries\Recaudacion;

use maquinas_recreativas\Domain\Recaudacion\RecaudacionRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class ObtenerRecaudacionPorIdHandler
 */
final class ObtenerRecaudacionPorIdHandler
{
    private RecaudacionRepository $recaudacionRepository;

    public function __construct(RecaudacionRepository $recaudacionRepository)
    {
        $this->recaudacionRepository = $recaudacionRepository;
    }

    /**
     * Maneja el query de obtener recaudación por ID.
     *
     * @param ObtenerRecaudacionPorIdQuery $query
     * @return array
     * @throws DomainException
     */
    public function handle(ObtenerRecaudacionPorIdQuery $query): array
    {
        $idRecaudacion = new Uuid($query->getIdRecaudacion());

        $recaudacion = $this->recaudacionRepository->findById($idRecaudacion);
        if (!$recaudacion) {
            throw new DomainException('Recaudación no encontrada.');
        }

        return [
            'recaudacion' => [
                'id' => $recaudacion->id()->value(),
                'id_maquina' => $recaudacion->idMaquina()->value(),
                'id_usuario' => $recaudacion->idUsuario()->value(),
                'tipo_comercio' => $recaudacion->tipoComercio(),
                'monto_total' => $recaudacion->montoTotal(),
                'monto_empresa' => $recaudacion->montoEmpresa(),
                'monto_comercio' => $recaudacion->montoComercio(),
                'porcentaje_comercio' => $recaudacion->porcentajeComercio(),
                'detalle' => $recaudacion->detalle(),
                'fecha' => $recaudacion->fecha()->format('Y-m-d H:i:s')
            ]
        ];
    }
}