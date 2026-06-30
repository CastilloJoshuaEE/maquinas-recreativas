<?php
/**
 * application/queries/historial/ObtenerHistorialGeneralHandler.php
 *
 * Manejador del query ObtenerHistorialGeneral.
 *
 * @package maquinas_recreativas\Application\Queries\Historial
 */

namespace maquinas_recreativas\Application\Queries\Historial;

use maquinas_recreativas\Domain\Historial\HistorialRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;

/**
 * Class ObtenerHistorialGeneralHandler
 */
final class ObtenerHistorialGeneralHandler
{
    private HistorialRepository $historialRepository;

    public function __construct(HistorialRepository $historialRepository)
    {
        $this->historialRepository = $historialRepository;
    }

    /**
     * Maneja el query de obtener historial general con filtros.
     *
     * @param ObtenerHistorialGeneralQuery $query
     * @return array
     */
    public function handle(ObtenerHistorialGeneralQuery $query): array
    {
        $idMaquina = $query->getIdMaquina() !== null ? new Uuid($query->getIdMaquina()) : null;
        $idUsuario = $query->getIdUsuario() !== null ? new Uuid($query->getIdUsuario()) : null;

        $offset = ($query->getPagina() - 1) * $query->getPorPagina();
        $historial = $this->historialRepository->findGeneral(
            $idMaquina,
            $idUsuario,
            $query->getTipoUsuario(),
            $query->getAccion(),
            $query->getFechaInicio(),
            $query->getFechaFin(),
            $query->getPorPagina(),
            $offset
        );

        $total = $this->historialRepository->countByFilters(
            $idMaquina,
            $idUsuario,
            $query->getTipoUsuario(),
            $query->getAccion(),
            $query->getFechaInicio(),
            $query->getFechaFin()
        );

        return [
            'historial' => array_map(function ($item) {
                return [
                    'id' => $item->id()->value(),
                    'fecha_hora' => $item->fechaHora()->format('Y-m-d H:i:s'),
                    'id_maquina' => $item->idMaquina()->value(),
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