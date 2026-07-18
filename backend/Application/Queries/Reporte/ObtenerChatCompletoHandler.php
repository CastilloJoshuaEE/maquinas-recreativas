<?php
/**
 * application/queries/reporte/ObtenerChatCompletoHandler.php
 *
 * Manejador del query ObtenerChatCompleto.
 *
 * @package maquinas_recreativas\Application\Queries\Reporte
 */

namespace maquinas_recreativas\Application\Queries\Reporte;

use maquinas_recreativas\Domain\Reporte\ReporteRepository;
use maquinas_recreativas\Domain\Comentario\ComentarioRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class ObtenerChatCompletoHandler
 */
final class ObtenerChatCompletoHandler
{
    private ReporteRepository $reporteRepository;
    private ComentarioRepository $comentarioRepository;
    private UsuarioRepository $usuarioRepository;

    public function __construct(
        ReporteRepository $reporteRepository,
        ComentarioRepository $comentarioRepository,
        UsuarioRepository $usuarioRepository
    ) {
        $this->reporteRepository = $reporteRepository;
        $this->comentarioRepository = $comentarioRepository;
        $this->usuarioRepository = $usuarioRepository;
    }

    /**
     * Maneja el query de obtener chat completo.
     *
     * @param ObtenerChatCompletoQuery $query
     * @return array
     * @throws DomainException
     */
    public function handle(ObtenerChatCompletoQuery $query): array
    {
        $emisorId = new Uuid($query->getEmisorId());
        $destinatarioId = new Uuid($query->getDestinatarioId());

        $emisor = $this->usuarioRepository->findById($emisorId);
        if (!$emisor) {
            throw new DomainException('Usuario emisor no encontrado.');
        }

        $destinatario = $this->usuarioRepository->findById($destinatarioId);
        if (!$destinatario) {
            throw new DomainException('Usuario destinatario no encontrado.');
        }

        $reportes = $this->reporteRepository->findChat($emisorId, $destinatarioId);

        $comentarios = [];

        if ($query->getReporteId() !== null) {
            $idReporte = new Uuid($query->getReporteId());
            $reporte = $this->reporteRepository->findById($idReporte);
            if ($reporte) {
                $comentarios = $this->comentarioRepository->findByReporte($idReporte, $emisorId);
            }
        } else {
            foreach ($reportes as $reporte) {
                $comentariosReporte = $this->comentarioRepository->findByReporte($reporte->id(), $emisorId);
                $comentarios = array_merge($comentarios, $comentariosReporte);
            }
        }

        return [
            'reportes' => array_map(function ($reporte) {
                return [
                    'id' => $reporte->id()->value(),
                    'id_emisor' => $reporte->idUsuarioEmisor()->value(),
                    'id_destinatario' => $reporte->idUsuarioDestinatario()?->value(),
                    'descripcion' => $reporte->descripcion(),
                    'fecha_hora' => $reporte->fechaHora()->format('Y-m-d H:i:s'),
                    'estado' => $reporte->estado()->value()
                ];
            }, $reportes),
            'comentarios' => $comentarios
        ];
    }
}