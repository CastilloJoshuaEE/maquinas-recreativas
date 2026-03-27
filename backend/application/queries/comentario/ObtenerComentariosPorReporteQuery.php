<?php
/**
 * application/queries/comentario/ObtenerComentariosPorReporte.php
 *
 * Query para obtener comentarios por reporte.
 *
 * @package maquinas_recreativas\Application\Queries\Comentario
 */

namespace maquinas_recreativas\Application\Queries\Comentario;

/**
 * Class ObtenerComentariosPorReporteQuery
 */
final class ObtenerComentariosPorReporteQuery
{
    private string $idReporte;
    private string $idUsuario;

    public function __construct(string $idReporte, string $idUsuario)
    {
        $this->idReporte = $idReporte;
        $this->idUsuario = $idUsuario;
    }

    public function getIdReporte(): string { return $this->idReporte; }
    public function getIdUsuario(): string { return $this->idUsuario; }
}