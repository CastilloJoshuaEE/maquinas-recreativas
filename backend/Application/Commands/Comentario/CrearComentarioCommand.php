<?php
/**
 * application/commands/comentario/CrearComentarioCommand.php
 *
 * Comando para crear un comentario.
 *
 * @package maquinas_recreativas\Application\Commands\Comentario
 */

namespace maquinas_recreativas\Application\Commands\Comentario;

use maquinas_recreativas\Application\Commands\Command;

/**
 * Class CrearComentarioCommand
 */
final class CrearComentarioCommand implements Command
{
    private string $idReporte;
    private string $idUsuarioEmisor;
    private string $comentario;

    public function __construct(string $idReporte, string $idUsuarioEmisor, string $comentario)
    {
        $this->idReporte = $idReporte;
        $this->idUsuarioEmisor = $idUsuarioEmisor;
        $this->comentario = $comentario;
    }

    public function idReporte(): string { return $this->idReporte; }
    public function idUsuarioEmisor(): string { return $this->idUsuarioEmisor; }
    public function comentario(): string { return $this->comentario; }
}