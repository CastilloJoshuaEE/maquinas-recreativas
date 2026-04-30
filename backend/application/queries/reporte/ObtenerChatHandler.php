<?php
/**
 * application/queries/reporte/ObtenerChatHandler.php
 *
 * Manejador del query ObtenerChat.
 *
 * @package maquinas_recreativas\Application\Queries\Reporte
 */

namespace maquinas_recreativas\Application\Queries\Reporte;

use maquinas_recreativas\Domain\Reporte\ReporteRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class ObtenerChatHandler
 */
final class ObtenerChatHandler
{
    private ReporteRepository $reporteRepository;
    private UsuarioRepository $usuarioRepository;

    public function __construct(
        ReporteRepository $reporteRepository,
        UsuarioRepository $usuarioRepository
    ) {
        $this->reporteRepository = $reporteRepository;
        $this->usuarioRepository = $usuarioRepository;
    }

    /**
     * Maneja el query de obtener chat entre dos usuarios.
     *
     * @param ObtenerChatQuery $query
     * @return array
     * @throws DomainException
     */
    public function handle(ObtenerChatQuery $query): array
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

        return array_map(function ($reporte) {
            return [
                'id' => $reporte->id()->value(),
                'id_emisor' => $reporte->idUsuarioEmisor()->value(),
                'id_destinatario' => $reporte->idUsuarioDestinatario()?->value(),
                'descripcion' => $reporte->descripcion(),
                'fecha_hora' => $reporte->fechaHora()->format('Y-m-d H:i:s'),
                'estado' => $reporte->estado()->value()
            ];
        }, $reportes);
    }
}