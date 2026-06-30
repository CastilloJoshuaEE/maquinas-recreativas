<?php
/**
 * application/queries/reporte/ObtenerReportesPorUsuarioQuery.php
 *
 * Query para obtener reportes por usuario.
 *
 * @package maquinas_recreativas\Application\Queries\Reporte
 */

namespace maquinas_recreativas\Application\Queries\Reporte;

/**
 * Class ObtenerReportesPorUsuarioQuery
 */
final class ObtenerReportesPorUsuarioQuery
{
    private string $idUsuario;

    public function __construct(string $idUsuario)
    {
        $this->idUsuario = $idUsuario;
    }

    public function getIdUsuario(): string { return $this->idUsuario; }
}