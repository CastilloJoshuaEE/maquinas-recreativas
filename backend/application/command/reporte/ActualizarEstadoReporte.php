<?php
/**
 * application/commands/reporte/ActualizarEstadoReporte.php
 *
 * Comando para actualizar el estado de un reporte.
 *
 * @package Reconocimiento\Application\Commands\Reporte
 */

namespace Reconocimiento\Application\Commands\Reporte;

/**
 * Class ActualizarEstadoReporte
 */
final class ActualizarEstadoReporte
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