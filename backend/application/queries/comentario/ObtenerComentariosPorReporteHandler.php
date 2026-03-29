<?php
/**
 * application/queries/comentario/ObtenerComentariosPorReporteHandler.php
 *
 * Manejador del query ObtenerComentariosPorReporteHandler.
 *
 * @package maquinas_recreativas\Application\Queries\Comentario
 */

namespace maquinas_recreativas\Application\Queries\Comentario;

use maquinas_recreativas\Domain\Comentario\ComentarioRepository;
use maquinas_recreativas\Domain\Reporte\ReporteRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class ObtenerComentariosPorReporteHandler
 */
final class ObtenerComentariosPorReporteHandler
{
    private ComentarioRepository $comentarioRepository;
    private ReporteRepository $reporteRepository;
    private UsuarioRepository $usuarioRepository;

    public function __construct(
        ComentarioRepository $comentarioRepository,
        ReporteRepository $reporteRepository,
        UsuarioRepository $usuarioRepository
    ) {
        $this->comentarioRepository = $comentarioRepository;
        $this->reporteRepository = $reporteRepository;
        $this->usuarioRepository = $usuarioRepository;
    }

    /**
     * Maneja el query de obtener comentarios por reporte.
     *
     * @param ObtenerComentariosPorReporteQuery $query
     * @return array
     * @throws DomainException
     */
    public function handle(ObtenerComentariosPorReporteQuery $query): array
    {
        $idReporte = new Uuid($query->getIdReporte());
        $idUsuario = new Uuid($query->getIdUsuario());

        $reporte = $this->reporteRepository->findById($idReporte);
        if (!$reporte) {
            throw new DomainException('Reporte no encontrado.');
        }

        $usuario = $this->usuarioRepository->findById($idUsuario);
        if (!$usuario) {
            throw new DomainException('Usuario no encontrado.');
        }

        if (!$reporte->usuarioTienePermiso($idUsuario)) {
            throw new DomainException('No autorizado para ver los comentarios de este reporte.');
        }

        $comentarios = $this->comentarioRepository->findByReporte($idReporte, $idUsuario);

        return $comentarios;
    }
}