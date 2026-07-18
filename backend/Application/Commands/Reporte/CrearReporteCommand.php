<?php
/**
 * application/commands/reporte/CrearReporteCommand.php
 *
 * Comando para crear un reporte.
 *
 * @package maquinas_recreativas\Application\Commands\Reporte
 */

namespace maquinas_recreativas\Application\Commands\Reporte;
use maquinas_recreativas\Application\Commands\Command;

/**
 * Class CrearReporte
 */
final class CrearReporteCommand implements Command
{
    private string $idUsuarioEmisor;
    private ?string $idUsuarioDestinatario;
    private string $descripcion;

    public function __construct(string $idUsuarioEmisor, ?string $idUsuarioDestinatario, string $descripcion)
    {
        $this->idUsuarioEmisor = $idUsuarioEmisor;
        $this->idUsuarioDestinatario = $idUsuarioDestinatario;
        $this->descripcion = $descripcion;
    }

    public function idUsuarioEmisor(): string { return $this->idUsuarioEmisor; }
    public function idUsuarioDestinatario(): ?string { return $this->idUsuarioDestinatario; }
    public function descripcion(): string { return $this->descripcion; }
}