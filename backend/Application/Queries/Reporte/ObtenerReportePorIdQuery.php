<?php
/**
 * application/queries/reporte/ObtenerReportePorIdQuery.php
 *
 * Query para obtener un reporte por ID.
 *
 * @package maquinas_recreativas\Application\Queries\Reporte
 */

namespace maquinas_recreativas\Application\Queries\Reporte;

/**
 * Class ObtenerReportePorIdQuery
 */
final class ObtenerReportePorIdQuery
{
    private string $idReporte;

    public function __construct(string $idReporte)
    {
        $this->idReporte = $idReporte;
    }

    public function getIdReporte(): string { return $this->idReporte; }
}