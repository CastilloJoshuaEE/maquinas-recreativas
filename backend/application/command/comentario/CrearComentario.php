<?php
/**
 * application/commands/comentario/CrearComentario.php
 *
 * Comando para crear un comentario.
 *
 * @package Reconocimiento\Application\Commands\Comentario
 */

namespace Reconocimiento\Application\Commands\Comentario;

/**
 * Class CrearComentario
 */
final class CrearComentario
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