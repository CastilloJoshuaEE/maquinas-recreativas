<?php
/**
 * application/queries/historial/ObtenerHistorialPorMaquinaHandler.php
 *
 * Manejador del query ObtenerHistorialPorMaquina.
 *
 * @package maquinas_recreativas\Application\Queries\Historial
 */

namespace maquinas_recreativas\Application\Queries\Historial;

use maquinas_recreativas\Domain\Historial\HistorialRepository;
use maquinas_recreativas\Domain\Maquina\MaquinaRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class ObtenerHistorialPorMaquinaHandler
 */
final class ObtenerHistorialPorMaquinaHandler
{
    private HistorialRepository $historialRepository;
    private MaquinaRepository $maquinaRepository;

    public function __construct(
        HistorialRepository $historialRepository,
        MaquinaRepository $maquinaRepository
    ) {
        $this->historialRepository = $historialRepository;
        $this->maquinaRepository = $maquinaRepository;
    }

    /**
     * Maneja el query de obtener historial por máquina.
     *
     * @param ObtenerHistorialPorMaquinaQuery $query
     * @return array
     * @throws DomainException
     */
    public function handle(ObtenerHistorialPorMaquinaQuery $query): array
    {
        $idMaquina = new Uuid($query->getIdMaquina());

        $maquina = $this->maquinaRepository->findById($idMaquina);
        if (!$maquina) {
            throw new DomainException('Máquina no encontrada.');
        }

        $offset = ($query->getPagina() - 1) * $query->getPorPagina();
        $historial = $this->historialRepository->findByMaquina($idMaquina, $query->getPorPagina(), $offset);
        $total = $this->historialRepository->countByFilters($idMaquina);

        return [
            'historial' => array_map(function ($item) {
                return [
                    'id' => $item->id()->value(),
                    'fecha_hora' => $item->fechaHora()->format('Y-m-d H:i:s'),
                    'id_usuario' => $item->idUsuario()->value(),
                    'tipo_usuario' => $item->tipoUsuario(),
                    'accion' => $item->accion(),
                    'descripcion' => $item->descripcion(),
                    'estado_anterior' => $item->estadoAnterior(),
                    'estado_nuevo' => $item->estadoNuevo(),
                    'etapa_anterior' => $item->etapaAnterior(),
                    'etapa_nueva' => $item->etapaNueva(),
                    'ip_address' => $item->ipAddress(),
                    'detalles' => $item->detallesAdicionales()
                ];
            }, $historial),
            'paginacion' => [
                'pagina_actual' => $query->getPagina(),
                'por_pagina' => $query->getPorPagina(),
                'total' => $total,
                'total_paginas' => ceil($total / $query->getPorPagina())
            ]
        ];
    }
}