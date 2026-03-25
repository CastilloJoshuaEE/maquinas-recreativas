<?php
/**
 * application/commands/reporte/CrearReporte.php
 *
 * Comando para crear un reporte.
 *
 * @package Reconocimiento\Application\Commands\Reporte
 */

namespace Reconocimiento\Application\Commands\Reporte;

/**
 * Class CrearReporte
 */
final class CrearReporte
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