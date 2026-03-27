<?php
/**
 * application/commands/reporte/ActualizarEstadoReporteCommand.php
 *
 * Comando para actualizar el estado de un reporte.
 *
 * @package maquinas_recreativas\Application\Commands\Reporte
 */

namespace maquinas_recreativas\Application\Commands\Reporte;

/**
 * Class ActualizarEstadoReporte
 */
final class ActualizarEstadoReporteCommand
{
    private string $idReporte;
    private string $estado;

    public function __construct(string $idReporte, string $estado)
    {
        $this->idReporte = $idReporte;
        $this->estado = $estado;
    }

    public function idReporte(): string { return $this->idReporte; }
    public function estado(): string { return $this->estado; }
}