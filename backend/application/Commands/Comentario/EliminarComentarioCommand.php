<?php
namespace maquinas_recreativas\Application\Commands\Comentario;

use maquinas_recreativas\Application\Commands\Command;

final class EliminarComentarioCommand implements Command
{
    private string $idComentario;
    private string $idUsuario;

    public function __construct(string $idComentario, string $idUsuario)
    {
        $this->idComentario = $idComentario;
        $this->idUsuario = $idUsuario;
    }

    public function idComentario(): string { return $this->idComentario; }
    public function idUsuario(): string { return $this->idUsuario; }
}