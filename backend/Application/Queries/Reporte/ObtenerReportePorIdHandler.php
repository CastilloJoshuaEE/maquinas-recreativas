<?php
/**
 * application/queries/reporte/ObtenerReportePorIdHandler.php
 *
 * Manejador del query ObtenerReportePorId.
 *
 * @package maquinas_recreativas\Application\Queries\Reporte
 */

namespace maquinas_recreativas\Application\Queries\Reporte;

use maquinas_recreativas\Domain\Reporte\ReporteRepository;
use maquinas_recreativas\Domain\Shared\ValueObjects\Uuid;
use maquinas_recreativas\Domain\Shared\Exceptions\DomainException;

/**
 * Class ObtenerReportePorIdHandler
 */
final class ObtenerReportePorIdHandler
{
    private ReporteRepository $reporteRepository;

    public function __construct(ReporteRepository $reporteRepository)
    {
        $this->reporteRepository = $reporteRepository;
    }

    /**
     * Maneja el query de obtener reporte por ID.
     *
     * @param ObtenerReportePorIdQuery $query
     * @return array
     * @throws DomainException
     */
    public function handle(ObtenerReportePorIdQuery $query): array
    {
        $idReporte = new Uuid($query->getIdReporte());

        $reporte = $this->reporteRepository->findById($idReporte);
        if (!$reporte) {
            throw new DomainException('Reporte no encontrado.');
        }

        return [
            'id' => $reporte->id()->value(),
            'id_emisor' => $reporte->idUsuarioEmisor()->value(),
            'id_destinatario' => $reporte->idUsuarioDestinatario()?->value(),
            'descripcion' => $reporte->descripcion(),
            'fecha_hora' => $reporte->fechaHora()->format('Y-m-d H:i:s'),
            'estado' => $reporte->estado()->value(),
            'es_chat' => $reporte->esChat()
        ];
    }
}