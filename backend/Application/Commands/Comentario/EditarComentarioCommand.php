<?php
namespace maquinas_recreativas\Application\Commands\Comentario;

use maquinas_recreativas\Application\Commands\Command;

final class EditarComentarioCommand implements Command
{
    private string $idComentario;
    private string $idUsuario;
    private string $nuevoComentario;

    public function __construct(string $idComentario, string $idUsuario, string $nuevoComentario)
    {
        $this->idComentario = $idComentario;
        $this->idUsuario = $idUsuario;
        $this->nuevoComentario = $nuevoComentario;
    }

    public function idComentario(): string { return $this->idComentario; }
    public function idUsuario(): string { return $this->idUsuario; }
    public function nuevoComentario(): string { return $this->nuevoComentario; }
}