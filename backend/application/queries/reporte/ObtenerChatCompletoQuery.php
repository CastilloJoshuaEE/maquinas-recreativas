<?php
/**
 * application/queries/reporte/ObtenerChatCompletoQuery.php
 *
 * Query para obtener chat completo entre dos usuarios (reportes + comentarios).
 *
 * @package maquinas_recreativas\Application\Queries\Reporte
 */

namespace maquinas_recreativas\Application\Queries\Reporte;

/**
 * Class ObtenerChatCompletoQuery
 */
final class ObtenerChatCompletoQuery
{
    private string $emisorId;
    private string $destinatarioId;
    private ?string $reporteId;

    public function __construct(string $emisorId, string $destinatarioId, ?string $reporteId = null)
    {
        $this->emisorId = $emisorId;
        $this->destinatarioId = $destinatarioId;
        $this->reporteId = $reporteId;
    }

    public function getEmisorId(): string { return $this->emisorId; }
    public function getDestinatarioId(): string { return $this->destinatarioId; }
    public function getReporteId(): ?string { return $this->reporteId; }
}