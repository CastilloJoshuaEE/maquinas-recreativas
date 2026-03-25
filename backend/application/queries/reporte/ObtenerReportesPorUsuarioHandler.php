<?php
/**
 * application/queries/reporte/ObtenerReportesPorUsuarioHandler.php
 *
 * Manejador del query ObtenerReportesPorUsuario.
 *
 * @package maquinas_recreativas\Application\Queries\Reporte
 */

namespace maquinas_recreativas\Application\Queries\Reporte;

use maquinas_recreativas\Domain\Reporte\ReporteRepository;
use maquinas_recreativas\Domain\Usuario\UsuarioRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class ObtenerReportesPorUsuarioHandler
 */
final class ObtenerReportesPorUsuarioHandler
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
     * Maneja el query de obtener reportes por usuario.
     *
     * @param ObtenerReportesPorUsuarioQuery $query
     * @return array
     * @throws DomainException
     */
    public function handle(ObtenerReportesPorUsuarioQuery $query): array
    {
        $idUsuario = new Uuid($query->getIdUsuario());

        $usuario = $this->usuarioRepository->findById($idUsuario);
        if (!$usuario) {
            throw new DomainException('Usuario no encontrado.');
        }

        $reportes = $this->reporteRepository->findByUsuario($idUsuario);

        return array_map(function ($reporte) {
            return [
                'id' => $reporte->id()->value(),
                'id_emisor' => $reporte->idUsuarioEmisor()->value(),
                'id_destinatario' => $reporte->idUsuarioDestinatario()?->value(),
                'descripcion' => $reporte->descripcion(),
                'fecha_hora' => $reporte->fechaHora()->format('Y-m-d H:i:s'),
                'estado' => $reporte->estado()->value(),
                'es_chat' => $reporte->esChat()
            ];
        }, $reportes);
    }
}