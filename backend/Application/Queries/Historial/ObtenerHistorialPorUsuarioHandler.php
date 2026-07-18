<?php
/**
 * application/queries/historial/ObtenerHistorialPorUsuarioHandler.php
 *
 * Manejador del query ObtenerHistorialPorUsuario.
 *
 * @package maquinas_recreativas\Application\Queries\Historial
 */

namespace maquinas_recreativas\Application\Queries\Historial;

use maquinas_recreativas\Domain\Historial\HistorialRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class ObtenerHistorialPorUsuarioHandler
 */
final class ObtenerHistorialPorUsuarioHandler
{
    private HistorialRepository $historialRepository;
    private UsuarioRepository $usuarioRepository;

    public function __construct(
        HistorialRepository $historialRepository,
        UsuarioRepository $usuarioRepository
    ) {
        $this->historialRepository = $historialRepository;
        $this->usuarioRepository = $usuarioRepository;
    }

    /**
     * Maneja el query de obtener historial por usuario.
     *
     * @param ObtenerHistorialPorUsuarioQuery $query
     * @return array
     * @throws DomainException
     */
    public function handle(ObtenerHistorialPorUsuarioQuery $query): array
    {
        $idUsuario = new Uuid($query->getIdUsuario());

        $usuario = $this->usuarioRepository->findById($idUsuario);
        if (!$usuario) {
            throw new DomainException('Usuario no encontrado.');
        }

        $offset = ($query->getPagina() - 1) * $query->getPorPagina();
        $historial = $this->historialRepository->findByUsuario($idUsuario, $query->getPorPagina(), $offset);
        $total = $this->historialRepository->countByFilters(null, $idUsuario);

        return [
            'historial' => array_map(function ($item) {
                return [
                    'id' => $item->id()->value(),
                    'fecha_hora' => $item->fechaHora()->format('Y-m-d H:i:s'),
                    'id_maquina' => $item->idMaquina()->value(),
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